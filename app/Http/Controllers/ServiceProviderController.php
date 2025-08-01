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

    function viewServiceCategories() {
        return $this->executeService($this->service_transformer, new Request(), [], "Service's Categories fetched successfully");
    }
    
    function viewServiceProviders(Request $request, $service_id) {
        $additionalData = ['service_id' => $service_id, 'page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, "Service Providers fetched successfully");
    }
    
    function viewServiceProviderDetails($service_provider_id) {
        $additionalData = ['service_provider_id' => $service_provider_id];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, "Service Provider details fetched successfully");
    }

    function viewServiceProviderServiceGallery($service_provider_service_id) {
        $additionalData = ['service_provider_service_id' => $service_provider_service_id];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, "Service Provider's Service Gallery fetched successfully");
    }
    
}
