<?php

declare(strict_types=1);

namespace App\Services\Trading\DTO;

final readonly class PairSettingDto
{
    public function __construct(
        public float|int $commissionMakerPercent,
        public float|int $commissionTakerPercent,
        public float|int $maxAmount,
        public float|int $maxPrice,
        public float|int $maxQuantity,
        public float|int $minAmount,
        public float|int $minPrice,
        public float|int $minQuantity,
        public float|int $pricePrecision,
    ) {}
}
