<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddingPropertyDataRequest;
use App\Http\Requests\FilterPropertiesRequest;
use App\Services\ServiceTransformer;
use Error;
use Exception;
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

    function viewProperties(FilterPropertiesRequest $request, $physical_status_type, $property_type, $buy_type=1)
    {
        $additionalData = [
          'page' => $request->input('page', 1),
          '_physical_status_type_id' => $physical_status_type,
          '_property_type_id' => $property_type,
          '_sell_type_id' => $buy_type
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Properties fetched successfully');
    }

}
