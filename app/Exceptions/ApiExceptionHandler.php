<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;

class ApiExceptionHandler extends Exception
{
    //
    public function __invoke(Exceptions $exceptions): void
    {
        // Unauthenticated
        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        });

        // AuthorizationException
        $exceptions->render(function (AuthorizationException $e, $request) {
            return response()->json(['message' => 'Forbidden.'], 403);
        });

        // Model not found
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            return response()->json(['message' => 'Resource not found.'], 404);
        });
    }
}
