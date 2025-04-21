<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\TradingRepository;
use App\Services\Trading\TradingRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TradingRepositoryInterface::class, function () {
            return new TradingRepository(
                db: $this->app['db']
            );
        });
    }
}

