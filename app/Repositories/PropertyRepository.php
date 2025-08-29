<?php

namespace App\Repositories;

use App\Enums\AvailabilityStatus;
use App\Enums\PropertyType;
use App\Enums\ResidentialPropertyType;
use App\Enums\SellType;
use App\Models\Amenity;
use App\Models\Apartment;
use App\Models\AvailabilityStatus as ModelsAvailabilityStatus;
use App\Models\CommercialProperty;
use App\Models\CommercialPropertyType;
use App\Models\Country;
use App\Models\Direction;
use App\Models\Location;
use App\Models\Log;
use App\Models\OffPlanProperty;
use App\Models\OwnershipType;
use App\Models\Property;
use App\Models\PropertyAdmin;
use App\Models\PropertyFavorite;
use App\Models\PropertyType as ModelsPropertyType;
use App\Models\Purchase;
use App\Models\Rent;
use App\Models\ResidentialProperty;
use App\Models\ResidentialPropertyType as ModelsResidentialPropertyType;
use App\Models\Villa;
use App\Services\SearchDictionaryService;

class PropertyRepository{
    public function create($data) {
        return Property::create($data);
    }

    public function editProperty($data) {
        return Property::where($data)->update();
    }

    public function createOffPlanProperty($data) {
        $offPlan = OffPlanProperty::create([
            'property_id' => $data['property_id'],
            'delivery_date' => $data['delivery_date'],
            'overall_payment' => $data['overall_payment'],
        ]);

        $overall = $data['overall_payment'];

        foreach ($data['payment_plan'] as $phase) {
            $percentage = $phase['payment_percentage'];
            $value = round(($percentage / 100) * $overall, 2);

            $offPlan->paymentPhases()->attach($phase['payment_phase_id'], [
                'payment_percentage' => $percentage,
                'payment_value' => $value,
                'duration_value' => $phase['duration_value'] ?? null,
                'duration_unit' => $phase['duration_unit'] ?? null,
            ]);
        }
        return $offPlan;
    }

    public function createRent($data) {
        return Rent::create($data);
    }
    
    public function createPurchase($data) {
        return Purchase::create($data);
    }

    public function createCommercialProperty($data) {
        return CommercialProperty::create($data);
    }

    public function createResidentialProperty($data) {
        return ResidentialProperty::create($data);
    }

    public function createVilla($data) {
        return Villa::create($data);
    }

    public function createPropertyAdmin($data) {
        return PropertyAdmin::create($data);
    }

    public function createApartment($data) {
        return Apartment::create($data);
    }
    
    public function createPropertyFavorite($data) {
        return PropertyFavorite::create($data);
    }
    
    public function deletePropertyFavorite($data) {
        return PropertyFavorite::where('user_id', $data['user_id'])
                               ->where('property_id', $data['property_id'])
                               ->delete();
    }

    public function getPropertyFavorite($propertyId, $userId) {
        return PropertyFavorite::where('property_id', $propertyId)
                               ->where('user_id', $userId)
                               ->first();
    }
    
    public function getFavoriteProperties($data) {
        $query = PropertyFavorite::where('user_id', $data['user_id'])
                    ->whereHas('property', function($q) use ($data) {
                        $q->where('sell_type_id', $data['sell_type_id']);
                    })
                    ->with([
                        'property.coverImage',
                        'property.availabilityStatus',
                        'property.owner',
                        'property.propertyType',
                        'property.location.country',
                        'property.location.city',
                        'property.residential.residentialPropertyType',
                        'property.commercial.commercialPropertyType',
                    ]);

        if($data['sell_type_id'] == SellType::OFF_PLAN)
            $query->with('property.offPlan');
        else if($data['sell_type_id'] == SellType::RENT)
            $query->with('property.rent');
        else if($data['sell_type_id'] == SellType::PURCHASE)
            $query->with('property.purchase');


        return $query->simplePaginate(10, [
                'id',
                'property_id',
        ], 'page', $data['page'] ?? 1);
    }

