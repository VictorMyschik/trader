<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\StockType;
use App\Enum\Strategy;
use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\ORM\ORM;

class Trading extends ORM
{
    use DescriptionNullableFieldTrait;

    protected $table = 'trading';
    protected $fillable = [
        'stock',
        'different',
        'max_trade',
        'pair',
        'skip_sum',
        'description',
        'is_active',
        'strategy'
    ];

    public function getStock(): StockType
    {
        return StockType::from($this->stock);
    }

    public function getDifferent(): float
    {
        return $this->Different;
    }

    public function getMaxTrade(): float
    {
        return $this->MaxTrade;
    }

    public function getPair(): string
    {
        return $this->Pair;
    }

    public function getSkipSum(): float
    {
        return $this->SkipSum;
    }

    public function getStrategy(): Strategy
    {
        return Strategy::from($this->strategy);
    }
}
