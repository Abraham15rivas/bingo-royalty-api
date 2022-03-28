<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Folder Admin
use App\Http\Controllers\Admin\{
    MatrixController
};

// Folder User
use App\Http\Controllers\User\{
    CardboardController as CardboardControllerUser,
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
            // Matrix
            Route::get('matrix', [MatrixController::class, 'index']);
            Route::post('matrix', [MatrixController::class, 'store']);
        });

        // Group route: User
        Route::group([
            'prefix' => 'user',
            // 'middleware' => 'user',
        ], function () {
            // Cardboard
            Route::get('cardboard', [CardboardControllerUser::class, 'index']);
            Route::post('cardboard', [CardboardControllerUser::class, 'store']);
        });
    });
});
