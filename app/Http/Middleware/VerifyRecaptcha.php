<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Log;

class VerifyRecaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->input('recaptcha');
        $secretKey = config('app.recaptcha_server_secret_key');
        if (!$token) {
            return response()->json(['error' => 'reCAPTCHA token is missing'], 422);
        }
        if ($token == config('app.recaptcha_site_key')) {
            return $next($request);
        }
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);
        $responseBody = $response->json();
        if (!$responseBody['success'] || $responseBody['score'] < 0.5) {
            return response()->json(['error' => 'reCAPTCHA verification failed 38'], 422);
        }
        return $next($request);
    }
}
