<?php

namespace App\Services;

use Exception;

class ServiceTransformer{
    public $aspect_mapper = [
        'signUp' => [], 
        'login' => [], 
        'viewUserProfile' => [], 
        'updateUserProfile' => [],
        'logout' => [],
        'resetPassword' => [],
        'upgradeToSeller' => [],

        'addProperty' => ['TransactionAspect'],
        'updateProperty' => ['TransactionAspect'],
        'viewProperties' => [],
        'filterProperties' => ['UserSearchTrackingAspect'],
        'viewPropertyDetails' => ['UserClickTrackingAspect'],
        'viewRecommendedProperties' => [], 

        'deleteProperty' => [],
        'addPropertyToFavorite' => [],
        'removePropertyFromFavorite' => [],
        'viewFavoriteProperties' => [],
        'adminLogin' => [],
        'adminLogout' => [],
        'adminRegister' => [],
        'viewAdmins' =>[],
        'viewAdminProfile'=>[],
        'removeAdmin'=> [],
        'searchAdmin'=>[],
        'viewPendingProperties' => [],
        'propertyAdjudication' => [],
        'viewPendingServiceProviders' => [], 
        'serviceProviderServiceAdjudication' => [], 

        
        'upgradeToServiceProvider' => [],
        'viewServiceProviders' => [], 
        'viewServiceProviderDetails' => [], 
        'viewServiceProviderServiceGallery' => [], 

        'viewAmenities' => [],
        'viewDirections' => [],
        'viewPropertyTypes' => [],
        'viewCommercialPropertyTypes' => [],
        'viewResidentialPropertyTypes' => [],
        'viewCountries' => [],
        'viewOwnershipTypes' => [],
        'viewAvailabilityStatus' => [],
        'viewServiceCategories' => [],
    ];

    private $service_mapper = [];

    protected $userService;
    protected $adminService;

    public function __construct()
    {   
        $this->userService = new UserServices();
        $this->adminService = new AdminServices();

        $this->service_mapper = [
            "User" => "App\\Services\\UserServices",
            "Property" => "App\\Services\\PropertyServices",
            "Admin" => "App\\Services\\AdminServices",
            "ServiceProvider" => "App\\Services\\ServiceProviderServices",
        ];
    }

    public function execute($data, $service, $function_name, $success_message) {
        try{
            $this->executeBefore($function_name);

            $service_obj = new $this->service_mapper[$service];
            $result = $service_obj->$function_name($data); 

            $response = $this->response(
                true,
                $success_message,
                200,
                $result
            );

            $this->executeAfter($function_name);
        }
        catch(Exception $e){
            $response = $this->response(
                false,
                $e->getMessage(),
                $e->getCode(),
                null,
            );
            
            $this->executeException($function_name);
        }

        return $response;
    }
    
    public function executeBefore($function_name) {
        $aspects = $this->aspect_mapper[$function_name];
        foreach ($aspects as $aspect) {
            $object = 'App\\Aspects\\'. $aspect;
            $class = new $object();
            $class->before($function_name);
        }
    }

    public function executeAfter($function_name) {
        $aspects = $this->aspect_mapper[$function_name];

        foreach ($aspects as $aspect) {
            $object = 'App\\Aspects\\'. $aspect;
            $class = new $object();
            $class->after($function_name);
        }
    }

    public function executeException($function_name) {
        $aspects = $this->aspect_mapper[$function_name];

        foreach ($aspects as $aspect) {
            $object = 'App\\Aspects\\'. $aspect;
            $class = new $object();
            $class->exception($function_name);
        }
    }

    public function response($status, $message, $code=200, $data){
        if (!is_int($code) || $code < 100 || $code > 599) {
            $code = 500;
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $code);
        
    }

}