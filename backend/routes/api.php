<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SnippetController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    Route::apiResource('snippets', SnippetController::class);
    
    Route::patch('snippets/{snippet}/favorite', [SnippetController::class, 'toggleFavorite']);
});