<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TelegramAdvancedController;
use App\Http\Controllers\TelegramWebhookController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/telegram/send', [TelegramController::class, 'send']);

Route::get('/telegram/dashboard', [TelegramAdvancedController::class, 'dashboard']);
Route::get('/telegram/form', [TelegramAdvancedController::class, 'showForm']);
Route::post('/telegram/send-advanced', [TelegramAdvancedController::class, 'sendAdvanced']);
Route::post('/telegram/send-bulk', [TelegramAdvancedController::class, 'sendBulk']);
Route::post('/telegram/send-subscribers', [TelegramAdvancedController::class, 'sendToSubscribers']);
Route::post('/telegram/send-image', [TelegramAdvancedController::class, 'sendWithImage']);
Route::post('/telegram/send-keyboard', [TelegramAdvancedController::class, 'sendWithInlineKeyboard']);

Route::get('/telegram/bot-info', [TelegramAdvancedController::class, 'getBotInfo']);
Route::get('/telegram/updates', [TelegramAdvancedController::class, 'getUpdates']);
Route::post('/telegram/set-webhook', [TelegramAdvancedController::class, 'setWebhook']);
Route::get('/telegram/analytics', [TelegramAdvancedController::class, 'getAnalytics']);
Route::get('/telegram/subscribers', [TelegramAdvancedController::class, 'getSubscribers']);

// Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);
Route::match(['get', 'post'], '/telegram/webhook', [TelegramWebhookController::class, 'handle']);