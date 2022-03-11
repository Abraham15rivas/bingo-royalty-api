<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;


Route::group([
    'prefix' => 'v1',
], function () {

    Route::get('/users',function (Request $request){
        return User::all();
    });

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::get('/user/{id}', [ProfileController::class, 'show']);
    Route::group([
        'middleware' => 'auth:api',
    ], function () {
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::get('/user',   [AuthController::class, 'user']);

        
    });
});

