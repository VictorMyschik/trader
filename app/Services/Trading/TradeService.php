<?php

declare(strict_types=1);

namespace App\Services\Trading;

use App\Enum\Strategy;
use App\Models\Trade;
use App\Services\Trading\DTO\Components\OpenOrderComponent;

abstract class TradeService implements TradingInterface
{
    public const string KIND_SELL = 'sell';
    public const string KIND_BUY = 'buy';
    protected array $precision = [];

    private float $quantityMin;

    public function trade(): void
    {
        $this->quantityMin = $this->getPairsSettings()[$this->dto->pair]->minQuantity;

        $fullOrderBook = $this->getOrderBook();
        if (!count($fullOrderBook)) {
            return;
        }
        $balance = $this->getBalance();

        if (!isset($balance[explode('_', $this->dto->pair)[1]])) {
            return;
        }

        $orderBookDiff = $this->getOrderBookDiff($fullOrderBook);
        $fullOpenOrders = $this->getOpenOrder($this->dto->pair);

        match ($this->dto->strategy) {
            Strategy::STRATEGY_BASE => $this->baseStrategy($orderBookDiff, $fullOpenOrders, $fullOrderBook, $balance),
            Strategy::STRATEGY_SMART_ANALISE => $this->smartAnaliseStrategy($fullOpenOrders, $fullOrderBook, $balance),
        };
    }

    private function getOrderBookDiff(array $fullOrderBook): float
    {
        return round($fullOrderBook[0]->priceSell * 100 / (float)$fullOrderBook[0]->priceBuy - 100, 2);
    }

    private function correctHasOrders(array $fullOpenOrder, array $orderBook): bool
    {
        /** @var OpenOrderComponent $openOrder */
        foreach ($fullOpenOrder as $openOrder) {
            // Has open order
            if ($this->dto->pair === $openOrder->pair) {
                // Update order
                if (!$this->isActual($openOrder, $orderBook)) {
                    $this->cancelOrder($openOrder->orderId);

                    return true;
                }
            }
        }

        return false;
    }

    protected function cancelOrder(int $orderId): void
    {
        $this->client->cancelOrder($orderId);
    }

    private function isActual(OpenOrderComponent $openOrder, array $orderBook): bool
    {
        $kind = $openOrder->type;
        $price = $openOrder->price;

        $precision = $this->getPricePrecision()[$openOrder->pair];
        $priceKeyName = ($kind == self::KIND_SELL) ? 'priceSell' : 'priceBuy';
        $sumKeyName = ($kind == self::KIND_SELL) ? 'sumSell' : 'sumBuy';

        $myOpenPrice = round($price, $precision);

        $orderBookItem = $orderBook[0];
        $sum = 0;
        foreach ($orderBook as $item) {
            // exclude self order
            if ($item->$priceKeyName == $price) {
                $sum += $item->$sumKeyName - round($openOrder->price * $openOrder->amount, $precision);
            } else {
                $sum += $item->$sumKeyName;
            }

            if ($sum > $this->dto->skipSum) {
                $orderBookItem = $item;
                break;
            }
        }

        $orderPrice = $orderBookItem->$priceKeyName;

        if ($kind == self::KIND_SELL) {
            $precisionDiff = pow(10, -$precision);
            $orderPrice = $orderPrice - $precisionDiff;
        }

        if ($kind == self::KIND_BUY) {
            $precisionDiff = pow(10, -$precision);
            $orderPrice = $orderPrice + $precisionDiff;
        }

        $orderPrice = round($orderPrice, $precision);

        if ((string)$orderPrice != (string)$myOpenPrice) {
            return false; // need update order
        }

        return true;
    }

