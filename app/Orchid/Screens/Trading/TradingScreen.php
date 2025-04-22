<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Trading;

use App\Orchid\Filters\TradingFilter;
use App\Orchid\Layouts\Trading\TradeEditLayout;
use App\Orchid\Layouts\Trading\TradeListLayout;
use App\Services\Trading\TradingRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\ModalToggle;
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
            'list' => TradingFilter::runQuery(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('add')
                ->class('mr-btn-danger')
                ->icon('plus')
                ->modal('trading_modal')
                ->novalidate()
                ->method('saveTrade', ['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            TradeListLayout::class,
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
            'trade.active'      => 'boolean',
            'trade.stock'       => 'required|integer',
            'trade.pair'        => 'required|string|max:20',
            'trade.different'   => 'required|numeric|min:0|max:100',
            'trade.max_trade'   => 'required|numeric|min:0',
            'trade.skip_sum'    => 'required|numeric|min:0',
            'trade.strategy'    => 'required|integer',
            'trade.description' => 'nullable|string|max:255',
        ])->validated()['trade'];

        $this->tradingRepository->saveTrade($id, $input);
    }

    public function remove(int $id): void
    {
        $this->tradingRepository->deleteTrade($id);
    }
}
