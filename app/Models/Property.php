<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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


    public function scopeFilterByCountry(Builder $query, $country)
    {
        if ($country) {
            $query->where('countries.name', $country);
        }
    }

    public function scopeFilterByCity(Builder $query, $city)
    {
        if ($city) {
            $query->where('cities.name', $city);
        }
    }
    
    public function scopeFilterByPrice(Builder $query, $priceMin, $priceMax)
    {
        $query->leftJoin('ready_to_move_in_properties', function ($join) {
            $join->on('properties.id', '=', 'ready_to_move_in_properties.property_id')
                 ->where('properties.property_physical_status', '=', 'Ready To Move In')
                 ->leftJoin('purchases', function ($join) {
                    $join->on('ready_to_move_in_properties.id', '=', 'purchases.ready_property_id')
                         ->where('ready_to_move_in_properties.sell_type', '=', 'Purchase');
                 })
                 ->leftJoin('rents', function ($join) {
                    $join->on('ready_to_move_in_properties.id', '=', 'rents.ready_property_id')
                         ->where('ready_to_move_in_properties.sell_type', '=', 'Rent');
                });
        })

        ->leftJoin('off_plan_properties', function ($join) {
            $join->on('properties.id', '=', 'off_plan_properties.property_id')
                 ->where('properties.property_physical_status', '=', 'Off Plan');
        });

        
        if ($priceMin) {
            $query->where(DB::raw('COALESCE(purchases.price, rents.price, off_plan_properties.overall_payment)'), '>=', $priceMin);
        }
        if ($priceMax) {
            $query->where(DB::raw('COALESCE(purchases.price, rents.price, off_plan_properties.overall_payment)'), '<=', $priceMax);
        }
    }

}
