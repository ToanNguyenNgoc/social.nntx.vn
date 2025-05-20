<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class ApiAuthenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        if (! $request->expectsJson()) {
            abort(response()->json([
                'status' => 401,
                'message' => 'Unauthenticated.',
                'context' => null
            ], 401));
        }
        return null;
    }
    public function handle($request, \Closure $next, ...$guards)
    {
        return parent::handle($request, $next, ...$guards);
    }
}