    private function tradeByOrder(array $balance, array $fullOpenOrders, array $orderBook, string $pairName): void
    {
        $currencyFirst = explode('_', $pairName)[0];
        $currencySecond = explode('_', $pairName)[1];
        $balanceValue = $balance[$currencyFirst] ?? 0;

        /// Sell MNX
        if ($balanceValue > $this->quantityMin) {
            // Cancel open orders. Disable many orders, one only
            foreach ($fullOpenOrders as $openOrder) {
                if ($openOrder->type === self::KIND_SELL && $this->dto->pair === $openOrder->pair) {
                    $this->cancelOrder($openOrder->orderId);

                    return;
                }
            }

            // Create new order
            $newPrice = $this->getNewPrice($orderBook, self::KIND_SELL, $pairName);
            $this->addOrder($newPrice, $pairName, self::KIND_SELL, $balanceValue);

            return;
        }

        /// Buy MNX
        $balanceValue = $balance[$currencySecond] ?? 0;
        if ($balanceValue > 0.01) {
            $allowMaxTradeSum = min($balanceValue, $this->dto->quantityMax);

            foreach ($fullOpenOrders as $openOrder) {
                if ($openOrder->type === self::KIND_BUY && $this->dto->pair === $openOrder->pair) {
                    $this->cancelOrder($openOrder->orderId);

                    return;
                }
            }

            // Create new order
            $newPrice = $this->getNewPrice($orderBook, self::KIND_BUY, $pairName);

            $quantity = $allowMaxTradeSum / $newPrice;

            if ($quantity <= $this->quantityMin) {
                return;
            }

            $this->addOrder($newPrice, $pairName, self::KIND_BUY, $quantity);
        }
    }

    private function getNewPrice(array $orderBook, string $type, string $pairName): float
    {
        $precision = $this->getPricePrecision()[$pairName];
        $precisionDiff = pow(10, -$precision);

        // Get price skipping "small" amount row
        $orderBookItem = $orderBook[0];
        $sum = 0;
        foreach ($orderBook as $item) {
            $sum += ($type == self::KIND_SELL) ? $item->sumSell : $item->sumBuy;
            if ($sum > $this->dto->skipSum) {
                $orderBookItem = $item;
                break;
            }
        }

        if ($type == self::KIND_SELL) {
            $oldPriceSell = (float)$orderBookItem->priceSell;
            $newPrice = $oldPriceSell - $precisionDiff;
        } else { // Buy
            $oldPriceBuy = (float)$orderBookItem->priceBuy;
            $newPrice = $oldPriceBuy + $precisionDiff;
        }

        return round($newPrice, $precision);
    }

    public static function runTrading(): void
    {
        foreach (Trade::all() as $item) {
            if (!$item->isActive()) {
                continue;
            }

            $parameter = [
                'strategy'  => $item->getStrategy(),
                'stock'     => $item->getStock()->getLabel(),
                'diff'      => $item->getDifferent(),
                'maxTrade'  => $item->getMaxTrade(),
                'pair'      => strtoupper($item->getPair()),
                'queueName' => 'default',//strtolower($item->id() . '_queue'),
                'skipSum'   => $item->getSkipSum(),
            ];

            self::tradingByStock($parameter);
        }
    }

    #region Strategics
    private function baseStrategy(float $orderBookDiff, array $fullOpenOrders, array $fullOrderBook, array $balance): void
    {
        // If diff smaller than commission - cancel all orders
        if ($orderBookDiff < $this->dto->diff) {
            foreach ($fullOpenOrders as $openOrder) {
                if ($openOrder->pair === $this->dto->pair) {
                    $this->cancelOrder($openOrder->orderId);
                }
            }
        } else {
            $needRestart = $this->correctHasOrders($fullOpenOrders, $fullOrderBook);
            if (!$needRestart) {
                $this->tradeByOrder($balance, $fullOpenOrders, $fullOrderBook, $this->dto->pair);
            }
        }
    }

