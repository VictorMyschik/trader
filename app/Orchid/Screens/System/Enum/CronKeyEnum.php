<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System\Enum;

enum CronKeyEnum: string
{
    case LINKS = 'links';

    public function getLabel(): string
    {
        return match ($this) {
            self::LINKS => 'Парсинг ссылок',
        };
    }

    public static function getSelectList(): array
    {
        return array_combine(
            array_map(fn($enum) => $enum->value, self::cases()),
            array_map(fn($enum) => $enum->getLabel(), self::cases())
        );
    }
}