    public function updateProperty($id, $data){
        Property::where('id', $id)
                ->update($data);
    }

    public function updatePropertyLocation($id, $data){
        Location::where('id', $id)
                ->update($data);
    }
    
    public function updatePurchase($id, $data){
        Purchase::where('property_id', $id)
                ->update($data);
    }
    
    public function updateRent($id, $data){
        Rent::where('property_id', $id)
                ->update($data);
    }
    
    public function updateOffPlan($id, $data){
        $offPlan = OffPlanProperty::where('property_id', $id)->first();

        if (!$offPlan) {
            return;
        }
        $offPlan->update([
            'delivery_date' => $data['delivery_date'] ?? $offPlan->delivery_date,
            'overall_payment' => $data['overall_payment'] ?? $offPlan->overall_payment,
        ]);

        if (!empty($data['payment_plan'])) {
            $syncData = [];
            $overall = $data['overall_payment'] ?? $offPlan->overall_payment;

            foreach ($data['payment_plan'] as $phase) {
                $percentage = $phase['payment_percentage'];
                $value = round(($percentage / 100) * $overall, 2);

                $syncData[$phase['payment_phase_id']] = [
                    'payment_percentage' => $percentage,
                    'payment_value' => $value,
                    'duration_value' => $phase['duration_value'] ?? null,
                    'duration_unit' => $phase['duration_unit'] ?? null,
                ];
            }

            $offPlan->paymentPhases()->sync($syncData);
        }
    }
    
    public function updateCommercialProperty($id, $data){
        CommercialProperty::where('property_id', $id)
                ->update($data);
    }

    public function updateResidentialProperty($id, $data){
        ResidentialProperty::where('property_id', $id)
                ->update($data);
    }

    public function updateVilla($id, $data){
        Villa::where('residential_property_id', $id)
                ->update($data);
    }

    public function updateApartment($id, $data){
        Apartment::where('residential_property_id', $id)
                ->update($data);
    }

    public function getBasePropertyDetails($propertyId){
        return Property::where('id', $propertyId)->first();
    }
    
    public function getProperties($data)
    {
        $query = Property::query();
        
        if(isset($data['owner_id'])){
            $query->where('owner_id', $data['owner_id']);
        }

        $query->where('sell_type_id', $data['sell_type_id'])
              ->with([
                    'coverImage',
                    'availabilityStatus',
                    'owner',
                    'propertyType',
                    'location.country',
                    'location.city',
                    'residential.residentialPropertyType',
                    'commercial.commercialPropertyType',
                ]);

        if(isset($data['user_id'])){
            $query->with(['favorites' => function($query) {
                $query->where('user_id', auth()->user()->id);   
            }]);
        }
        else{
            $query->with(['favorites' => function($query) {
                $query->where('user_id', -1);
            }]);
        }
        
        if($data['sell_type_id'] == SellType::OFF_PLAN)
            $query->with('offPlan');
        else if($data['sell_type_id'] == SellType::RENT)
            $query->with('rent');
        else if($data['sell_type_id'] == SellType::PURCHASE)
            $query->with('purchase');


        return $query->simplePaginate(10, [
                    'id',
                    'location_id',
                    'property_type_id',
                    'owner_id',
                    'availability_status_id',
            ], 'page', $data['page'] ?? 1);

    }

