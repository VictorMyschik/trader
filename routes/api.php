<?php

use App\Http\Controllers\Api\TelegramApiController;
use Illuminate\Support\Facades\Route;

Route::post('/telegram', [TelegramApiController::class, 'index'])->name('telegram.webhook');
