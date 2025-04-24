<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\GrokTradingLog;
use App\Models\Trade;
use App\Services\Trading\DTO\GrokResponseDto;
use App\Services\Trading\TradingRepositoryInterface;

final readonly class TradingRepository extends DatabaseRepository implements TradingRepositoryInterface
{
    public function getById(int $id): ?Trade
    {
        return Trade::loadBy($id);
    }

    public function saveTrade(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table('trading')->where('id', $id)->update($data);

            return $id;
        }

        return $this->db->table('trading')->insertGetId($data);
    }

    public function deleteTrade(int $id): void
    {
        $this->db->table('trading')->where('id', $id)->delete();
    }

    public function getActiveTradingList(): array
    {
        return Trade::where('active', true)->get()->all();
    }

    public function clearGrokTradingLog(): void
    {
        $this->db->table(GrokTradingLog::getTableName())->truncate();
    }

    public function saveGrokTradingLog(GrokResponseDto $log, bool $isDone): void
    {
        $this->db->table(GrokTradingLog::getTableName())->insert([
            'action'   => $log->action->value,
            'price'    => $log->price,
            'order_id' => $log->orderId,
            'reason'   => $log->reason,
            'done'     => $isDone,
        ]);
    }
}