    public function filterPurchaseProperties($filters)
    {
        $query = Property::query(); 
        
        if(isset($data['owner_id'])){
            $query->where('owner_id', $data['owner_id']);
        }
                    
        $this->_joinNeededTables(
            $query,
            $filters,
            SellType::PURCHASE,
        );

        $this->_basePropertyfiltering($query, $filters);

        $query->purchaseFilters([
            'min_price' => $filters['min_price'] ?? null,
            'max_price' => $filters['max_price'] ?? null,
            'is_furnished' => $filters['is_furnished'] ?? null
            ])

            ->with([
                'coverImage',
                'availabilityStatus',
                'owner',
                'propertyType',
                'location.country',
                'location.city',
                'purchase',
                'residential.residentialPropertyType',
                'commercial.commercialPropertyType',
            ]);
            
            if(isset($filters['user_id'])){
                $query->with(['favorites' => function($query) {
                    $query->where('user_id', auth()->user()->id);   
                }]);
            }
            
            else{
                $query->with(['favorites' => function($query) {
                    $query->where('user_id', -1);
                }]);
            }

            return $query->simplePaginate(10, [
                'properties.id',
                'properties.location_id',
                'properties.property_type_id',
                'properties.owner_id',
                'properties.availability_status_id',
            ], 'page', $data['page'] ?? 1);
    }
    
    public function filterRentProperties($filters)
    {
        $query = Property::query(); 

        if(isset($data['owner_id'])){
            $query->where('owner_id', $data['owner_id']);
        }

        $this->_joinNeededTables(
            $query,
            $filters,
            SellType::RENT,
        );
        $this->_basePropertyfiltering($query, $filters);

        return $query->rentFilters([
            'min_price' => $filters['min_price'] ?? null,
            'max_price' => $filters['max_price'] ?? null,
            'is_furnished' => $filters['is_furnished'] ?? null,
            'lease_period_unit' => $filters['lease_period_unit'] ?? null
            ])
            
            ->with([
                'coverImage',
                'availabilityStatus',
                'owner',
                'propertyType',
                'location.country',
                'location.city',
                'rent',
                'residential.residentialPropertyType',
                'commercial.commercialPropertyType',
                'favorites' => function($query) {
                    $query->where('user_id', auth()->user()->id);
                }
            ])

            ->simplePaginate(10, [
                'properties.id',
                'properties.location_id',
                'properties.property_type_id',
                'properties.owner_id',
                'properties.availability_status_id',
            ], 'page', $data['page'] ?? 1);
    }

    public function filterOffPlanProperties($filters)
    {
        $query = Property::query(); 
        
        if(isset($data['owner_id'])){
            $query->where('owner_id', $data['owner_id']);
        }

        $this->_joinNeededTables(
            $query,
            $filters,
            SellType::OFF_PLAN,
        );
        $this->_basePropertyfiltering($query, $filters);

        return $query->offPlanFilters([
                'min_price' => $filters['min_price'] ?? null,
                'max_price' => $filters['max_price'] ?? null,
                'min_first_pay' => $filters['min_first_pay'] ?? null,
                'max_first_pay' => $filters['max_first_pay'] ?? null,
                'delivery_date' => $filters['delivery_date'] ?? null,
                ])

            ->with([
                'coverImage',
                'availabilityStatus',
                'owner',
                'propertyType',
                'location.country',
                'location.city',
                'offPlan',
                'residential.residentialPropertyType',
                'commercial.commercialPropertyType',
                'favorites' => function($query) {
                    $query->where('user_id', auth()->user()->id);
                }
            ])

            ->simplePaginate(10, [
                'properties.id',
                'properties.location_id',
                'properties.property_type_id',
                'properties.owner_id',
                'properties.availability_status_id',
            ], 'page', $data['page'] ?? 1);
    }

    function viewPropertyDetails($data) {
        Property::where('id', $data['id'])->increment('users_clicks');
        $query = Property::query()->where('properties.id', $data['id']);

        $query = $query->with(
            'amenities',
            'directions',
            'images',
            'propertyType',
            'availabilityStatus',
            'ownershipType',
            'owner',
            'location.country',
            'location.city', 
            
        );

        if($data['property_type_id'] == PropertyType::RESIDENTIAL)
            $query = $query->with('residential.residentialPropertyType');
        else if ($data['property_type_id'] == PropertyType::COMMERCIAL)
            $query = $query->with('commercial.commercialPropertyType');

        if($data['sell_type_id'] == SellType::PURCHASE)
            $query = $query->with('purchase');
        else if ($data['sell_type_id'] == SellType::RENT)
            $query = $query->with('rent');
        else if ($data['sell_type_id'] == SellType::OFF_PLAN)
            $query = $query->with(['offPlan.paymentPhases']);


        $query = $query->first();
        
        return $query;
    }

