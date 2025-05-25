<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [AuthController::class, 'register'])->name('register')->middleware(['recaptcha']);
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
        Route::put('/comments/{id}', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
        //Favorite
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::get('/favorites/{id}', [FavoriteController::class, 'show'])->name('favorites.show');
        Route::post('/favorites', [FavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('/favorites', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
        //Topic
        Route::get('/topics', [TopicController::class, 'index'])->name('topics.index');
        Route::get('/topics/{id}', [TopicController::class, 'show'])->name('topics.show');
        Route::post('/topics', [TopicController::class, 'store'])->name('topics.store');
        Route::delete('/topics/{id}', [TopicController::class, 'destroy'])->name('topics.destroy');
        //Message
        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index')->middleware(['check_topic_joined']);
        Route::get('/messages/{id}', [MessageController::class, 'show'])->name('messages.show')->middleware(['check_topic_joined']);
        Route::post('/messages', [MessageController::class, 'store'])->name('messages.store')->middleware(['check_topic_joined']);
    });
});
