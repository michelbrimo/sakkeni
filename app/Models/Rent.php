<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rent extends Model
{
    protected $fillable = [
        'price',
        'ready_property_id',
        'lease_period',
        'payment_plan',
    ];

    use HasFactory;
}
