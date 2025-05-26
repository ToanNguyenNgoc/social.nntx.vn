<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Route::post('/broadcasting/auth', function (Request $request) {
            Log::info('Broadcast auth route called', ['user' => $request->user()]);
            return Broadcast::auth($request);
        })->middleware('auth:sanctum');

        require base_path('routes/channels.php');
    }
}
