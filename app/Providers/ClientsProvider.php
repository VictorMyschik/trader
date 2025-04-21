<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\ParsingService\Sites\Maxon\API\MaxonClient;
use App\Services\ParsingService\Sites\Maxon\MaxonClientInterface;
use App\Services\ParsingService\Sites\OLX\API\OlxClient;
use App\Services\ParsingService\Sites\OLX\OlxClientInterface;
use App\Services\ParsingService\Sites\Realting\API\RealtingClient;
use App\Services\ParsingService\Sites\Realting\RealtingClientInterface;
use App\Services\Telegram\ClientInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class ClientsProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OlxClientInterface::class, function () {
            return new OlxClient(
                client: new Client(),
                log: Log::channel(env('LOG_CHANNEL')),
            );
        });

        $this->app->bind(MaxonClientInterface::class, function () {
            return new MaxonClient(
                client: new Client(),
                log: Log::channel(env('LOG_CHANNEL')),
            );
        });

        $this->app->bind(RealtingClientInterface::class, function () {
            return new RealtingClient(
                client: new Client(),
                log: Log::channel(env('LOG_CHANNEL')),
            );
        });

        $this->app->bind(ClientInterface::class, function () {
            return new \App\Services\Telegram\Api\Client();
        });
    }
}
