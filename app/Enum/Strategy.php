<?php

declare(strict_types=1);

namespace App\Enum;

enum Strategy: int
{
    case STRATEGY_BASE = 1;
    case STRATEGY_SMART_ANALISE = 2;
    case GROK = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::STRATEGY_SMART_ANALISE => 'Аналитика истории',
            self::STRATEGY_BASE => 'Базовая',
            self::GROK => 'Grok',
        };
    }
}
