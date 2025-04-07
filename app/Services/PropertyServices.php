<?php

namespace App\Services;

use App\Repositories\ImageRepository;
use App\Repositories\LocationRepository;
use Exception;
use App\Repositories\PropertyRepository;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make($data, [
            "owner_id" => "integer|required",

            # Location
            "country_name" => "string|required",
            "city_name" => "string|required",
            "altitude" => "",
            "longitude" => "",
            "additional_info" => "string",

            # Property
            "area" => "",
            "exposure" => "array",
            "bathrooms" => "integer",
            "balconies" => "integer",
            "ownership_type" => "string",
            "property_physical_status" => "string",
            "images" => "array",

            # Amenities
            "amenities" => "array",
        ]);
        
        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }

        $locationId = $this->_saveLocation([
            'country_name' => $data['country_name'],
            'city_name' => $data['city_name'],
            'altitude' => $data['altitude'] ?? null,
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
            'property_physical_status' => $data['property_physical_status'],
        ]);

        $this->_saveImages($property->id, $data['images']);

        $property->directions()->attach($data['exposure']);
        $property->amenities()->attach($data['amenities']);

    }

    function _saveImages($propertyId, $images) {
        $imagesPaths = $this->_storeImages($images, 'property', $propertyId);
        $this->image_repository->addImages($propertyId, $imagesPaths);
    }

    function _saveLocation($data) {
        $data['country_id'] = $this->location_repository->getCountry_byName($data['country_name'])->id;
        $data['city_id'] = $this->location_repository->getCity_byName($data['city_name'])->id;
        
        unset($data['country_name']);
        unset($data['city_name']);

        return $this->location_repository->create($data); 
    }

    function _saveProperty($data) {
        $data['availability_status'] = 'Pending';

        return $this->property_repository->create($data); 
    }
}
