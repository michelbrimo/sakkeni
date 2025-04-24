<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appartment extends Model
{
    protected $fillable = [
        "residential_property_id",
        "floor",
        "building_number",
        "appartment_number",
    ];
    use HasFactory;
}
