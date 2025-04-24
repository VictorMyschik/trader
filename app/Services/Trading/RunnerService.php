<?php

declare(strict_types=1);

namespace App\Services\Trading;

use App\Jobs\TradingJob;

final readonly class RunnerService
{
    public function __construct(
        private TradingRepositoryInterface $repository
    ) {}

    public function runJob(): void
    {
        $list = $this->repository->getActiveTradingList();

        foreach ($list as $trade) {
            TradingJob::dispatch($trade->id())->onConnection('sync');
        }
    }
}
