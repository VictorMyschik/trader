<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enum\StockType;
use App\Services\Trading\DTO\ConstructorDto;
use App\Services\Trading\Exmo\ExmoClient;
use App\Services\Trading\Exmo\ExmoTradingService;
use App\Services\Trading\Payeer\PayeerTradingService;
use App\Services\Trading\TradingRepositoryInterface;
use App\Services\Trading\Yobit\YobitClient;
use App\Services\Trading\Yobit\YobitTradingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TradingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $id)
    {
        $this->connection = 'database';
    }

    public function handle(TradingRepositoryInterface $repository): void
    {
        $trade = $repository->getById($this->id);

        if (!$trade->isActive()) {
            return;
        }

        $dto = new ConstructorDto(
            strategy: $trade->getStrategy(),
            skipSum: $trade->getSkipSum(),
            pair: $trade->getPair(),
            diff: $trade->getDifferent(),
            client: new YobitClient(),
        );

        $service = match ($trade->getStock()) {
            StockType::YOBIT => new YobitTradingService($dto),
            StockType::EXMO => new ExmoTradingService($dto, new ExmoClient()),
            StockType::PAYEER => new PayeerTradingService($dto),
        };

        $service->trade();

        sleep(1);

        TradingJob::dispatch($this->id);
    }
}
