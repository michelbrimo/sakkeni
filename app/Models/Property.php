<?php

namespace App\Models;

use App\Enums\PropertyType;
use App\Enums\ResidentialPropertyType;
use App\Models\PropertyType as ModelsPropertyType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;
use Ramsey\Uuid\Type\Integer;

class Property extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'location_id',
        'owner_id',
        'area',
        'bathrooms',
        'balconies',
        'ownership_type_id',
        'physical_status_type_id',
        'property_type_id',
        'sell_type_id',
        'availability_status_id',
        'tags'
    ];



    public function toSearchableArray(): array
    {
        $this->load(
            'location.city', 'location.country', 'propertyType', 'amenities', 
            'sellType', 'ownershipType', 'directions', 'residential.residentialPropertyType',
            'commercial.commercialPropertyType'
        );

        $isFurnished = $this->purchase->is_furnished ?? $this->rent->is_furnished ?? false;


        return [
            'id'            => $this->id,
            'description'   => $this->description,
            'tags'          => $this->tags,
            'is_furnished'  => $isFurnished, 

            // Location Details
            'city'          => $this->location->city->name ?? null,
            'country'       => $this->location->country->name ?? null,
            // Property Type Details
            'property_type' => $this->propertyType->name ?? null,
            'sell_type'     => $this->sellType->name ?? null,
            'ownership_type'=> $this->ownershipType->name ?? null,
            // Property Attributes
            'area'          => $this->area,
            'bathrooms'     => $this->bathrooms,
            'balconies'     => $this->balconies,
            // Relational Details
            'amenities'     => $this->amenities->pluck('name')->all(),
            'directions'    => $this->directions->pluck('name')->implode(', '),
            // Residential/Commercial Specifics
            'bedrooms'      => $this->residential->bedrooms ?? null,
            'residential_type' => $this->residential->residentialPropertyType->name ?? null,
            'commercial_type'  => $this->commercial->commercialPropertyType->name ?? null,

        ];
    }

    public function sellType()
    {
        return $this->belongsTo(SellType::class);
    }

    public function directions() {
        return $this->belongsToMany(Direction::class);
    }

    public function amenities() {
        return $this->belongsToMany(Amenity::class);
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function coverImage()
    {
        return $this->hasOne(PropertyImage::class)->orderBy('id');
    }
    
    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function propertyType()
    {
        return $this->belongsTo(ModelsPropertyType::class);
    }
    
    public function availabilityStatus()
    {
        return $this->belongsTo(AvailabilityStatus::class);
    }
    
    public function ownershipType()
    {
        return $this->belongsTo(OwnershipType::class);
    }
    
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function residential()
    {
        return $this->hasOne(ResidentialProperty::class);
    }

    public function commercial()
    {
        return $this->hasOne(CommercialProperty::class);
    }
    
    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

    public function rent()
    {
        return $this->hasOne(Rent::class);
    }

    public function offPlan()
    {
        return $this->hasOne(OffPlanProperty::class);
    }

    public function propertyAdmin()
    {
        return $this->hasOne(PropertyAdmin::class);
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
            $query->where("purchases.is_furnished", '=', $filters['is_furnished']);
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
            $query->where("rents.is_furnished", '=', $filters['is_furnished']);
        }
        
        if ($filters['lease_period_unit'] !== null) {
            $query->where("rents.lease_period_unit", '=', $filters['lease_period_unit']);
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
        if (isset($filters['property_type_id'])) {
            if ($filters['property_type_id'] == PropertyType::RESIDENTIAL) {
                $this->_applyResidentialFilter(
                    $query,
                    $filters['bedrooms'] ?? null,
                    $filters['residential_property_type_id'] ?? null
                );
            }
            else if ($filters['property_type_id'] == PropertyType::COMMERCIAL) {
                $this->_applyCommercialFilter(
                    $query,
                    $filters['commercial_property_type_id'] ?? null
                );
            }
        }
        
        return $query;
    }

    
    protected function _applyResidentialFilter(Builder $query, $bedrooms, $residentialPropertyTypeId)
    {
        if ($bedrooms !== null) {$query->where("bedrooms", $bedrooms);}
        if ($residentialPropertyTypeId !== null) {$query->where("residential_property_type_id", $residentialPropertyTypeId);}

        return $query;
    }
    
    protected function _applyCommercialFilter(Builder $query, $commercialPropertyTypeId)
    {
        if ($commercialPropertyTypeId !== null) {$query->where("commercial_properties.commercial_property_type_id", $commercialPropertyTypeId);}

        return $query;
    }
    

    public function similarityScores()
    {
        return $this->hasMany(PropertySimilarity::class, 'property_id_1');
    }

    public function popularityMetrics()
    {
        return $this->hasOne(PropertyPopularityMetrics::class);
    }

    public function favorites()
    {
        return $this->hasMany(PropertyFavorite::class);
    }

    public function reportOnService()
    {
        return $this->morphMany(ReportOnService::class, 'reportable');
    }

}
