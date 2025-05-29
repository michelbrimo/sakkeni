<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $table = 'user_preferences_tabel';
    protected $fillable = [
        'user_id',
        'property_type_id',
        'min_bedrooms',
        'max_bedrooms',
        'min_bathrooms',
        'max_bathrooms',
        'min_balconies',
        'max_balconies',
        'min_area',
        'max_area',
        'min_price',
        'max_price',
        'preferred_locations',
        'must_have_features'
    ];

    protected $casts = [
        'preferred_locations' => 'array',
        'must_have_features' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }
}