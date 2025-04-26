<?php

declare(strict_types=1);

namespace App\Services\Trading;

use App\Services\Trading\DTO\Components\GrokBalanceDto;
use App\Services\Trading\DTO\Components\GrokOrderBook;
use App\Services\Trading\DTO\Components\GrokTradingHistoryComponent;
use App\Services\Trading\DTO\GrokStockDataDto;
use App\Services\Trading\Enum\GrokActionEnum;
use App\Services\Trading\Exmo\ExmoClient;
use App\Services\Trading\Exmo\GrokClient;
use Carbon\Carbon;

final readonly class GrokTradingService
{
    private const string PAIR = 'BTC_USDT';

    public function __construct(
        private ExmoClient                 $client,
        private GrokClient                 $grokClient,
        private TradingRepositoryInterface $tradingRepository,
    ) {}

    public function run(): void
    {
        $stockData = $this->getStockData(self::PAIR);

        $json = json_encode($stockData, JSON_PRETTY_PRINT);

        $message = $this->buildMessage($json);

        $response = $this->grokClient->send($message);

        $isDone = match ($response->action) {
            GrokActionEnum::BUY => $this->by(self::PAIR, $response->price),
            GrokActionEnum::SELL => $this->sell(self::PAIR, $response->price),
            GrokActionEnum::HOLD => $this->hold(),
            GrokActionEnum::CANCEL => $this->cancel($response->orderId),
        };

        $this->tradingRepository->saveGrokTradingLog($response, $isDone);
    }

    private function by(string $pair, float $price): bool
    {
        [$currencyFirst, $currencySecond] = explode('_', $pair);
        $balance = $this->getBalance()[$currencySecond];
        $quantity = $balance / $price;

        return $this->addOrder($price, $pair, GrokActionEnum::BUY, $quantity);
    }

    private function sell(string $pair, float $price): bool
    {
        [$currencyFirst, $currencySecond] = explode('_', $pair);
        $balance = $this->getBalance()[$currencyFirst];

        return $this->addOrder($price, $pair, GrokActionEnum::SELL, $balance);
    }

    private function addOrder(float $price, string $pairName, GrokActionEnum $kind, float $quantity): bool
    {
        $tmpNum = (explode('.', (string)$quantity));
        $precisionDiff = pow(10, -strlen($tmpNum[1]));
        $finalQuantity = $quantity - $precisionDiff;

        $parameters = [
            "pair"     => $pairName,
            "quantity" => $finalQuantity,
            "price"    => $price,
            "type"     => $kind->value
        ];

        $result = $this->client->createOrder($parameters);

        return $result['result'];
    }

    private function getBalance(): array
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


    private function hold(): true
    {
        return true;
    }

    private function cancel(int $orderId): bool
    {
        $result = $this->client->cancelOrder($orderId);

        return $result['result'];
    }

    private function getStockData(string $pair): GrokStockDataDto
    {
        $ticker = $this->client->getTicker()[$pair];
        $pairArr = explode('_', $pair);
        $balance = [];
        foreach ($this->getBalance() as $key => $value) {
            if (in_array($key, $pairArr)) {
                $balance[] = new GrokBalanceDto(
                    currency: $key,
                    amount: $value,
                );
            }
        }


        return new GrokStockDataDto(
            pair: $pair,
            lastPrice: $ticker['last_trade'],
            bid: $ticker['buy_price'],
            ask: $ticker['sell_price'],
            volume24h: $ticker['vol'],
            high24h: $ticker['high'],
            low24h: $ticker['low'],
            orderBook: $this->getOrderBook($pair),
            myOpenOrders: $this->getOpenOrders($pair),
            myBalance: $balance,
            myHistory: $this->getHistory($pair),
        );
    }

    private function getHistory(string $pair): array
    {
        $data = $this->client->getUserTradeHistory($pair);

        $history = [];
        foreach ($data[$pair] ?? [] as $item) {
            $history[] = new GrokTradingHistoryComponent(
                pair: $pair,
                amount: (float)$item['amount'],
                date: Carbon::createFromTimestamp($item['date'])->toAtomString(),
                price: (float)$item['price'],
                quantity: (float)$item['quantity'],
                type: $item['type'],
            );

            // Пока только одну транзакцию
            return $history;
        }

        return $history;
    }

    private function getOpenOrders(string $pair): array
    {
        $data = $this->client->getOpenOrder();

        $orders = [];
        foreach ($data[$pair] ?? [] as $item) {
            $orders[] = [
                "orderId"  => $item['order_id'],
                "type"     => $item['type'],
                "price"    => $item['price'],
                "quantity" => $item['quantity'],
                "status"   => "open"
            ];
        }

        return $orders;
    }

    private function getOrderBook(string $pair): GrokOrderBook
    {
        $data = $this->client->getOrderBook($pair, 10);

        $bids = [];

        foreach ($data[$pair]['bid'] as $item) {
            $bids[] = [
                'price'    => round((float)$item[0], 8),
                'quantity' => round((float)$item[1], 4),
                'sum'      => round((float)$item[2], 4),
            ];
        }
        $asks = [];
        foreach ($data[$pair]['ask'] as $item) {
            $asks[] = [
                'price'    => round((float)$item[0], 8),
                'quantity' => round((float)$item[1], 4),
                'sum'      => round((float)$item[2], 4),
            ];
        }

        return new GrokOrderBook(
            bids: $bids,
            asks: $asks,
        );
    }

    private function buildMessage(string $json): string
    {
        $message = 'Ты трейдер, анализирующий данные криптовалютной биржи Exmo в формате JSON для торговой пары (например, BTC_USDT). Задача торговать на бирже Exmo, используя API.
Торги в краткосрочной перспективе (от 1 до 5 минут).
Я предоставляю JSON с текущими рыночными данными (цена, bid, ask, объем, книга ордеров) и списком открытых ордеров.
Проанализируй данные и дай короткий ответ в формате JSON:
json
{
  "action": "buy|sell|hold|cancel",
  "price": <цена или null>,
  "orderId": <ID ордера или null>,
  "reason": "<краткое объяснение>"
}
action: "buy", "sell", "hold" или "cancel" (для отмены существующего ордера).
price: рекомендованная цена для покупки/продажи (null для "hold" или "cancel").
orderId: ID ордера для отмены (null, если не отменяется ордер).
reason: краткое обоснование рекомендации.
Учитывай текущие открытые ордера, спред, объемы в книге ордеров и рыночные тренды. Данные будут поступать каждые несколько секунд.
Проанализируй предоставленные данные и верни рекомендацию в указанном формате.

Текущие данные:

';

        $message .= $json;

        return $message;
    }
}
