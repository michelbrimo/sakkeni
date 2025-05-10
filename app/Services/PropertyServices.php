<?php

namespace App\Services;

use App\Repositories\ImageRepository;
use App\Repositories\LocationRepository;
use Exception;
use App\Repositories\PropertyRepository;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Event\Code\Test;

class PropertyServices extends ImageServices
{
    protected $property_repository; 
    protected $image_repository; 
    protected $location_repository; 

    public function __construct() {
        $this->property_repository = new PropertyRepository();
        $this->location_repository = new LocationRepository();
        $this->image_repository = new ImageRepository();
    }

    public function addProperty($data){
        $locationId = $this->_saveLocation([
            'country_name' => $data['country_name'],
            'city_name' => $data['city_name'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'additional_info' => $data['additional_info'] ?? null,
        ])->id;

        $property = $this->_saveProperty([
            'location_id' => $locationId,
            'owner_id' => $data['owner_id'],
            'area' => $data['area'],
            'bathrooms' => $data['bathrooms'],
            'balconies' => $data['balconies'],
            'ownership_type' => $data['ownership_type'],
            'physical_status_type_id' => $data['physical_status_type_id'],
            'property_type_id' => $data['property_type_id'],
        ]);
 
        $this->_saveImages($property->id, $data['images']);
        $property->directions()->attach($data['exposure']);
        $property->amenities()->attach($data['amenities']);

        
        if ($data['physical_status_type_id'] == 1) {
            $this->_saveReadyToMoveIn($data, $property);
        }
        else if($data['physical_status_type_id'] == 2){
            $this->property_repository->createOffPlanProperty([
                'property_id' => $property->id,
                'delivery_date' => $data['delivery_date'],
                'first_pay' => $data['first_pay'],
                'pay_plan' => $data['pay_plan'],
                'overall_payment' => $data['overall_payment'],
            ]);
        }

        if ($data['property_type_id'] == 1){
            $this->_saveResidentialProperty($data, $property) ;
        }
        else if ($data['property_type_id'] == 2){
            $this->_saveCommercialProperty([
                "property_id" => $property->id,
                "building_number" => $data["building_number"],
                "apartment_number" => $data["apartment_number"],
                "floor" => $data["floor"],
                "commercial_property_type_id" => $data["commercial_property_type_id"],
            ]);
        }
    }

    public function viewProperties($data){
        if($data['_buy_type'] == 'purchase')
            return $this->property_repository->getPurchaseProperties($data);
        else if($data['_buy_type'] == 'rent')
            return $this->property_repository->getRentProperties($data);
        else if($data['_buy_type'] == 'off-plan')
            return $this->property_repository->getOffPlanProperties($data);
    
        throw new \Exception('Unkown Property Type', 422);
    }



    # -------------------------------------------------------------

    protected function _saveImages($propertyId, $images) {
        $imagesPaths = $this->_storeImages($images, 'property', $propertyId);
        $this->image_repository->addImages($propertyId, $imagesPaths);
    }
    

    protected function _saveReadyToMoveIn($data, $property) {
        $readyToMoveInProperty = $this->property_repository->createReadyToMoveInProperty([
            "property_id" => $property->id,
            "is_furnished" => $data["is_furnished"],
            "sell_type_id" => $data["sell_type_id"],
        ]);

        if ($data['sell_type_id'] == 1) {
            $this->property_repository->createPurchase([
                'price' => $data['price'],
                'ready_property_id' => $readyToMoveInProperty->id,
            ]);
        }
        else if($data['sell_type_id'] == 2){
            $this->property_repository->createRent([
                'ready_property_id' => $readyToMoveInProperty->id,
                'price' => $data['price'],
                'lease_period' => $data['lease_period'],
                'payment_plan' => $data['payment_plan'],
            ]);
        }
    }

    protected function _saveCommercialProperty($data) {
        $this->property_repository->createCommercialProperty($data);
    }
    
    protected function _saveResidentialProperty($data, $property) {
        $residentialProperty = $this->property_repository->createResidentialProperty([
            "property_id" => $property->id,
            "bedrooms" => $data["bedrooms"],
            "residential_property_type_id" => $data["residential_property_type_id"],
        ]);

        if ($data['residential_property_type_id'] == 1) {
            $this->property_repository->createApartment([
                "residential_property_id" => $residentialProperty->id,
                "floor" => $data['floor'],
                "building_number" => $data['building_number'],
                "apartment_number" => $data['apartment_number'],
            ]);
        }
        else if($data['residential_property_type_id'] == 2){
            $this->property_repository->createVilla([
                "residential_property_id" => $residentialProperty->id,
                "floors" => $data['floors']
            ]);
        }
    }
    
    protected function _saveLocation($data) {
        $data['country_id'] = $this->location_repository->getCountry_byName($data['country_name'])->id;
        $data['city_id'] = $this->location_repository->getCity_byName($data['city_name'])->id;
        
        unset($data['country_name']);
        unset($data['city_name']);

        return $this->location_repository->create($data); 
    }

    protected function _saveProperty($data) {
        $data['availability_status'] = 'Pending';

        return $this->property_repository->create($data); 
    }
}




