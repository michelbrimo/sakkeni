<?php

namespace App\Http\Controllers;

use App\Models\ServiceProvider;
use App\Repositories\ServiceProviderRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Route;


class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function getRouteExploded($routeName){
        $exp_arr=explode(".",$routeName);
        if(isset($exp_arr) && count($exp_arr)==2){
            $service_function["service"]=$exp_arr[0];
            $service_function["function"]=$exp_arr[1];
            return $service_function;
        }else{
            return null;
        }
    }
    
    public function executeService($serviceTransformer, $request, $additionalData = [], $successMessage = '')
    {
        $currentRoute = Route::current();
        $routeName = $currentRoute->getName();
        $serviceFunction = $this->getRouteExploded($routeName);

        if (!$serviceFunction) {
            throw new \Exception("Invalid route name format. Ensure it contains service and function.");
        }

        $requestData = array_merge($request->all(), $additionalData);

        return $serviceTransformer->execute(
            $requestData,
            $serviceFunction['service'],
            $serviceFunction['function'],
            $successMessage
        );
    }

    public function getServiceProviderId(){
        $service_provider_repository = new ServiceProviderRepository();
        return $service_provider_repository->getServiceProviderByUserId(auth()->user()->id)->id;
    }

    public function getServiceId($service){
        $service_provider_repository = new ServiceProviderRepository();
        return $service_provider_repository->getServiceByName($service)->id;
    }
    
}
