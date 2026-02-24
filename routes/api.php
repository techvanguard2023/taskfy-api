<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::prefix('v1')->group(function () {

    Route::get('status', function () {
        return response()->json(['status' => 'API V1 Taskfy is alive!'], 200);
    });

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('users/phone/{phone}', [UserController::class, 'findByPhone']);
        Route::apiResource('users', UserController::class);
    });

});
