<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\ORM\ORM;
use App\Services\Trading\Enum\GrokActionEnum;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class GrokTradingLog extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'grok_trading_log';

    protected $fillable = [
        'action',
        'price',
        'orderId',
        'reason',
        'done',
    ];
    protected array $allowedSorts = [
        'id',
        'action',
        'price',
        'orderId',
        'reason',
        'done',
        'created_at',
    ];

    public function getAction(): GrokActionEnum
    {
        return GrokActionEnum::from($this->action);
    }
}
