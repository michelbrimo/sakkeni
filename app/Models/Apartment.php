<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    protected $fillable = [
        "residential_property_id",
        "floor",
        "building_number",
        "apartment_number",
    ];
    use HasFactory;
}
