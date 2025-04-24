<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Trading;

use App\Orchid\Filters\System\GrokTradingLogFilter;
use App\Orchid\Layouts\Trading\GrokTradingLogListLayout;
use App\Services\Trading\TradingRepositoryInterface;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;

class GrokTradingLogScreen extends Screen
{
    public ?string $name = 'Grok Trading Log';

    public function __construct(
        private readonly TradingRepositoryInterface $tradingRepository,
    ) {}

    public function query(): iterable
    {
        return [
            'list' => GrokTradingLogFilter::runQuery(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Clear')
                ->icon('trash')
                ->method('clear')
                ->confirm('Are you sure you want to clear the Grok trading log?'),
        ];
    }

    public function layout(): iterable
    {
        return [
            GrokTradingLogListLayout::class,
        ];
    }

    public function clear(): void
    {
        $this->tradingRepository->clearGrokTradingLog();
    }
}
