<?php

namespace Tests\Feature;

use GrokPHP\Client\Config\ChatOptions;
use GrokPHP\Client\Enums\Model;
use GrokPHP\Laravel\Facades\GrokAI;
use Tests\TestCase;

class MyTest extends TestCase
{
    /**
     * @test
     */
    public function testMy(): void
    {
        $response = GrokAI::chat(
            [['role' => 'user', 'content' => 'Привет Grok!']],
            new ChatOptions(model: Model::GROK_2)
        );

        $r = $response['choices'][0]['message']['content'];
    }
}
