<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\StockType;
use App\Enum\Strategy;
use App\Models\Lego\Fields\ActiveFieldTrait;
use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Trade extends ORM
{
    use AsSource;
    use Filterable;
    use ActiveFieldTrait;
    use DescriptionNullableFieldTrait;

    protected $table = 'trading';
    protected $fillable = [
        'stock',
        'different',
        'max_trade',
        'pair',
        'skip_sum',
        'description',
        'active',
        'strategy'
    ];
    protected array $allowedSorts = [
        'id',
        'stock',
        'different',
        'max_trade',
        'pair',
        'skip_sum',
        'description',
        'active',
        'strategy'
    ];

    public function getStock(): StockType
    {
        return StockType::from($this->stock);
    }

    public function getDifferent(): float
    {
        return (float)$this->different;
    }

    public function getMaxTrade(): float
    {
        return (float)$this->max_trade;
    }

    public function getPair(): string
    {
        return $this->pair;
    }

    public function getSkipSum(): float
    {
        return (float)$this->skip_sum;
    }

    public function getStrategy(): Strategy
    {
        return Strategy::from($this->strategy);
    }
}
