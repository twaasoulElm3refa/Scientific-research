<?php

use App\Http\Controllers\api\auth\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::prefix('v1')->group(function () {

    Route::prefix('users')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware(['throttle:3,1', 'guest']);
        Route::post('login', [AuthController::class, 'login'])->middleware(['throttle:3,1', 'guest']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
            ->middleware(['throttle:5,1', 'guest']);

        Route::post('reset-password', [AuthController::class, 'resetPassword'])
            ->middleware(['throttle:5,1', 'guest']);

        Route::middleware(['auth:sanctum', 'throttle:15,1'])->group(function () {
            Route::get('profile', [AuthController::class, 'profile']);
            Route::put('profile', [AuthController::class, 'updateProfile']);
            Route::put('password', [AuthController::class, 'updatePassword']);
        });
    });

});
