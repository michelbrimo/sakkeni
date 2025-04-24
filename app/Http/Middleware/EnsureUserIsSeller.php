<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSeller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::join('sellers', 'sellers.user_id', '=', 'users.id')->get();
        
        if(count($user) > 0){
            return $next($request);
        }

        return response()->json([
            'status' => false,
            'message' => 'You do not have permission to perform this action.',
        ], 403);
    }
}
