<?php

namespace App\Http\Middleware;

use App\Enums\AvailabilityStatus;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsServiceProvider
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::join('service_providers', 'service_providers.user_id', '=', 'users.id')
                    ->where('users.id', auth()->user()->id)
                    ->where('service_providers.availability_status_id', AvailabilityStatus::Active)
                    ->first();
        
        if($user){
            return $next($request);
        }

        return response()->json([
            'status' => false,
            'message' => 'You do not have permission to perform this action.',
        ], 403);    
    }
}
