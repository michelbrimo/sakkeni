<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rent extends Model
{
    protected $fillable = [
        'property_id',
        'price',
        'lease_period_unit',
        'lease_period_value',
        'is_furnished'
    ];

    use HasFactory;
}
