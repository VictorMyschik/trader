<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\Telegram\TelegramService;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;

final class TelegramApiController
{
    public function __construct(
        private readonly TelegramService $telegramService,
        private readonly LoggerInterface $logger,
    ) {}

    public function index(Request $request): void
    {
        $body = $request->all();
        $message = (string)$body['message']['text'];
        $user = (string)$body['message']['chat']['id'];

        $this->logger->info(json_encode($body, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        try {
            $this->telegramService->manageBot($user, $message);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            $this->telegramService->sendRawMessage($user, 'Error: ' . $e->getMessage());
        }

        $this->telegramService->sendRawMessage($user, 'Done!');
    }
}