    function deleteProperty($data) {
        Property::where('id', $data['id'])->delete();
        return;
    }

    function viewPendingProperties($data) {
        $query = Property::where('availability_status_id', AvailabilityStatus::Pending)
                        ->with([
                            'coverImage',
                            'availabilityStatus',
                            'owner',
                            'propertyType',
                            'location.country',
                            'location.city',
                            'residential.residentialPropertyType',
                            'commercial.commercialPropertyType',
                            'offPlan',
                            'Rent',
                            'Purchase'
                        ]);


        return $query->simplePaginate(10, [
                    'id',
                    'location_id',
                    'property_type_id',
                    'owner_id',
                    'availability_status_id',
        ], 'page', $data['page'] ?? 1);
    }

    function viewLatestAcceptedProperty($page) {
        return PropertyAdmin::where('approve', 1)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->with(['property.owner', 'property.location.city', 'property.location.country', 'admin'])
                    ->orderBy('created_at', 'desc') 
                    ->simplePaginate(10, '*', 'page', $page?? 1);
    }

    function viewLatestRejectedProperty($page) {
        return PropertyAdmin::where('approve', 0)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->with(['property.owner', 'property.location.city', 'property.location.country', 'admin'])
                    ->orderBy('created_at', 'desc') 
                    ->simplePaginate(10, '*', 'page', $page?? 1);
    }

    function viewLatestPropertyAdjudication($page) {
        return Property::whereMonth('created_at', now()->month)
                       ->whereYear('created_at', now()->year)
                       ->with(['owner', 'propertyAdmin.admin', 'availabilityStatus', 'location.city', 'location.country'])
                       ->orderBy('created_at', 'desc') 
                       ->simplePaginate(10, '*', 'page', $page?? 1);
    }

    

    function getSoldProperties($page) {
        return Property::where('availability_status_id', AvailabilityStatus::Sold)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->simplePaginate(10, '*', 'page', $page?? 1);
        }

    function propertyAdjudication($data){ 
        if($data['approve'] == 1)
            $this->updateProperty($data['property_id'], [
                'availability_status_id' => AvailabilityStatus::Active,
            ]);
        else if($data['approve'] == 0)
            $this->updateProperty($data['property_id'], [
                'availability_status_id' => AvailabilityStatus::Rejected,
            ]);

        $this->createPropertyAdmin([
            'property_id' => $data['property_id'],
            'admin_id' => $data['admin_id'],
            'approve' => $data['approve'],
            'reason' => $data['reason'] ?? null
         ]);
    }    
    
    protected function _basePropertyfiltering($query, $filters)
    {
        return $query->filterByLocation($filters['country_id'] ?? null, $filters['city_id'] ?? null)
                     ->filterByArea($filters['min_area'] ?? null, $filters['max_area'] ?? null)
                     ->filterByRooms($filters['bathrooms'] ?? null, $filters['balconies'] ?? null)
                     ->filterByAmenities($filters['amenity_ids'] ?? [])
                     ->filterPropertyType($filters);
    }
    
    public function _joinNeededTables(
        $query,
        $data,
        $sellTypeId,
        $id=null,
    ){
        $query->join('locations', 'properties.location_id', '=', 'locations.id')
              ->join('countries', 'locations.country_id', '=', 'countries.id')
              ->join('cities', 'locations.city_id', '=', 'cities.id');


        $this->_joinSellTypeTables($query, $sellTypeId);
        $this->_joinPropertyTypeTables(
            $query,
            $data['property_type_id'] ?? null,
            $data['residential_type_id'] ?? null,
            $id ?? null,
        );
        
        return $query;
    }


