<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OptionalSanctum
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken()) {
            auth()->setUser(
                \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken())?->tokenable
            );
        }

        return $next($request);
    }
}