<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;

//Route::get('/user', [UserController::class, 'index']);


Route::group([
    'prefix' => 'v1',
], function () {
    Route::get('/users',function (Request $request){
        return User::all();
    }); 
    /*Route::group([
        'middleware' => 'auth:api',
    ], function () {

    });*/
});

