<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
    });
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
            Route::get('/profile', [AuthController::class, 'profile'])->name('profile.show');
            Route::post('/profile-update', [AuthController::class, 'profileUpdate'])->name('profile.update');
        });
        Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/{id}', [UserController::class, 'show'])->name('show');
        });
        //Media
        Route::post('/media', [MediaController::class, 'store'])->name('media.store');
        //Flow
        Route::get('/follows', [FollowController::class, 'index'])->name('follows.index');
        Route::post('/follows', [FollowController::class, 'store'])->name('follows.store');
        Route::delete('/follows/{follower_user_id}', [FollowController::class, 'delete'])->name('follows.destroy');
        //Post
        Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
        Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
        Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
        Route::put('/posts/{id}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
        //Comment
        Route::get('/comments', [CommentController::class, 'index'])->name('comments.index');
        Route::get('/comments/{id}', [CommentController::class, 'show'])->name('comments.show');
        Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    });
});
