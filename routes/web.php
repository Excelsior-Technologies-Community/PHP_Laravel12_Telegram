<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TelegramAdvancedController;

Route::get('/', function () {
    return view('welcome');
});

// Basic route
Route::get('/telegram/send', [TelegramController::class, 'send']);

// Advanced routes with simple UI
Route::get('/telegram/dashboard', [TelegramAdvancedController::class, 'dashboard']);
Route::get('/telegram/form', [TelegramAdvancedController::class, 'showForm']);
Route::post('/telegram/send-advanced', [TelegramAdvancedController::class, 'sendAdvanced']);
Route::get('/telegram/bot-info', [TelegramAdvancedController::class, 'getBotInfo']);
Route::get('/telegram/updates', [TelegramAdvancedController::class, 'getUpdates']);