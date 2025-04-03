<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/chat/history', [ChatController::class, 'chatHistory']);
    Route::post('/chat', [ChatController::class, 'chat']);
});
