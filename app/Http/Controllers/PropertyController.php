<?php

namespace App\Http\Controllers;

use App\Enums\SellType;
use App\Http\Requests\AddingPropertyDataRequest;
use App\Http\Requests\FilterPropertiesRequest;
use App\Http\Requests\UpdatingPropertyDataRequest;
use App\Models\Property;
use App\Services\ServiceTransformer;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    protected $service_transformer;
    
    function __construct(){
        $this->service_transformer = new ServiceTransformer();
    }

    function addProperty(AddingPropertyDataRequest $request)
    {
        $additionalData = ['owner_id' => auth()->user()->id];   
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Property Added successfully');
    }

    function updateProperty(UpdatingPropertyDataRequest $request, $property)
    {
        $additionalData = ['property' => $property];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Property Updated successfully');
    }

    function viewProperties(Request $request, $sell_type_id)
    {
        $additionalData = [
          'page' => $request->query('page', 1),
          'sell_type_id' => $sell_type_id
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Properties fetched successfully');
    }

    function filterProperties(FilterPropertiesRequest $request, $sell_type_id)
    {
        $additionalData = [
          'page' => $request->input('page', 1),
          'sell_type_id' => $sell_type_id
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Properties filtered successfully');
    }

    
    function viewMyProperties(FilterPropertiesRequest $request, $sell_type_id)
    {
        $additionalData = [
          'page' => $request->query('page', 1),
          'sell_type_id' => $sell_type_id,
          'owner_id' => auth()->user()->id
        ];

        return $this->executeService($this->service_transformer, $request, $additionalData, 'Your properties fetched successfully');
    }
    
    function filterMyProperties(FilterPropertiesRequest $request, $sell_type)
    {
        $additionalData = [
          'page' => $request->input('page', 1),
          'sell_type_id' => $sell_type,
          'owner_id' => auth()->user()->id
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Your properties fetched successfully');
    }

    function viewPropertyDetails($property)
    {
        $additionalData = $property->toArray();
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Property Details fetched successfully');
    }

    function deleteProperty($property)
    {
        $additionalData = $property->toArray();
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Property Deleted successfully');
    }
    
    function viewPendingProperties(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];

        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Pending Properties fetched successfully');
    }

    function propertyAdjudication(Request $request)
    {
        $additionalData = ['admin_id' => auth('admin')->user()->id];

        return $this->executeService($this->service_transformer,  $request, $additionalData, 'Property adjudicated Successfully');
    }
    
    function addPropertyToFavorite($property)
    {
        $additionalData = ['user_id' => auth()->user()->id, 'property_id' => $property->id];

        return $this->executeService($this->service_transformer,  new Request(), $additionalData, 'Property added to favorite Successfully');
    }
    
    function removePropertyFromFavorite($property)
    {
        $additionalData = ['user_id' => auth()->user()->id, 'property_id' => $property->id];

        return $this->executeService($this->service_transformer,  new Request(), $additionalData, 'Property removed from favorite Successfully');
    }
    
    function viewFavoriteProperties(Request $request, $sell_type_id)
    {
        $additionalData = [
            'user_id' => auth()->user()->id,
            'page' => $request->query('page', 1),
            'sell_type_id' => $sell_type_id,
        ];

        return $this->executeService($this->service_transformer,  new Request(), $additionalData, 'Favorite Properties fetched Successfully');
    }




    
    function viewAmenities()
    {
        return $this->executeService($this->service_transformer, new Request(), [], 'Amenities fetched successfully');
    }

    function viewDirections()
    {
        return $this->executeService($this->service_transformer, new Request(), [], 'Directions fetched successfully');
    }

    function viewPropertyTypes()
    {
        return $this->executeService($this->service_transformer, new Request(), [], 'Property Types fetched successfully');
    }

    function viewCommercialPropertyTypes()
    {
        return $this->executeService($this->service_transformer, new Request(), [], 'Commercial Property Types fetched successfully');
    }

    function viewResidentialPropertyTypes()
    {
        return $this->executeService($this->service_transformer, new Request(), [], 'Residential Property Types fetched successfully');
    }

    function viewCountries()
    {
        return $this->executeService($this->service_transformer, new Request(), [], 'Countries fetched successfully');
    }

    function viewOwnershipTypes()
    {
        return $this->executeService($this->service_transformer, new Request(), [], 'Ownership Types fetched successfully');
    }

    function viewAvailabilityStatus()
    {
        return $this->executeService($this->service_transformer, new Request(), [], 'Availability Status fetched successfully');
    }

   



}
