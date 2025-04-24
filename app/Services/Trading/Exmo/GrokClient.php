<?php

declare(strict_types=1);

namespace App\Services\Trading\Exmo;

use App\Services\Trading\DTO\GrokResponseDto;
use App\Services\Trading\Enum\GrokActionEnum;
use GrokPHP\Client\Config\ChatOptions;
use GrokPHP\Client\Enums\Model;
use GrokPHP\Laravel\Facades\GrokAI;

final readonly class GrokClient
{
    public function send(string $message): GrokResponseDto
    {
        $response = GrokAI::chat(
            [['role' => 'user', 'content' => $message]],
            new ChatOptions(model: Model::GROK_2)
        );

        $str = $response->content();
        $str = str_replace('```json', '', $str);
        $str = str_replace('```', '', $str);
        $json = json_decode($str, true);

        return new GrokResponseDto(
            action: GrokActionEnum::from($json['action']),
            price: (float)$json['price'] ?? null,
            orderId: (int)$json['orderId'] ?? null,
            reason: $json['reason'] ?? null,
        );
    }
}
