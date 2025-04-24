<?php

declare(strict_types=1);

namespace App\Services\Trading\Exmo;

use App\Services\Trading\DTO\Components\OpenOrderComponent;
use App\Services\Trading\DTO\Components\OrderBookComponent;
use App\Services\Trading\DTO\ConstructorDto;
use App\Services\Trading\DTO\PairSettingDto;
use App\Services\Trading\StockClientInterface;
use App\Services\Trading\TradeService;
use App\Services\Trading\TradingInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

final class ExmoTradingService extends TradeService implements TradingInterface
{
    /**
     * @param ConstructorDto $dto
     * @param ExmoClient $client
     */
    public function __construct(
        protected readonly ConstructorDto       $dto,
        protected readonly StockClientInterface $client,
    ) {}

    /**
     * Order Book with trades history
     */
    public function getOrderBook(int $limit = 25): array
    {
        $data = $this->client->getOrderBook($this->dto->pair, $limit);

        return $this->parseOrderBook($data);
    }

    public function getHistory(): array
    {
        $data = $this->client->getHistory($this->dto->pair);

        return $this->parseHistory($data);
    }

    private function parseOrderBook(array $data): array
    {
        $rows = [];

        if (!isset($data[$this->dto->pair]['ask'])) {
            return $rows;
        }

        foreach ($data[$this->dto->pair]['ask'] as $key => $item) {
            $rows[] = new OrderBookComponent(
                priceSell: round((float)$item[0], 8),
                quantitySell: round((float)$item[1], 4),
                sumSell: round((float)$item[2], 4),
                priceBuy: round((float)$data[$this->dto->pair]['bid'][$key][0], 8),
                quantityBuy: round((float)$data[$this->dto->pair]['bid'][$key][1], 4),
                sumBuy: round((float)$data[$this->dto->pair]['bid'][$key][2], 4),
            );
        }

        return $rows;
    }

    protected function parseHistory(array $data): array
    {
        $out = [];
        if (!isset($data[$this->dto->pair])) {
            return $out;
        }

        foreach ($data[$this->dto->pair] as $row) {
            $item = [];

            $item['QuantityTraded'] = round($row['quantity'], 8);
            $item['PriceTraded'] = round($row['price'], 8);

            $item['SumTraded'] = round($row['amount'], 8);
            $item['TimeTraded'] = Carbon::createFromTimestamp($row['date'])->toDateTime()->format('H:i:s');
            $item['timestamp'] = $row['date'];

            $out[$row['type'] == 'buy' ? self::KIND_BUY : self::KIND_SELL][] = $item;
        }

        return $out;
    }

    protected function getPricePrecision(): array
    {
        if (!count($this->precision)) {
            $this->precision = Cache::rememberForever(self::class . '_price_precision', function () {
                $pairs = [];
                foreach ($this->getPairsSettings() as $key => $item) {
                    $pairs[$key] = $item['price_precision'];
                }
                ksort($pairs);

                return $pairs;
            });
        }

        return $this->precision;
    }

    protected function getPairsSettings(): array
    {
        return Cache::rememberForever(self::class . '_PairsSettings', function () {
            foreach ($this->client->getPairsSettings() as $pair => $item) {
                $pairs[$pair] = new PairSettingDto(
                    commissionMakerPercent: (float)$item['commission_maker_percent'],
                    commissionTakerPercent: (float)$item['commission_taker_percent'],
                    maxAmount: (float)$item['max_amount'],
                    maxPrice: (float)$item['max_price'],
                    maxQuantity: (float)$item['max_quantity'],
                    minAmount: (float)$item['min_amount'],
                    minPrice: (float)$item['min_price'],
                    minQuantity: (float)$item['min_quantity'],
                    pricePrecision: (float)$item['price_precision'],
                );
            }

            ksort($pairs);

            return $pairs;
        });
    }

    protected function getBalance(): array
    {
        $response = $this->client->getBalance();

        $balanceOut = array();

        if (isset($response['balances'])) {
            foreach ($response['balances'] as $cryptoName => $balance) {
                $balanceOut[$cryptoName] = (float)$balance;
            }
        }

        return $balanceOut;
    }

    protected function addOrder(float $price, string $pairName, string $kind, float $quantity): mixed
    {
        $tmpNum = (explode('.', (string)$quantity));
        $precisionDiff = pow(10, -strlen($tmpNum[1]));
        $finalQuantity = $quantity - $precisionDiff;

        $parameters = [
            "pair"     => $pairName,  //"BTC_USD",
            "quantity" => $finalQuantity,
            "price"    => $price,
            "type"     => $kind
        ];

        return $this->client->createOrder($parameters);
    }

    protected function cancelOrder(int $orderId): void
    {
        $this->client->cancelOrder($orderId);
    }

    protected function getOpenOrder(): array
    {
        $list = $this->client->getOpenOrder();

        if (empty($list)) {
            return [];
        }

        $out = [];
        foreach ($list as $pair => $orders) {
            foreach ($orders as $item) {
                $out[] = new OpenOrderComponent(
                    orderId: (int)$item['order_id'],
                    pair: $pair,
                    type: $item['type'],
                    amount: (float)$item['quantity'],
                    price: (float)$item['price'],
                    value: (float)$item['amount'],
                );
            }
        }

        return $out;
    }
}
