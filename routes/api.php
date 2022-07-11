<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    PriceController
};

// Folder Admin
use App\Http\Controllers\Admin\{
    MatrixController,
    GamersController,
    AccountController as AccountControllerAdmin,
};

// Folder Supervisor
use App\Http\Controllers\Supervisor\{
    RequestController
};

// Folder User
use App\Http\Controllers\User\{
    CardboardController as CardboardControllerUser,
    WalletController as WalletControllerUser,
    AccountController as AccountControllerUser,
    ChangePasswordController,
    UserProfileController,
    ReferralController,
    RequestController as RequestControllerUser
};

// Folder PlayAssistant
use App\Http\Controllers\PlayAssistant\{
    GameController
};

// Group route: v1.0 Bingo Royal
Route::group([
    'prefix' => 'v1.0',
], function () {
    // Route: Auth
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/signup', [AuthController::class, 'signup']);

    // Send reset password mail
    Route::post('reset-password',  [AuthController::class, 'sendPasswordResetLink']);
    
    // handle reset password form process
    Route::post('reset/password', [AuthController::class, 'callResetPassword']);

    Route::group([
        'middleware' => 'auth:api',
    ], function () {
        Route::get('/logout', [AuthController::class, 'logout']);

        // User
        Route::get('/user', [AuthController::class, 'user']);

        // List Price
        Route::get('/prices', [PriceController::class, 'index']);

        // Group route: Admin
        Route::group([
            'prefix' => 'admin',
            'middleware' => 'admin',
        ], function () {
            // Matrix
            Route::get('matrices', [MatrixController::class, 'index']);
            Route::post('matrices', [MatrixController::class, 'store']);

            // Account
            Route::get('accounts', [AccountControllerAdmin::class, 'index']);
            //Route::get('account', [AccountControllerAdmin::class, 'show']);
            Route::post('account', [AccountControllerAdmin::class, 'store']);
            Route::put('account/update/{id}', [AccountControllerAdmin::class, 'update']);
            Route::delete ('account/{id}', [AccountControllerAdmin::class, 'destroy']);
            Route::put('account/active/{id}', [AccountControllerAdmin::class, 'activeAccount']);

            //Gamers
            Route::get('gamers', [GamersController::class, 'index']);
            Route::get('gamer/{id}', [GamersController::class, 'show']);
            Route::post('gamer', [GamersController::class, 'store']);
            Route::put('deactive/gamer/{id}', [GamersController::class, 'deactiveGamer']);

            Route::get('roles', [GamersController::class, 'indexRoles']);
           
            // List cardboards VIP
            Route::get('matrices/vip', [MatrixController::class, 'listCardboardVip']);
        });

        // Group route: User
        Route::group([
            'prefix' => 'user',
            'middleware' => 'user',
        ], function () {

            // Profile
            Route::prefix('profile')->group(function () {
                Route::get('/', [UserProfileController::class, 'show']);
                Route::post('store', [UserProfileController::class, 'store']);

                // Active - Deactive notifications
                Route::put('notifications', [UserProfileController::class, 'notifications']);

                // USER VIP
                Route::put('user-vip', [UserProfileController::class, 'userVip']);

                // Disable account
                Route::put('disableAccount', [UserProfileController::class, 'disableAccount']);

                // Referral
                Route::get('/r/{referralCode}',  [ReferralController::class, 'link']);
                
                // Change Password
                Route::post('change-password', [ChangePasswordController::class, 'store']);
                
                // Account
                Route::resources(['accounts'  => AccountControllerUser::class]);
                Route::put('accounts/active/{id}', [AccountControllerUser::class, 'activeAccount']);
                Route::get('accounts/active/', [AccountControllerUser::class, 'show']);
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

            //Accounts active by admin
            Route::get('accounts/admin', [AccountControllerUser::class, 'accountsAdmin']);

            // Requests
            Route::get('requests', [RequestControllerUser::class, 'index']);
            Route::post('request', [RequestControllerUser::class, 'store']);
        });

        // Group route: play-assistant
        Route::group([
            'prefix' => 'play-assistant',
            'middleware' => 'playAssistant',
        ], function () {
            Route::post('meeting', [GameController::class, 'createMeeting']);
        });

        // Group route: Supervisor
        Route::group([
            'prefix' => 'supervisor',
            'middleware' => 'supervisor',
        ], function () {
            // Request
            Route::get('requests', [RequestController::class, 'index']);
            Route::put('requests/{id}', [RequestController::class, 'approveRequest']);
            Route::put('requests/{id}/reject', [RequestController::class, 'rejectRequest']);
        });
    });
});
