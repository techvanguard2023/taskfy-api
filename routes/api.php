<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WpSettingController;

Route::prefix('v1')->group(function () {

    Route::get('status', function () {
            return response()->json(['status' => 'API V1 Taskfy is alive!'], 200);
        }
        );

        Route::post('login', [AuthController::class , 'login']);
        Route::post('register', [AuthController::class , 'register']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class , 'logout']);
            Route::get('/me', [AuthController::class , 'me']);

            Route::middleware('role:admin')->group(function () {
                    Route::get('users/phone/{phone}', [UserController::class , 'findByPhone']);
                    Route::apiResource('users', UserController::class);
                }
                );

                Route::apiResource('tasks', TaskController::class);
                Route::delete('wp-settings/name/{name}', [WpSettingController::class , 'destroyByName']);
                Route::patch('wp-settings/name/{name}', [WpSettingController::class , 'updateByName']);
                Route::apiResource('wp-settings', WpSettingController::class);
            }
            );
        });
