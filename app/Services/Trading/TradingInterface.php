<?php

declare(strict_types=1);

namespace App\Services\Trading;

interface TradingInterface
{
    public function getOrderBook(): array;

    public function getHistory(): array;
}
