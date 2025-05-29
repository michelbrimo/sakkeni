<?php
namespace App\Aspects;

use App\Models\UserSearch;
use Illuminate\Support\Facades\Auth;

class UserSearchTrackingAspect
{
    public function before($function_name)
    {
        if ($function_name !== 'filterProperties') return;

        $user_id =  auth()->user()->id;
        if (!$user_id) return;

        // $filters = request()->all(); //ma fhmt kif al filter 3m ymshi 

        if (!empty($filters)) {
            UserSearch::create([
                'user_id' => $user_id,
                'filters' => $filters
            ]);
        }
    }

    public function after($function_name) {}
    public function exception($function_name) {}
}
