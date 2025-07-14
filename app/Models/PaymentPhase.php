<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPhase extends Model
{
    protected $fillable = ['name'];

    public function offPlanProperties()
    {
        return $this->belongsToMany(OffPlanProperty::class, 'off_plan_property_payment_phases')
            ->withPivot(['payment_percentage', 'payment_value', 'duration_value', 'duration_unit'])
            ->withTimestamps();
    }
}
