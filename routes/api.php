<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\Admin\{
    CardboardController
};

// Group route: v1.0 Bingo Royal
Route::group([
    'prefix' => 'v1.0',
], function () {
    // Route: Auth
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/signup', [AuthController::class, 'signup']);

    Route::group([
        'middleware' => 'auth:api',
    ], function () {
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::get('/user',   [AuthController::class, 'user']);


        // Group route: Admin
        Route::group([
            'prefix' => 'admin',
            // 'middleware' => 'admin',
        ], function () {
            Route::get('cardboard', [CardboardController::class, 'index']);
        });

    });
});
