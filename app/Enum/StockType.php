<?php

declare(strict_types=1);

namespace App\Enum;

enum StockType: int
{
    case EXMO = 1;
    case YOBIT = 2;
    case PAYEER = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::EXMO => 'EXMO',
            self::YOBIT => 'YOBIT',
            self::PAYEER => 'PAYEER',
        };
    }

    public static function getSelectList(): array
    {
        return [
            self::EXMO->value   => self::EXMO->getLabel(),
            self::YOBIT->value  => self::YOBIT->getLabel(),
            self::PAYEER->value => self::PAYEER->getLabel(),
        ];
    }
}
