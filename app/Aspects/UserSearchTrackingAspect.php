<?php
namespace App\Aspects;

use App\Models\UserSearch;
use Illuminate\Support\Facades\Auth;

class UserSearchTrackingAspect
{
    public function before($function_name)
    {
        if ($function_name !== 'filterProperties') return;

        $user =  auth()->user();
        if (!$user || !$user->id) {
            return false;
        }

        $user_id = $user->id;

        $filters = request()->all(); 

        $sell_type_id = request()->route('sell_type');

        
        if (!empty($filters)) {
            UserSearch::create([
                'user_id' => $user_id,
                'sell_type_id' => $sell_type_id,
                'filters' => $filters,
                'created_at' => now() 
            ]);
        }
    }

    public function after($function_name) {}
    public function exception($function_name) {}
}
