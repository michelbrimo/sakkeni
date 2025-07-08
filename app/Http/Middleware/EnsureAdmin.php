<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
          if (!auth()->guard('admin')->check()) {
            return response()->json([
                'status' => false,
                'message' => 'You must be an admin to proceed'
            ], 403);
        }

        return $next($request);
    }
}