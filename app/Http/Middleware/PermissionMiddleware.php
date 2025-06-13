<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $permission, $guard = 'sanctum')
    {
        $authGuard = auth($guard);

        if ($authGuard->guest()) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthenticated',
                'context' => null
            ], 401);
        }
        if ($authGuard->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
            return $next($request);
        }
        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);

        foreach ($permissions as $permission) {
            if ($authGuard->user()->can($permission)) {
                return $next($request);
            }
        }
        return response()->json([
            'status' => 403,
            'message' => 'User does not have the right permissions.',
            'context' => null
        ], 403);
    }
}
