<?php 
namespace App\Aspects;

use App\Models\UserClick;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserClickTracking
{
    public function before($function_name)
    {
        if ($function_name !== 'viewProperty') return;

        $user_id =  auth()->user()->id;
        $property_id = request()->route('id'); //it will need editing if we used another params in our  route 

        if (!$user_id || !$property_id) return;

        $click = UserClick::where('user_id', $user_id)
            ->where('property_id', $property_id)
            ->first();

        if (!$click) {
            UserClick::create([
                'user_id' => $user_id->id,
                'property_id' => $property_id,
                'click_count' => 1,
                'session_start' => now(),
                'session_duration' => 0
            ]);
            return;
        }

        $click->click_count += 1;

        $duration = now()->diffInSeconds(Carbon::parse($click->session_start));

        if ($duration < 1800) {
            $click->session_duration += $duration;
        } else {
            $click->session_start = now();
        }

        $click->save();
    }

    public function after($function_name) { }

    public function exception($function_name) { }
}
