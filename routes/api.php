<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
    });
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
            Route::get('/profile', [AuthController::class, 'profile'])->name('profile.show');
        });
        Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
        });
    });
});
