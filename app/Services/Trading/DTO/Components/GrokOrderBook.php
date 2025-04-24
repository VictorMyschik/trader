<?php

declare(strict_types=1);

namespace App\Services\Trading\DTO\Components;

final readonly class GrokOrderBook
{
    public function __construct(
        public array $bids,
        public array $asks,
    ) {}
}
