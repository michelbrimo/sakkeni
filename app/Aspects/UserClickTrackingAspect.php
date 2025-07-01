<?php 
namespace App\Aspects;

use App\Models\UserClick;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class UserClickTrackingAspect
{
    public function before($function_name)
    {
        if ($function_name !== 'viewPropertyDetails') {
            return false;
        }

        $user_id = auth()->user()->id;
        if (!$user_id) {
            return false;
        }

        $property_id = request()->route('property_id');
        
        if (is_object($property_id) || is_array($property_id)) {
            $property_id = $property_id['id'] ?? $property_id->id ?? null;
        }

        if (!$property_id) {
            return false;
        }

        $click = UserClick::firstOrNew([
            'user_id' => $user_id,
            'property_id' => $property_id // Now this will be just the ID
        ]);
        // If this is a new record
        if (!$click->exists) {
            $click->fill([
                'click_count' => 1,
                'session_start' => now(),
                'session_duration' => 0
            ])->save();
            return true;
        }

        // Update existing record
        $click->click_count += 1;

        $duration = now()->diffInSeconds($click->session_start);
        $sessionTimeout = 1800; // 30 minutes in seconds

        if ($duration < $sessionTimeout) {
            $click->session_duration += $duration;
        } else {
            $click->session_start = now();
        }

        $click->save();
        return true;
    }

    public function after($function_name) { }

   public function exception($function_name, \Throwable $exception = null)
    {
        
    }
}