    protected function _joinPropertyTypeTables($query, $propertyTypeId, $residentialPropertyTypeId, $id){
        if($propertyTypeId == PropertyType::RESIDENTIAL){
            $query->where('properties.property_type_id', $propertyTypeId)
                  ->leftJoin('residential_properties', 'properties.id', 'residential_properties.property_id');
            
            if($id != null){
                $residentialPropertyTypeId = ResidentialProperty::where('property_id', $id)
                                                       ->first()->residential_property_type_id;
            }

            $this->_joinResidentialPropertyTypeTables($query, $residentialPropertyTypeId);
        }

        else if($propertyTypeId == PropertyType::COMMERCIAL){
            $query->where('properties.property_type_id', $propertyTypeId)
                  ->leftJoin('commercial_properties', 'properties.id', 'commercial_properties.property_id');
        }

        return $query;
    }

    protected function _joinResidentialPropertyTypeTables($query, $residentialPropertyTypeId = null){
        if ($residentialPropertyTypeId == ResidentialPropertyType::APARTMENT) {
            $query->where('residential_properties.residential_property_type_id', $residentialPropertyTypeId)
                  ->leftJoin('apartments', 'residential_properties.id', 'apartments.residential_property_id');
        }
        
        else if ($residentialPropertyTypeId == ResidentialPropertyType::VILLA) {
            $query->where('residential_properties.residential_property_type_id', $residentialPropertyTypeId)
                  ->leftJoin('villas', 'residential_properties.id', 'villas.residential_property_id');
        }

        return $query;
    }

    
    protected function _joinSellTypeTables($query, $sellTypeId){
        if ($sellTypeId == SellType::PURCHASE) {
            $query->where('properties.sell_type_id', SellType::PURCHASE)
                  ->leftJoin('purchases', 'properties.id', 'purchases.property_id');
            }
        
        else if ($sellTypeId == SellType::RENT) {
            $query->where('properties.sell_type_id', SellType::RENT)
                  ->leftJoin('rents', 'properties.id', 'rents.property_id');
        }
        
        else if ($sellTypeId == SellType::OFF_PLAN) {
            $query->where('properties.sell_type_id', SellType::OFF_PLAN)
                  ->leftJoin('off_plan_properties', 'properties.id', 'off_plan_properties.property_id');       
        }
        
        return $query;
    }

    public function getFilteredPropertiesByIds(array $propertyIds, ?\App\Models\UserPreference $preferences, int $page = 1, int $perPage = 10)
    {
        $query = Property::whereIn('id', $propertyIds);

        if (empty($propertyIds)) {
            return collect();
        }

        if ($preferences) {
            if ($preferences->min_price) {
                $query->where(function ($q) use ($preferences) {
                    $q->whereHas('purchase', fn($sub) => $sub->where('price', '>=', $preferences->min_price))
                      ->orWhereHas('rent', fn($sub) => $sub->where('price', '>=', $preferences->min_price))
                      ->orWhereHas('offPlan', fn($sub) => $sub->where('overall_payment', '>=', $preferences->min_price));
                });
            }
            if ($preferences->max_price) {
                $query->where(function ($q) use ($preferences) {
                    $q->whereHas('purchase', fn($sub) => $sub->where('price', '<=', $preferences->max_price))
                      ->orWhereHas('rent', fn($sub) => $sub->where('price', '<=', $preferences->max_price))
                      ->orWhereHas('offPlan', fn($sub) => $sub->where('overall_payment', '<=', $preferences->max_price));
                });
            }

            if ($preferences->min_area) { $query->where('area', '>=', $preferences->min_area); }
            if ($preferences->max_area) { $query->where('area', '<=', $preferences->max_area); }

            if ($preferences->min_bedrooms || $preferences->max_bedrooms) {
                $query->whereHas('residential', function ($q) use ($preferences) {
                    if ($preferences->min_bedrooms) { $q->where('bedrooms', '>=', $preferences->min_bedrooms); }
                    if ($preferences->max_bedrooms) { $q->where('bedrooms', '<=', $preferences->max_bedrooms); }
                });
            }

            if (!empty($preferences->preferred_locations)) {
                $query->whereHas('location', function ($q) use ($preferences) {
                    $q->whereIn('city_id', $preferences->preferred_locations);
                });
            }

            if (!empty($preferences->must_amenity)) {
                foreach ($preferences->must_amenity as $amenityId) {
                    $query->whereHas('amenities', fn($q) => $q->where('amenities.id', $amenityId));
                }
            }
        }
        
        $query->with([
                'coverImage', 'availabilityStatus', 'owner', 'propertyType',
                'location.country', 'location.city', 'residential.residentialPropertyType',
                'commercial.commercialPropertyType', 'rent', 'purchase', 'offPlan'
            ])
            ->orderByRaw("FIELD(id, " . implode(',', $propertyIds) . ")");

        return $query->simplePaginate($perPage, ['*'], 'page', $page);
    }

