<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Trading;

use App\Orchid\Layouts\Trading\TradeEditLayout;
use App\Services\Trading\TradingRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class TradingScreen extends Screen
{
    public ?string $name = 'Trading';

    public function __construct(
        private readonly TradingRepositoryInterface $tradingRepository,
    ) {}

    public function query(): iterable
    {
        return [
            'list' => null,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('add')
                ->class('mr-btn-danger')
                ->icon('plus')
                ->novalidate()
                ->method('saveTrade', ['id' => 0])
        ];
    }

    public function layout(): iterable
    {

        return [
            Layout::modal('trading_modal', TradeEditLayout::class)->async('asyncGetTrade'),
        ];
    }

    public function asyncGetTrade(int $id): array
    {
        return [
            'trade' => $this->tradingRepository->getById($id),
        ];
    }

    public function saveTrade(Request $request, int $id): void
    {
        $input = Validator::make($request->all(), [
            'active'      => 'boolean',
            'stock'       => 'required|integer',
            'pair'        => 'required|string|max:20',
            'different'   => 'required|numeric|min:0|max:100',
            'max_trade'   => 'required|numeric|min:0',
            'strategy'    => 'required|integer',
            'description' => 'string|max:255',
        ])->validated();

        $this->tradingRepository->saveTrade($id, $input);
    }
}
