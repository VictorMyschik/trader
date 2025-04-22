<?php

namespace App\Orchid\Layouts\Trading;

use App\Models\Trade;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class TradeListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort(),
            TD::make('active', 'Active')->sort()->active(),
            TD::make('pair')->sort(),
            TD::make('description', 'Description')->width('50%')->defaultHidden(),
            TD::make('created_at', 'Created')
                ->render(fn(Trade $trade) => $trade->created_at->format('d.m.Y'))
                ->sort()
                ->defaultHidden(),
            TD::make('updated_at', 'Updated')
                ->render(fn(Trade $trade) => $trade->updated_at?->format('d.m.Y'))
                ->sort()
                ->defaultHidden(),

            TD::make('#', 'Действия')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (Trade $trade) {
                    return DropDown::make()->icon('options-vertical')->list([
                        ModalToggle::make('Edit')
                            ->icon('pencil')
                            ->modal('trading_modal')
                            ->novalidate()
                            ->method('saveTrade', ['id' => $trade->id()]),

                        Button::make('Delete')
                            ->icon('trash')
                            ->confirm('This item will be removed permanently.')
                            ->method('remove', ['id' => $trade->id()]),
                    ]);
                }),
        ];
    }
}
