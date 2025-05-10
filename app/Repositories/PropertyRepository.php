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

    public function getOffPlanProperties($filters)
    {
        $query = $this->_joinNeededTables(
            $filters['_property_type_id'],
            $filters['residential_property_type_id'] ?? null,
            $filters['_sell_type_id'],
            $filters['_physical_status_type_id'],
        );
        $this->_basePropertyfiltering($query, $filters);

        
        return $query->offPlanFilters([
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
                
    public function getReadyProperties($filters)
    {
        $query = $this->_joinNeededTables(
            $filters['_property_type_id'],
            $filters['residential_property_type_id'] ?? null,
            $filters['_sell_type_id'],
            $filters['_physical_status_type_id'],
        );
        $this->_basePropertyfiltering($query, $filters);

        if($filters['_sell_type_id'] == 1){
            return $this->_getPurchaseProperties($query, $filters);
        }
        else if($filters['_sell_type_id'] == 2){
            return $this->_getRentProperties($query, $filters);
        }
    }
    
    protected function _getPurchaseProperties($query, $filters)
    {
        return $query->purchaseFilters([
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

    protected function _getRentProperties($query, $filters)
    {
        return $query->rentFilters([
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

    protected function _basePropertyfiltering($query, $filters)
    {
        return $query->filterByLocation($filters['country_id'] ?? null, $filters['city_id'] ?? null)
                     ->filterByArea($filters['min_area'] ?? null, $filters['max_area'] ?? null)
                     ->filterByRooms($filters['bathrooms'] ?? null, $filters['balconies'] ?? null)
                     ->filterByAmenities($filters['amenity_ids'] ?? [])
                     ->filterPropertyType($filters);
    }
    
    protected function _joinNeededTables(
        $propertyTypeId,
        $residentialPropertyTypeId,
        $sellTypeId,
        $physicalStatusTypeId
    ){
        $query = Property::query()
                ->join('locations', 'properties.location_id', '=', 'locations.id')
                ->join('countries', 'locations.country_id', '=', 'countries.id')
                ->join('cities', 'locations.city_id', '=', 'cities.id');

        $this->_joinPropertyTypeTables($query, $propertyTypeId, $residentialPropertyTypeId);
        $this->_joinPhysicalStatusTypeTables($query, $physicalStatusTypeId, $sellTypeId);

        return $query;
    }


    protected function _joinPropertyTypeTables($query, $propertyTypeId, $residentialPropertyTypeId){
        if($propertyTypeId == 1){
            $query->where('properties.property_type_id', $propertyTypeId)
                  ->leftJoin('residential_properties', 'properties.id', 'residential_properties.property_id');

            $this->_joinResidentialPropertyTypeTables($query, $residentialPropertyTypeId);
        }

        else if($propertyTypeId == 2){
            $query->where('properties.property_type_id', $propertyTypeId)
                  ->leftJoin('commercial_properties', 'properties.id', 'commercial_properties.property_id');
        }

        return $query;
    }

    protected function _joinResidentialPropertyTypeTables($query, $residentialPropertyTypeId = null){
        if ($residentialPropertyTypeId == 1) {
            $query->where('residential_properties.residential_property_type_id', $residentialPropertyTypeId)
                  ->leftJoin('apartments', 'residential_properties.id', 'apartments.residential_property_id');
        }
        
        else if ($residentialPropertyTypeId == 2) {
            $query->where('residential_properties.residential_property_type_id', $residentialPropertyTypeId)
                  ->leftJoin('villas', 'residential_properties.id', 'villas.residential_property_id');
        }

        return $query;
    }

    
    protected function _joinPhysicalStatusTypeTables($query, $physicalStatusTypeId, $sellTypeId){
        if ($physicalStatusTypeId == 1) {
            $query->where('properties.physical_status_type_id', $physicalStatusTypeId)
                  ->leftJoin('ready_to_move_in_properties', 'properties.id', 'ready_to_move_in_properties.property_id');
            
            
            $this->_joinSellTypeTables($query, $sellTypeId);
        }
        
        else if ($physicalStatusTypeId == 2) {
            $query->where('properties.physical_status_type_id', $physicalStatusTypeId)
                  ->leftJoin('off_plan_properties', 'properties.id', 'off_plan_properties.property_id');       
        }
        
        return $query;
    }

    protected function _joinSellTypeTables($query, $sellTypeId){
        if ($sellTypeId == 1) {
            $query->where('ready_to_move_in_properties.sell_type_id', $sellTypeId)
                  ->leftJoin('purchases', 'ready_to_move_in_properties.id', 'purchases.ready_property_id');
        }
        
        else if ($sellTypeId == 2) {
            $query->where('ready_to_move_in_properties.sell_type_id', $sellTypeId)
                  ->leftJoin('rents', 'ready_to_move_in_properties.id', 'rents.ready_property_id');
        }

        return $query;
    }
}