<?php

namespace App\Http\Controllers;

use App\Services\ServiceTransformer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $service_transformer;
    
    function __construct(){
        $this->service_transformer = new ServiceTransformer();
    }

    function viewTotalUsers() {
        return $this->executeService($this->service_transformer, new Request(), [], 'Total users fetched successfully');
    }

    function viewTotalProperties() {
        return $this->executeService($this->service_transformer, new Request(), [], 'Total properties fetched successfully');
    }
}
