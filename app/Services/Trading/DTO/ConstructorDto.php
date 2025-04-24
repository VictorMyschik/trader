<?php

declare(strict_types=1);

namespace App\Services\Trading\DTO;

use App\Enum\Strategy;
use App\Services\Trading\StockClientInterface;

final readonly class ConstructorDto
{
    public function __construct(
        public Strategy             $strategy,
        public float|int            $skipSum,
        public string               $pair,
        public float                $diff,
        public StockClientInterface $client,
    ) {}
}
