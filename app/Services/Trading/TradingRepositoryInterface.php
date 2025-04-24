<?php

declare(strict_types=1);

namespace App\Services\Trading;

use App\Models\Trade;

interface TradingRepositoryInterface
{
    public function getById(int $id): ?Trade;

    public function saveTrade(int $id, array $data): int;

    public function deleteTrade(int $id): void;

    public function getActiveTradingList(): array;
}
