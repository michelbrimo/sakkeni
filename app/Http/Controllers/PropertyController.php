<?php

namespace App\Http\Controllers;

use App\Services\ServiceTransformer;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    protected $service_transformer;
    
    function __construct(){
        $this->service_transformer = new ServiceTransformer();
    }

    function addProperty(Request $request)
    {
        $additionalData = ['owner_id' => auth()->user()->id];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Property Added successfully');
    }

}
