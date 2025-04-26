<?php

namespace Tests\Feature;

use App\Services\Trading\GrokTradingService;
use Tests\TestCase;

class MyTest extends TestCase
{
    public function testGrok(): void
    {
        /** @var GrokTradingService $service */
        $service = app(GrokTradingService::class);
        $service->run();
    }
}
