<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/chat/history', [ChatController::class, 'chatHistory']);
    Route::post('/chat', [ChatController::class, 'chat']);
    Route::get('/user/profile', [ProfileController::class, 'show']);
    Route::patch('/user/profile', [ProfileController::class, 'updateProfile']);
});
