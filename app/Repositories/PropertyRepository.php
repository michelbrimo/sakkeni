<?php

namespace App\Repositories;

use App\Enums\PhysicalStatusType;
use App\Enums\PropertyType;
use App\Enums\ResidentialPropertyType;
use App\Enums\SellType;
use App\Models\Apartment;
use App\Models\CommercialProperty;
use App\Models\OffPlanProperty;
use App\Models\Property;
use App\Models\Purchase;
use App\Models\ReadyToMoveInProperty;
use App\Models\Rent;
use App\Models\ResidentialProperty;
use App\Models\Villa;

class PropertyRepository{
    public function create($data) {
        return Property::create($data);
    }

    public function createOffPlanProperty($data) {
        return OffPlanProperty::create($data);
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

    public function getBasePropertyDetails($propertyId){
        return Property::where('id', $propertyId)->first();
    }
    
    public function getPurchaseProperties($data)
    {
        $query = Property::query();

        return $query->join('locations', 'properties.location_id', '=', 'locations.id')
              ->join('countries', 'locations.country_id', '=', 'countries.id')
              ->join('cities', 'locations.city_id', '=', 'cities.id')
              ->join('purchases', 'properties.id', '=', 'purchases.property_id')

              ->with('images')
              ->simplePaginate(10, [
                'properties.id',
                'price',
                'countries.name as country',
                'cities.name as city',
                'locations.additional_info',
              ], 'page', $data['page'] ?? 1);
    }

    public function filterPurchaseProperties($filters)
    {
        $query = $this->_joinNeededTables(
            $filters,
            SellType::PURCHASE,
        );
        $this->_basePropertyfiltering($query, $filters);

        return $query->purchaseFilters([
            'min_price' => $filters['min_price'] ?? null,
            'max_price' => $filters['max_price'] ?? null,
            'is_furnished' => $filters['is_furnished'] ?? null
            ])

            ->simplePaginate(10, [
            'properties.id',
            'price',
            'countries.name as country',
            'cities.name as city',
            'locations.additional_info',
            ], 'page', $filters['page'] ?? 1);
    }
    

    public function getRentProperties()
    {
        $query = Property::query();
                
        return $query->join('locations', 'properties.location_id', '=', 'locations.id')
              ->join('countries', 'locations.country_id', '=', 'countries.id')
              ->join('cities', 'locations.city_id', '=', 'cities.id')
              ->join('rents', 'properties.id', '=', 'rents.property_id')

              ->with('images')
              ->simplePaginate(10, [
                'properties.id',
                'price',
                'lease_period',
                'countries.name as country',
                'cities.name as city',
                'locations.additional_info',
              ], 'page', $filters['page'] ?? 1);
    }

    public function filterRentProperties($filters)
    {
        $query = $this->_joinNeededTables(
            $filters,
            SellType::RENT,
        );
        $this->_basePropertyfiltering($query, $filters);

        return $query->rentFilters([
            'min_price' => $filters['min_price'] ?? null,
            'max_price' => $filters['max_price'] ?? null,
            'is_furnished' => $filters['is_furnished'] ?? null,
            'lease_period' => $filters['lease_period'] ?? null
            ])

            ->simplePaginate(10, [
            'properties.id',
            'price',
            'lease_period',
            'countries.name as country',
            'cities.name as city',
            'locations.additional_info',
            ], 'page', $filters['page'] ?? 1);
    }

    public function getOffPlanProperties()
    {
        $query = Property::query();
                
        return $query->join('locations', 'properties.location_id', '=', 'locations.id')
              ->join('countries', 'locations.country_id', '=', 'countries.id')
              ->join('cities', 'locations.city_id', '=', 'cities.id')
              ->join('off_plan_properties', 'properties.id', '=', 'off_plan_properties.property_id')

              ->with('images')
              ->simplePaginate(10, [
                'properties.id',
                'overall_payment as price',
                'countries.name as country',
                'cities.name as city',
                'locations.additional_info',
              ], 'page', $data['page'] ?? 1);
    }

    public function filterOffPlanProperties($filters)
    {
        $query = $this->_joinNeededTables(
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
    
            ->simplePaginate(10, [
                'properties.id',
                'overall_payment as price',
                'countries.name as country',
                'cities.name as city',
                'first_pay',
                'locations.additional_info',
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
        
                     ->simplePaginate(10, [
                        'properties.id',
                        'rents.price',
                        'countries.name as country',
                        'cities.name as city',
                        'locations.additional_info',
                        'image_path'
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
    
    public function _joinNeededTables(
        $data,
        $sellTypeId,        
    ){
        $query = Property::query();
        
        $query->join('locations', 'properties.location_id', '=', 'locations.id')
              ->join('countries', 'locations.country_id', '=', 'countries.id')
              ->join('cities', 'locations.city_id', '=', 'cities.id')
              ->with('images');

        
        $this->_joinSellTypeTables($query, $sellTypeId);
        $this->_joinPropertyTypeTables(
            $query,
            $data['property_type_id'] ?? null,
            $data['residential_type_id'] ?? null,
            $data["commercial_type_id"] ?? null
        );
        
        return $query;
    }


    protected function _joinPropertyTypeTables($query, $propertyTypeId, $residentialPropertyTypeId, $commercialPropertyTypeId){
        if($propertyTypeId == PropertyType::RESIDENTIAL){
            $query->where('properties.property_type_id', $propertyTypeId)
                  ->leftJoin('residential_properties', 'properties.id', 'residential_properties.property_id');

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
}