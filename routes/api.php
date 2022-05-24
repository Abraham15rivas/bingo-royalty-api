<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Folder Admin
use App\Http\Controllers\Admin\{
    MatrixController
};

// Folder Admin
use App\Http\Controllers\PlayAssistant\{
    RequestController
};

// Folder User
use App\Http\Controllers\User\{
    CardboardController as CardboardControllerUser,
    WalletController as WalletControllerUser,
    AccountController as AccountControllerUser,
    UserProfileController,
    RequestController as RequestControllerUser
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

        // Group route: Admin
        Route::group([
            'prefix' => 'admin',
            'middleware' => 'admin',
        ], function () {
            // Matrix
            Route::get('matrices', [MatrixController::class, 'index']);
            Route::post('matrices', [MatrixController::class, 'store']);
        });

        // Group route: User
        Route::group([
            'prefix' => 'user',
            'middleware' => 'user',
        ], function () {
            // User
            Route::get('/',   [AuthController::class, 'user']);

            // Profile
            Route::prefix('profile')->group(function () {
                Route::get('/', [UserProfileController::class, 'show']);
                Route::post('/store', [UserProfileController::class, 'store']);

                // Account
                Route::resources(['accounts' => AccountControllerUser::class]);
            });

            // Cardboard
            Route::get('cardboards', [CardboardControllerUser::class, 'index']);
            Route::post('cardboards', [CardboardControllerUser::class, 'store']);
            Route::get('cardboard/group', [CardboardControllerUser::class, 'listCardboard']);
            Route::post('cardboard/group', [CardboardControllerUser::class, 'buyCardboard']);

            // Wallet
            Route::get('wallet', [WalletControllerUser::class, 'index']);
            Route::put('wallet', [WalletControllerUser::class, 'withdrawalOfFunds']);
            Route::post('wallet/balance', [WalletControllerUser::class, 'rechargeBalance']);
            Route::put('wallet/balance', [WalletControllerUser::class, 'transferBalance']);
            Route::get('wallet/activity', [WalletControllerUser::class, 'getActivities']);

            // Request
            Route::post('request', [RequestControllerUser::class, 'store']);
        });

        // Group route: User
        Route::group([
            'prefix' => 'play-assistant',
            'middleware' => 'playAssistant',
        ], function () {
            // Request
            Route::get('requests', [RequestController::class, 'index']);
            Route::put('requests/{id}', [RequestController::class, 'approveRequest']);
            Route::put('requests/{id}/reject', [RequestController::class, 'rejectRequest']);
        });
    });
});