    private function smartAnaliseStrategy(array $fullOpenOrders, array $fullOrderBook, array $balance): void
    {
        $history = $this->getHistory();

        $currentOrders = reset($fullOrderBook);
        $currentPriceBuy = $currentOrders['PriceBuy'];
        $currentPriceSell = $currentOrders['PriceSell'];


        /// Find Price Buy
        // Actual price to open order for buy
        $priceSuy = array_column($history['sell'], 'PriceTraded');
        $minBuy = min($priceSuy);

        // 2/3 percent of diff
        $diff = ($currentPriceBuy * 100 / $minBuy - 100) / 5 * 1;
        $finalPriceBuy = $currentPriceBuy - ($currentPriceBuy / 100 * $diff);
        $finalPriceBuy = round($finalPriceBuy, $this->getPricePrecision()[$this->dto->pair]);

        /// Find Price Sell
        // Actual price to open order for sale
        $priceBuy = array_column($history['buy'], 'PriceTraded');
        $maxSell = max($priceBuy);

        // 2/3 percent of diff
        $diff = ($maxSell * 100 / $currentPriceSell - 100) / 5 * 1;
        $finalPriceSell = $currentPriceSell / 100 * $diff + $currentPriceSell;
        $finalPriceSell = round($finalPriceSell, $this->getPricePrecision()[$this->dto->pair]);

        $commissionTakerMaker = ($this->getPairsSettings()[$this->dto->pair]['commission_maker_percent']) * 3; // %

        // Cancel Open Orders
        $d = $finalPriceSell * 100 / $finalPriceBuy - 100;
        if ($d < $commissionTakerMaker) {
            foreach ($fullOpenOrders as $openOrder) {
                if ($openOrder['pair'] === $this->dto->pair) {
                    $out['cancelOrder'] = $openOrder['order_id'];
                    $this->cancelOrder($openOrder['order_id']);
                }
            }
        }

        // Trade
        [$currencyFirst, $currencySecond] = explode('_', $this->dto->pair);

        $balanceValue = $balance[$currencyFirst] ?? 0;

        /// Sell MNX
        // Correct opened order
        foreach ($fullOpenOrders as $openOrder) {
            if ($openOrder['type'] === self::KIND_SELL
                && $this->dto->pair === $openOrder['pair']
                && $openOrder['price'] !== $finalPriceSell
            ) {
                $this->cancelOrder($openOrder['order_id']);
                return;
            }
        }

        if ($balanceValue > $this->quantityMin) {
            // Cancel open orders. Disable many orders, one only
            foreach ($fullOpenOrders as $openOrder) {
                if ($openOrder['type'] === self::KIND_SELL && $this->dto->pair === $openOrder['pair']) {
                    $this->cancelOrder($openOrder['order_id']);

                    return;
                }
            }

            // Create new order
            $this->addOrder($finalPriceSell, $this->dto->pair, self::KIND_SELL, $balanceValue);
        }


        /// Buy MNX
        // Correct opened order
        foreach ($fullOpenOrders as $openOrder) {
            if ($openOrder['type'] === self::KIND_BUY
                && $this->dto->pair === $openOrder['pair']
                && $openOrder['price'] !== $finalPriceBuy
            ) {
                $this->cancelOrder($openOrder['order_id']);
                return;
            }
        }

        $balanceValue = $balance[$currencySecond] ?? 0;
        if ($balanceValue > 0.01) {
            $allowMaxTradeSum = min($balanceValue, $this->dto->quantityMax);

            foreach ($fullOpenOrders as $openOrder) {
                if ($openOrder['type'] === self::KIND_BUY && $this->dto->pair === $openOrder['pair']) {
                    $this->cancelOrder($openOrder['order_id']);

                    return;
                }
            }

            // Create new order
            $quantity = $allowMaxTradeSum / $finalPriceBuy;

            if ($quantity <= $this->quantityMin) {
                return;
            }

            $this->addOrder($finalPriceBuy, $this->dto->pair, self::KIND_BUY, $quantity);
        }
    }

    #endregion

    #region Commands
    public static function stopTrading()
    {
        echo exec('supervisorctl reread all') . '<br>';
        echo exec('supervisorctl update') . '<br>';
        echo exec('supervisorctl restart all') . '<br>';
        echo exec('cd /var/www/trading') . '<br>';
        echo exec('php artisan queue:clear') . '<br>';
        echo exec('php artisan queue:clear') . '<br>';
        echo exec('php artisan config:clear') . '<br>';
        echo exec('php artisan cache:clear') . '<br>';
        echo exec('redis-cli -h localhost -p 6379 flushdb') . '<br>';
        echo exec('php artisan horizon:pause') . '<br>';
        echo exec('php artisan horizon:clear') . '<br>';
    }

    public static function tradingByStock(array $parameter)
    {
        TradingJob::dispatch($parameter);
    }
    #endregion
}
