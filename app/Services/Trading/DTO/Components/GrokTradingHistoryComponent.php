<?php

declare(strict_types=1);

namespace App\Services\Trading\DTO\Components;

final readonly class GrokTradingHistoryComponent
{
    public function __construct(
        public string $pair,
        public float  $amount,
        public string $date,
        public float  $price,
        public float  $quantity,
        public string $type,
    ) {}
}
