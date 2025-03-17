<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UsersController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Route::group(['middleware' => ['auth:api']], function () {
//     Route::get('/me', [AuthController::class, 'me']);
//     Route::post('/refresh', [AuthController::class, 'refreshToken']);
//     Route::post('/logout', [AuthController::class, 'logout']);
// });

Route::apiResource('users', UsersController::class);

Route::get('/me', [AuthController::class, 'me']);
Route::post('/refresh', [AuthController::class, 'refreshToken']);
Route::post('/logout', [AuthController::class, 'logout']);


Route::apiResource('users', UsersController::class);
