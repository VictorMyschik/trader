<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Trading;

use App\Models\GrokTradingLog;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class GrokTradingLogListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('action', 'Action')->render(fn(GrokTradingLog $log) => $log->getAction()->getLabel())->sort(),
            TD::make('price', 'Price')->sort(),
            TD::make('orderId', 'Order ID')->sort(),
            TD::make('reason', 'Reason')->sort(),
            TD::make('done', 'Done')->active(),
            TD::make('created_at', 'Created')
                ->render(fn(GrokTradingLog $log) => $log->created_at->format('d.m.Y H:i:s'))
                ->sort()
                ->defaultHidden(),
        ];
    }
}
