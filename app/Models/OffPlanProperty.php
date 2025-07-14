<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OffPlanProperty extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        // 'first_pay',
        'delivery_date',
        'overall_payment',
        // 'pay_plan'
    ];

    public function paymentPhases()
    {
        return $this->belongsToMany(PaymentPhase::class, 'off_plan_property_payment_phases')
            ->withPivot(['payment_percentage', 'payment_value', 'duration_value', 'duration_unit'])
            ->withTimestamps();
    }

}
