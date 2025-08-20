<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidentialProperty extends Model
{
    protected $fillable = [
        "property_id",
        "bedrooms",
        "residential_property_type_id",
    ];
    
    public function residentialPropertyType()
    {
        return $this->belongsTo(ResidentialPropertyType::class);
    }

    public function villa()
    {
        return $this->hasOne(Villa::class);
    }

    public function apartment()
    {
        return $this->hasOne(Apartment::class);
    }


    use HasFactory;
}
