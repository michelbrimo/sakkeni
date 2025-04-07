<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'owner_id',
        'area',
        'bathrooms',
        'balconies',
        'ownership_type',
        'property_physical_status',
        'availability_status',
    ];

    public function directions() {
        return $this->belongsToMany(Direction::class);
    }

    public function amenities() {
        return $this->belongsToMany(Amenity::class);
    }
}