    function viewAmenities()
    {
        return Amenity::get();
    }

    function viewDirections()
    {
        return Direction::get();
    }

    function viewPropertyTypes()
    {
        return ModelsPropertyType::get();
    }

    function viewCommercialPropertyTypes()
    {
        return CommercialPropertyType::get();
    }

    function viewResidentialPropertyTypes()
    {
        return ModelsResidentialPropertyType::get();
    }

    function viewAvailabilityStatus()
    {
        return ModelsAvailabilityStatus::get();
    }

    function viewOwnershipTypes()
    {
        return OwnershipType::get();
    }

    function viewCountries()
    {
        return Country::with('cities')->get();
    }

    public function search($data)
    {
        $textQuery = $data['query'] ?? '*';
        $filters = $data['filters'] ?? [];
        $negations = $data['negations'] ?? [];
        $sorting = $data['sorting'] ?? [];
        $userId = $data['user_id'] ?? null;
        $perPage = $data['per_page'] ?? 10;
        
        unset($filters['query']);

        $searchCallback = function ($meilisearch, $query, $options) use ($filters, $negations, $sorting) {
        $filterParts = [];

        foreach ($filters as $key => $value) {
            if ($key === 'amenities' && is_array($value)) {
                foreach ($value as $amenity) {
                    $filterParts[] = 'amenities = "' . $amenity . '"';
                }
            } elseif (str_starts_with($key, 'min_')) {
                $filterParts[] = substr($key, 4) . ' >= ' . (int)$value;
            } elseif (str_starts_with($key, 'max_')) {
                $filterParts[] = substr($key, 4) . ' <= ' . (int)$value;
            } elseif ($key === 'is_furnished') {
                $filterParts[] = 'is_furnished = true';
            } elseif (!is_array($value)) {
                $filterParts[] = $key . ' = "' . $value . '"';
            }
        }

        foreach ($negations as $negatedItem) {
            $filterParts[] = 'amenities != "' . $negatedItem . '"';
        }

        if (!empty($filterParts)) {
            $options['filter'] = implode(' AND ', $filterParts);
        }

        if (!empty($sorting)) {
            $options['sort'] = [$sorting['attribute'] . ':' . $sorting['direction']];
        }

        return $meilisearch->search($query, $options);
    };

    $search = Property::search($textQuery, $searchCallback);

    $search->query(function ($builder) use ($userId) {
        $builder->with([
            'coverImage', 'availabilityStatus', 'owner', 'propertyType',
            'location.country', 'location.city', 'residential.residentialPropertyType',
            'commercial.commercialPropertyType', 'rent', 'purchase', 'offPlan'
        ]);
        if ($userId) {
            $builder->with(['favorites' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }]);
        }
    });

    return $search->paginate($perPage);
}
}


