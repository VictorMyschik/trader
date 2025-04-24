<?php

declare(strict_types=1);

namespace App\Services\Trading\DTO;

use App\Services\Trading\Enum\GrokActionEnum;

final readonly class GrokResponseDto
{
    public function __construct(
        public GrokActionEnum $action,
        public ?float         $price,
        public ?int           $orderId,
        public ?string        $reason,
    ) {}
}
