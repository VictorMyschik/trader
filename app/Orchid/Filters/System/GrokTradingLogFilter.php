<?php

declare(strict_types=1);

namespace App\Orchid\Filters\System;

use App\Models\GrokTradingLog;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;

class GrokTradingLogFilter extends Filter
{
    public static function runQuery(): iterable
    {
        return GrokTradingLog::filters([self::class])->paginate(100);
    }

    public function run(Builder $builder): Builder
    {
        return $builder;
    }
}
