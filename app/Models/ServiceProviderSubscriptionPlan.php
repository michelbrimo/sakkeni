<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProviderSubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id',
        'subscription_plan_id',
        'start_date',
        'end_date'
    ];
}
