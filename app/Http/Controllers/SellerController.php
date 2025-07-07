<?php

namespace App\Http\Controllers;

use App\Services\ServiceTransformer;

class SellerController extends Controller
{
    protected $service_transformer;
    
    function __construct(){
        $this->service_transformer = new ServiceTransformer();
    }
}

