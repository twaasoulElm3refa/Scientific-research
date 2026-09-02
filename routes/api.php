<?php

use App\Http\Controllers\api\admin\AuthController as AdminAuthController;
use App\Http\Controllers\api\admin\DocumentController;
use App\Http\Controllers\api\admin\DocumentLookupController;
use App\Http\Controllers\api\admin\GoogleDriveConnectionController;
use App\Http\Controllers\api\auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('google-drive/callback', [GoogleDriveConnectionController::class, 'callback'])
        ->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('google-drive', [GoogleDriveConnectionController::class, 'status']);
        Route::post('google-drive/authorize', [GoogleDriveConnectionController::class, 'authorize'])
            ->middleware('throttle:10,1');
        Route::post('google-drive/refresh', [GoogleDriveConnectionController::class, 'refresh'])
            ->middleware('throttle:10,1');
        Route::delete('google-drive', [GoogleDriveConnectionController::class, 'destroy'])
            ->middleware('throttle:10,1');
        Route::get('documents/lookups/{type}', [DocumentLookupController::class, 'index']);
        Route::post('documents/lookups/{type}', [DocumentLookupController::class, 'store'])
            ->middleware('throttle:30,1');
        Route::get('documents', [DocumentController::class, 'index']);
        Route::post('documents', [DocumentController::class, 'store'])->middleware('throttle:10,1');
        Route::patch('documents/{document}', [DocumentController::class, 'update'])->middleware('throttle:30,1');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->middleware('throttle:30,1');
    });
});

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
