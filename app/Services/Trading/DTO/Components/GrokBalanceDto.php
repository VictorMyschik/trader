<?php

declare(strict_types=1);

namespace App\Services\Trading\DTO\Components;

final readonly class GrokBalanceDto
{
    public function __construct(
        public string $currency,
        public float  $amount,
    ) {}
}
