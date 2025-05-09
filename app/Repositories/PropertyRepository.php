<?php

namespace App\Repositories;

use App\Models\Apartment;
use App\Models\CommercialProperty;
use App\Models\OffPlanProperty;
use App\Models\Property;
use App\Models\Purchase;
use App\Models\ReadyToMoveInProperty;
use App\Models\Rent;
use App\Models\ResidentialProperty;
use App\Models\Villa;
use Illuminate\Support\Facades\DB;

class PropertyRepository{
    public function create($data) {
        return Property::create($data);
    }

    public function createOffPlanProperty($data) {
        return OffPlanProperty::create($data);
    }

    public function createReadyToMoveInProperty($data) {
        return ReadyToMoveInProperty::create($data);
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

    public function createApartment($data) {
        return Apartment::create($data);
    }


    public function getPurchaseProperties($filters)
    {
        return $this->_basePropertyfiltering($filters)
                    
                    ->where('properties.property_physical_status', 'Ready To Move In')
                    ->leftJoin('ready_to_move_in_properties', 'properties.id', 'ready_to_move_in_properties.property_id')

                    ->where('ready_to_move_in_properties.sell_type', 'Purchase')
                    ->leftJoin('purchases', 'ready_to_move_in_properties.id', 'purchases.ready_property_id')

                    ->purchaseFilters([
                        'min_price' => $filters['min_price'] ?? null,
                        'max_price' => $filters['max_price'] ?? null,
                        'is_furnished' => $filters['is_furnished'] ?? null
                    ])
        
                    ->paginate(10, [
                        'properties.id',
                        'price',
                        'countries.name as country',
                        'cities.name as city',
                        'locations.additional_info'
                    ], 'page', $filters['page'] ?? 1);
    }

    public function getRentProperties($filters)
    {
        return $this->_basePropertyfiltering($filters)

                    ->where('properties.property_physical_status', 'Ready To Move In')
                    ->leftJoin('ready_to_move_in_properties', 'properties.id', 'ready_to_move_in_properties.property_id')

                    ->where('ready_to_move_in_properties.sell_type', 'Rent')
                    ->leftJoin('rents', 'ready_to_move_in_properties.id', 'rents.ready_property_id')
                      
                    ->rentFilters([
                        'min_price' => $filters['min_price'] ?? null,
                        'max_price' => $filters['max_price'] ?? null,
                        'is_furnished' => $filters['is_furnished'] ?? null,
                        'lease_period' => $filters['lease_period'] ?? null
                    ])
        
                    ->paginate(10, [
                        'properties.id',
                        'rents.price',
                        'countries.name as country',
                        'cities.name as city',
                        'locations.additional_info'
                    ], 'page', $filters['page'] ?? 1);
    }


    public function getOffPlanProperties($filters)
    {
        return $this->_basePropertyfiltering($filters)

                ->where('properties.property_physical_status', 'Off Plan')
                ->leftJoin('off_plan_properties', 'properties.id', 'off_plan_properties.property_id')        
        
                ->offPlanFilters([
                    'min_price' => $filters['min_price'] ?? null,
                    'max_price' => $filters['max_price'] ?? null,
                    'min_first_pay' => $filters['min_first_pay'] ?? null,
                    'max_first_pay' => $filters['max_first_pay'] ?? null,
                    'delivery_date' => $filters['delivery_date'] ?? null
                ])
        
                ->paginate(10, [
                    'properties.id',
                    'countries.name as country',
                    'cities.name as city',
                    'overall_payment',
                    'first_pay',
                    'locations.additional_info'
                ], 'page', $filters['page'] ?? 1);
    }

    protected function _basePropertyfiltering($filters)
    {
        return Property::query()
            ->join('locations', 'properties.location_id', '=', 'locations.id')
            ->join('countries', 'locations.country_id', '=', 'countries.id')
            ->join('cities', 'locations.city_id', '=', 'cities.id')

            ->filterByLocation($filters['country_id'] ?? null, $filters['city_id'] ?? null)
            ->filterByArea($filters['min_area'] ?? null, $filters['max_area'] ?? null)
            ->filterByRooms($filters['bathrooms'] ?? null, $filters['balconies'] ?? null)
            ->filterByAmenities($filters['amenity_ids'] ?? [])
            
            ->filterPropertyType($filters);
    }

}