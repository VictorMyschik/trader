<?php

declare(strict_types=1);

namespace App\Services\Trading\DTO;

use App\Services\Trading\DTO\Components\GrokOrderBook;

final readonly class GrokStockDataDto
{
    public function __construct(
        public string        $pair,
        public string        $lastPrice,
        public string        $bid,
        public string        $ask,
        public string        $volume24h,
        public string        $high24h,
        public string        $low24h,
        public GrokOrderBook $orderBook,
        public array         $openOrders,
        public array         $balance //GrokBalanceDto[],
    ) {}
}
