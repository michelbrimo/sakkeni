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

    function viewProperties(FilterPropertiesRequest $request, $buy_type, $property_type)
    {
        $additionalData = [
          'page' => $request->input('page', 1),
          '_buy_type' => $buy_type,
          '_property_type' => $property_type
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Properties fetched successfully');
    }

}
