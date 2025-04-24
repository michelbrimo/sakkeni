<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidentialProperty extends Model
{
    protected $fillable = [
        "property_id",
        "bedrooms",
        "property_type",
    ];
    
    use HasFactory;
}
