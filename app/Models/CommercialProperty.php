<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommercialProperty extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'floor',
        'building_number',
        'apartment_number',
        'commercial_property_type_id'
    ];
}
