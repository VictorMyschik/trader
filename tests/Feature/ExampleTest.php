<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Trading\RunnerService;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testMy(): void
    {
        /** @var RunnerService $service */
        $service = app(RunnerService::class);
        $service->runJob();
    }
}
