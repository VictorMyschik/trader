<?php

declare(strict_types=1);

namespace App\Services\Trading\Enum;

enum GrokActionEnum: string
{
    case BUY = 'buy';
    case SELL = 'sell';
    case HOLD = 'hold';
    case CANCEL = 'cancel';

    public function getLabel(): string
    {
        return match ($this) {
            self::BUY => 'Buy',
            self::SELL => 'Sell',
            self::HOLD => 'Hold',
            self::CANCEL => 'Cancel',
        };
    }
}
