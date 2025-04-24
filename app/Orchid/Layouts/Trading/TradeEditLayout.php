<?php

namespace App\Orchid\Layouts\Trading;

use App\Enum\StockType;
use App\Enum\Strategy;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class TradeEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Group::make([
                Switcher::make('trade.active')->sendTrueOrFalse()->title('Active'),

                Select::make('trade.stock')
                    ->options(StockType::getSelectList())
                    ->required()
                    ->title('Stock'),
            ]),

            Input::make('trade.pair')
                ->type('text')
                ->required()
                ->title('Pair'),

            Input::make('trade.different')
                ->type('number')
                ->min(0)
                ->max(100)
                ->step(0.01)
                ->required()
                ->title('Different, %'),

            Input::make('trade.max_trade')
                ->type('number')
                ->required()
                ->title('Max trade sum'),

            Input::make('trade.skip_sum')
                ->type('number')
                ->required()
                ->title('Skip sum'),

            Select::make('trade.strategy')
                ->options(Strategy::getSelectList())
                ->required()
                ->title('Strategy'),

            TextArea::make('trade.description')
                ->rows(3)
                ->title('Description'),
        ];
    }
}
