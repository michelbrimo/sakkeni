<?php

namespace App\Http\Controllers;

use App\Enums\SellType;
use App\Http\Requests\AddingPropertyDataRequest;
use App\Http\Requests\FilterPropertiesRequest;
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

    function viewProperties(Request $request, $sell_type_id)
    {
        $additionalData = [
          'page' => $request->query('page', 1),
          '_sell_type_id' => $sell_type_id
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Properties fetched successfully');
    }

    function filterProperties(FilterPropertiesRequest $request, $sell_type_id)
    {
        $additionalData = [
          'page' => $request->input('page', 1),
          '_sell_type_id' => $sell_type_id
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Properties fetched successfully');
    }

    function viewPropertyDetails($property_id)
    {
        $additionalData = ['property_id' => $property_id];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Property Details fetched successfully');
    }

}
