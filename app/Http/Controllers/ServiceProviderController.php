<?php

namespace App\Http\Controllers;

use App\Services\ServiceTransformer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    function addService(Request $request) {
        $additionalData = ['user_id' => auth()->user()->id];

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|integer',
            'service_description' => 'string',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 

        return $this->executeService($this->service_transformer, $request, $additionalData, "Service added successfully");
    }

    function removeService($service_provider_service_id) {
        $additionalData = ['service_provider_service_id' => $service_provider_service_id];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, "Service removed successfully");
    }

    function editService(Request $request) {
        $validator = Validator::make($request->all(), [
            'service_provider_service_id' => 'required|integer',
            'service_gallery' => 'array',
            'description' => 'string'
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 

        return $this->executeService($this->service_transformer, $request, [], "Service gallery updated successfully");
    }
    
}
