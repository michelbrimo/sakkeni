<?php

namespace App\Models;

use App\Enums\PropertyType;
use App\Enums\ResidentialPropertyType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Type\Integer;

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
        'physical_status_type_id',
        'property_type_id',
        'availability_status',
    ];

    public function directions() {
        return $this->belongsToMany(Direction::class);
    }

    public function amenities() {
        return $this->belongsToMany(Amenity::class);
    }


    public function scopefilterByLocation(Builder $query, $countryId, $cityId)
    {
        if ($countryId !== null) {$query->where('locations.country_id', $countryId);}
        if ($cityId !== null) {$query->where('locations.city_id', $cityId);}

        return $query;
    }
    
    public function scopefilterByRooms(Builder $query, $bathrooms, $balconies)
    {
        if ($bathrooms !== null) {$query->where('properties.bathrooms', $bathrooms);}
        if ($balconies !== null) {$query->where('properties.balconies', $balconies);}

        return $query;
    }
    
    public function scopeFilterByArea(Builder $query, $minArea, $maxArea)
    {
        if ($minArea !== null) {$query->where("properties.area", '>=', $minArea);}
        if ($maxArea !== null) {$query->where("properties.area", '<=', $maxArea);}

        return $query;
    }
    
    public function scopeFilterByAmenities(Builder $query, Array $amenityIds)
    {
        if (empty($amenityIds)) {return $query;}

        return $query->whereHas('amenities', function($q) use ($amenityIds) {
            $q->whereIn('amenities.id', $amenityIds);
        });
    }
    
    public function scopePurchaseFilters(Builder $query, $filters)
    {
        $this->_applyPriceFilter(
            $query,
            $filters['min_price'],
            $filters['max_price'],
            'purchases',
            'price'
        );

        if ($filters['is_furnished'] !== null) {
            $query->where("ready_to_move_in_properties.is_furnished", '=', $filters['is_furnished']);
        }

        return $query;
    }
    
    public function scopeRentFilters(Builder $query, $filters)
    {
        $this->_applyPriceFilter(
            $query,
            $filters['min_price'],
            $filters['max_price'],
            'rents',
            'price'
        );

        if ($filters['is_furnished'] !== null) {
            $query->where("ready_to_move_in_properties.is_furnished", '=', $filters['is_furnished']);
        }
        
        if ($filters['lease_period'] !== null) {
            $query->where("rents.lease_period", '=', $filters['lease_period']);
        }

        return $query;
    }
    
    public function scopeOffPlanFilters(Builder $query, $filters)
    {
        $this->_applyPriceFilter(
            $query,
            $filters['min_price'],
            $filters['max_price'],
            'off_plan_properties',
            'overall_payment'
        );
        
        $this->_applyPriceFilter(
            $query,
            $filters['min_first_pay'],
            $filters['max_first_pay'],
            'off_plan_properties',
            'first_pay'
        );

        if($filters['delivery_date'] !== null) {
            $query->where("off_plan_properties.delivery_date", '=', $filters['delivery_date']);
        }
        
        return $query;
    }
    
    protected function _applyPriceFilter(Builder $query, $minPrice, $maxPrice, $table, $column)
    {
        if ($minPrice !== null) {$query->where("$table.$column", '>=', $minPrice);}
        if ($maxPrice !== null) {$query->where("$table.$column", '<=', $maxPrice);}

        return $query;
    }



    protected function scopeFilterPropertyType(Builder $query, $filters)
    {
        if ($filters['_property_type_id'] == PropertyType::RESIDENTIAL) {
            $this->_applyResidentialFilter(
                $query,
                $filters['bedrooms'] ?? null,
                $filters['floors'] ?? null,
                $filters['floor'] ?? null,
                $filters['residential_property_type_id'] ?? null
            );
        }
        else if ($filters['_property_type_id'] == PropertyType::COMMERCIAL) {
            $this->_applyCommercialFilter(
                $query,
                $filters['floor'] ?? null,
                $filters['commercial_property_type_id'] ?? null
            );
        }

        return $query;
    }

    
    protected function _applyResidentialFilter(Builder $query, $bedrooms, $floors, $floor, $residentialPropertyTypeId)
    {
        if ($bedrooms !== null) {$query->where("bedrooms", $bedrooms);}
        
        if ($residentialPropertyTypeId == ResidentialPropertyType::APARTMENT) {            
            if ($floor !== null) {
                $query->where("floor", $floor);
            }
        }
        
        else if ($residentialPropertyTypeId == ResidentialPropertyType::VILLA) {            
            if ($floors !== null) {
                $query->where("floors", $floors);
            }
        }

        return $query;
    }
    
    protected function _applyCommercialFilter(Builder $query, $floor, $commercialPropertyTypeId)
    {
        if ($floor !== null) {$query->where("commercial_properties.floor", $floor);}
        if ($commercialPropertyTypeId !== null) {$query->where("commercial_properties.commercial_property_type_id", $commercialPropertyTypeId);}

        return $query;
    }
    

    



}
