<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;

Route::prefix('auth')->group(function () {
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/login', [AuthController::class, 'login']);
  Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
  Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
  Route::patch('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
});

Route::apiResource('users', UsersController::class);
