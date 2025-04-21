<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\LinkRepository;
use App\Repositories\OfferRepository;
use App\Services\OfferRepositoryInterface;
use App\Services\ParsingService\LinkRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LinkRepositoryInterface::class, function () {
            return new LinkRepository(
                db: $this->app['db']
            );
        });

        $this->app->bind(OfferRepositoryInterface::class, function () {
            return new OfferRepository(
                db: $this->app['db']
            );
        });
    }
}

