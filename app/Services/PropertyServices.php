<?php

namespace App\Services;

use App\Enums\AvailabilityStatus;
use App\Enums\PhysicalStatusType;
use App\Enums\PropertyType;
use App\Enums\ResidentialPropertyType;
use App\Enums\SellType;
use App\Models\ResidentialProperty;
use App\Repositories\ImageRepository;
use App\Repositories\LocationRepository;
use Exception;
use App\Repositories\PropertyRepository;
use Illuminate\Support\Facades\Storage;

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
            'country_id' => $data['country_id'],
            'city_id' => $data['city_id'],
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
            'ownership_type_id' => $data['ownership_type_id'],
            'property_type_id' => $data['property_type_id'],
            'sell_type_id' => $data['sell_type_id'],
        ]);
 
        $this->_saveImages($property->id, $data['images']);
        $property->directions()->attach($data['exposure']);
        $property->amenities()->attach($data['amenities']);

        
        if ($data['sell_type_id'] == SellType::PURCHASE) {
            $this->property_repository->createPurchase([
                'property_id' => $property->id,
                'price' => $data['price'],
                'is_furnished' => $data['is_furnished'],
            ]);        
        }
        else if($data['sell_type_id'] == SellType::RENT){
            $this->property_repository->createRent([
                'property_id' => $property->id,
                'price' => $data['price'],
                'lease_period' => $data['lease_period'],
                'payment_plan' => $data['payment_plan'],
                'is_furnished' => $data['is_furnished'],
            ]);
        }
        else if($data['sell_type_id'] == SellType::OFF_PLAN){
            $this->property_repository->createOffPlanProperty([
                'property_id' => $property->id,
                'delivery_date' => $data['delivery_date'],
                'first_pay' => $data['first_pay'],
                'pay_plan' => $data['pay_plan'],
                'overall_payment' => $data['overall_payment'],
            ]);
        }

        if ($data['property_type_id'] == PropertyType::RESIDENTIAL){
            $this->_saveResidentialProperty($data, $property) ;
        }
        else if ($data['property_type_id'] == PropertyType::COMMERCIAL){
            $this->_saveCommercialProperty([
                "property_id" => $property->id,
                "building_number" => $data["building_number"],
                "apartment_number" => $data["apartment_number"],
                "floor" => $data["floor"],
                "commercial_property_type_id" => $data["commercial_property_type_id"],
            ]);
        }
    }

    public function updateProperty($data){
        $propertyId = $data['property']->id;

        $updateLocationData = collect($data)
                    ->only(['country_id', 'city_id', 'latitude', 'longitude', 'additional_info'])
                    ->filter(fn($value) => !is_null($value))
                    ->toArray();

        $this->property_repository->updatePropertyLocation($data['property']->location_id, $updateLocationData);


        $updatePropertyData = collect($data)
                    ->only(['area', 'bathrooms', 'balconies', 'ownership_type_id'])
                    ->filter(fn($value) => !is_null($value))
                    ->toArray();
        $updatePropertyData['availability_status_id'] = AvailabilityStatus::Pending;

        $this->property_repository->updateProperty($propertyId, $updatePropertyData);
 
        if(isset($data['exposure']))
            $data['property']->directions()->sync($data['exposure']);

        if(isset($data['amenities']))
            $data['property']->amenities()->sync($data['amenities']);

        if(isset($data['images']))
            $this->updateImages($propertyId, $data['images']);
        
        
        if ($data['property']->sell_type_id == SellType::PURCHASE) {
            $updatePurchaseData = collect($data)
                ->only(['price', 'is_furnished'])
                ->filter(fn($value) => !is_null($value))
                ->toArray();

            $this->property_repository->updatePurchase($propertyId, $updatePurchaseData);        
        }
        else if($data['property']->sell_type_id == SellType::RENT){
            $updateRentData = collect($data)
                ->only(['price', 'lease_period', 'payment_plan', 'is_furnished'])
                ->filter(fn($value) => !is_null($value))
                ->toArray();

            $this->property_repository->updateRent($propertyId, $updateRentData);
        }
        else if($data['property']->sell_type_id == SellType::OFF_PLAN){
            $updateOffPlanData = collect($data)
                ->only(['delivery_date', 'first_pay', 'pay_plan', 'overall_payment'])
                ->filter(fn($value) => !is_null($value))
                ->toArray();

            $this->property_repository->updateOffPlan($propertyId, $updateOffPlanData);
        }
        
        
        if ($data['property']->property_type_id == PropertyType::RESIDENTIAL){
            $this->_updateResidentialProperty($propertyId, $data) ;
        }
        else if ($data['property']->property_type_id == PropertyType::COMMERCIAL){
            $this->_updateCommercialProperty($propertyId, $data) ;
        }
    }
                
    public function viewProperties($data){
        return $this->property_repository->getProperties($data);

        throw new \Exception('Unkown Property Type', 422);
    }

    public function filterProperties($data){
        if($data['sell_type_id'] == SellType::PURCHASE)
            return $this->property_repository->filterPurchaseProperties($data);
        else if($data['sell_type_id'] == SellType::RENT)
            return $this->property_repository->filterRentProperties($data);
        else if($data['sell_type_id'] == SellType::OFF_PLAN)
            return $this->property_repository->filterOffPlanProperties($data);

        throw new \Exception('Unkown Property Type', 422);
    }

    function viewPropertyDetails($data) {
        return $this->property_repository->viewPropertyDetails($data);
    }

    function deleteProperty($data) {
        return $this->property_repository->deleteProperty($data);
    }
    
    function viewPendingProperties($data) {
        return $this->property_repository->viewPendingProperties($data);
    }
    
    function propertyAdjudication($data) {
        return $this->property_repository->propertyAdjudication($data);
    }
    
    function addPropertyToFavorite($data) {
        return $this->property_repository->createPropertyFavorite($data);
    }
    
    function removePropertyFromFavorite($data) {
        return $this->property_repository->deletePropertyFavorite($data);
    }
    
    function viewFavoriteProperties($data) {
        return $this->property_repository->getFavoriteProperties($data);
    }



    function viewAmenities()
    {
        return $this->property_repository->viewAmenities();
    }

    function viewDirections()
    {
        return $this->property_repository->viewDirections();
    }

    function viewPropertyTypes()
    {
        return $this->property_repository->viewPropertyTypes();
    }

    function viewCommercialPropertyTypes()
    {
        return $this->property_repository->viewCommercialPropertyTypes();
    }

    function viewResidentialPropertyTypes()
    {
        return $this->property_repository->viewResidentialPropertyTypes();
    }

    function viewCountries()
    {
        return $this->property_repository->viewCountries();
    }

    function viewAvailabilityStatus()
    {
        return $this->property_repository->viewAvailabilityStatus();
    }

    function viewOwnershipTypes()
    {
        return $this->property_repository->viewOwnershipTypes();
    }


    protected function _saveImages($propertyId, $images) {
        $imagesPaths = $this->_storeImages($images, 'property', $propertyId);
        $this->image_repository->addPropertyImages($propertyId, $imagesPaths);
    }

  public function updateImages($propertyId, array $newImages)
    {
        $oldImages = $this->image_repository->getImagesByPropertyId($propertyId);

        foreach ($oldImages as $image) {
            $filePath = str_replace('storage/', '', $image->image_path);
            Storage::disk('public')->delete($filePath);
            $this->image_repository->deleteImage($image->id);
        }

        $this->_saveImages($propertyId, $newImages);
    }

    
    protected function _saveCommercialProperty($data) {
        $this->property_repository->createCommercialProperty($data);
    }

    protected function _updateCommercialProperty($propertyId, $data) {
        $updateResidentialProperty = collect($data)
            ->only(['building_number', 'apartment_number', 'floor'])
            ->filter(fn($value) => !is_null($value))
            ->toArray();

        $this->property_repository->updateCommercialProperty($propertyId, $updateResidentialProperty);
    }

    protected function _saveResidentialProperty($data, $property) {
        $residentialProperty = $this->property_repository->createResidentialProperty([
            "property_id" => $property->id,
            "bedrooms" => $data["bedrooms"],
            "residential_property_type_id" => $data["residential_property_type_id"],
        ]);

        if ($data['residential_property_type_id'] == ResidentialPropertyType::APARTMENT) {
            $this->property_repository->createApartment([
                "residential_property_id" => $residentialProperty->id,
                "floor" => $data['floor'],
                "building_number" => $data['building_number'],
                "apartment_number" => $data['apartment_number'],
            ]);
        }
        else if($data['residential_property_type_id'] == ResidentialPropertyType::VILLA){
            $this->property_repository->createVilla([
                "residential_property_id" => $residentialProperty->id,
                "floors" => $data['floors']
            ]);
        }
    }

    protected function _updateResidentialProperty($propertyId, $data) {
        $updateResidentialProperty = collect($data)
                ->only(['bedrooms'])
                ->filter(fn($value) => !is_null($value))
                ->toArray();

        $this->property_repository->updateResidentialProperty($propertyId, $updateResidentialProperty);

        $residentialProperty = ResidentialProperty::where('property_id', $propertyId)->first();
        
        if ($residentialProperty->residential_property_type_id == ResidentialPropertyType::APARTMENT) {
            $updateApartmentData = collect($data)
                ->only(['floor', 'building_number', 'apartment_number'])
                ->filter(fn($value) => !is_null($value))
                ->toArray();

            $this->property_repository->updateApartment($residentialProperty->id, $updateApartmentData);
        }
        else if($residentialProperty->residential_property_type_id == ResidentialPropertyType::VILLA){
            $updateVillaData = collect($data)
                ->only(['floors'])
                ->filter(fn($value) => !is_null($value))
                ->toArray();

            $this->property_repository->updateVilla($residentialProperty->id, $updateVillaData);
        }
    }
    
    protected function _saveLocation($data) {
        return $this->location_repository->create($data); 
    }

    protected function _saveProperty($data) {
        $data['availability_status_id'] = AvailabilityStatus::Pending;

        return $this->property_repository->create($data); 
    }
}




