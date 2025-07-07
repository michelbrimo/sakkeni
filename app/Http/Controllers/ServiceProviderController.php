<?php

namespace App\Http\Controllers;

use App\Services\ServiceTransformer;
use Illuminate\Http\Request;

class ServiceProviderController extends Controller
{
    protected $service_transformer;
    
    function __construct(){
        $this->service_transformer = new ServiceTransformer();
    }

    
}
