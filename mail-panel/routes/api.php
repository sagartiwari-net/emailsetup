<?php

use App\Http\Controllers\Api\V1\SendController;
use App\Http\Middleware\AuthenticateApiKey;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(AuthenticateApiKey::class)->group(function () {
    Route::post('/send', [SendController::class, 'send']);
    Route::get('/status/{messageId}', [SendController::class, 'status']);
    Route::get('/stats/today', [SendController::class, 'todayStats']);
});
