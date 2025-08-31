<?php

namespace App\Services;

use Exception;

class ServiceTransformer{
    public $aspect_mapper = [
        'signUp' => [], 
        'login' => [], 
        'viewUserProfile' => [], 
        'updateUserProfile' => ['LoggingAspect'],
        'logout' => [],
        'resetPassword' => ['LoggingAspect'],
        'upgradeToSeller' => ['LoggingAspect'],

        'addProperty' => ['TransactionAspect', 'LoggingAspect'],
        'updateProperty' => ['TransactionAspect', 'LoggingAspect'],
        'viewProperties' => [],
        'filterProperties' => ['UserSearchTrackingAspect'],
        'viewPropertyDetails' => ['UserClickTrackingAspect'],
        'viewRecommendedProperties' => [], 
        'propertySold' => ['LoggingAspect'], 

        'deleteProperty' => ['LoggingAspect'],
        'addPropertyToFavorite' => ['favoriteAspect', 'LoggingAspect'],
        'removePropertyFromFavorite' => ['favoriteAspect', 'LoggingAspect'],
        'viewFavoriteProperties' => [],
        'adminLogin' => [],
        'adminLogout' => [],
        'adminRegister' => [],
        'viewAdmins' =>[],
        'viewAdminProfile'=>[],
        'removeAdmin'=> [],
        'searchAdmin'=>[],
        
        'viewPendingProperties' => [],        
        'viewLatestAcceptedProperty' => [],        
        'viewLatestRejectedProperty' => [],        
        'viewLatestPropertyAdjudication' => [],        
        'viewSoldProperties' => [],        
        'propertyAdjudication' => [],
        'viewMyProperties' => [],
        'searchId' => [],

        'viewPendingServiceProviders' => [], 
        'viewLatestAcceptedServiceProviders' => [], 
        'viewLatestRejectedServiceProviders' => [], 
        'viewLatestServiceProvidersAdjudication' => [], 
        'serviceProviderServiceAdjudication' => [], 
        
        
        'upgradeToServiceProvider' => ['LoggingAspect'],
        'viewServiceProviders' => [], 
        'viewBestServiceProviders' => [], 
        'viewServiceProviderDetails' => [], 
        'viewServiceProviderServiceGallery' => [], 
        'addService' => ['LoggingAspect'], 
        'removeService' => ['LoggingAspect'], 
        'editService' => ['LoggingAspect'], 
        'viewMyServices' => [], 


        'viewAmenities' => [],
        'viewDirections' => [],
        'viewPropertyTypes' => [],
        'viewCommercialPropertyTypes' => [],
        'viewResidentialPropertyTypes' => [],
        'viewCountries' => [],
        'viewOwnershipTypes' => [],
        'viewAvailabilityStatus' => [],
        'viewServiceCategories' => [],
        'viewSubscriptionPlans' => [],

        'reportProperty' => ['LoggingAspect'],
        'reportServiceProvider' => ['LoggingAspect'],
        'viewPropertyReports' => [],
        'viewServiceProviderReports' => [],
        'viewPropertyReportReasons' => [],
        'viewServiceProviderReportReasons' => [],
        'processReport' => [],

        'updateAdminProfile' => [],

        'viewTotalUsers' => [],
        'viewTotalProperties' => [],
        'viewPropertiesStatus' => [],
        'viewServiceStatus' => [],
        'viewPropertiesLocation' => [],
        
        'viewLog' => [],

        'viewConversations' => [],
        'viewMessages' => [],
        'sendMessage' => [],

        'requestQuote' => [],
        'updateQuoteRequest' => [],

        'viewUserQuotes'=> [],
        'viewProviderQuotes' => [],
        'submitQuote' => [],
        'acceptQuote' => [],
        'declineQuote' => [],
        'declineUserQuote'=>[],


        'createPaymentIntent' => [],
        'handleWebhook' => [], 
        
        'markAsComplete' => [],
        'markAsDecline' => [], 

        'submitReview'=>['UpdateServiceProviderRatingAspect'],

        'search'=>[],

        'createSubscriptionPaymentIntent' => [], 

        'createPropertyPaymentIntent' => [], 

        'getServiceActivity' => [],


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
            "Report" => "App\\Services\\ReportServices",
            "Conversation" => "App\\Services\\MessagingService",
            "Quote" => "App\\Services\\QuoteService",
            "Payment" => "App\\Services\\PaymentService",
            "Notification" => "App\\Services\\NotificationService", 
            "Dashboard" => "App\\Services\\DashboardServices",
        ];
    }

    public function execute($data, $service, $function_name, $success_message) {
        try{
            $this->executeBefore($function_name, $data);

            $service_obj = new $this->service_mapper[$service];
            $result = $service_obj->$function_name($data); 
            if ($service === 'Property' && $function_name === 'search' && is_array($result) && isset($result['properties'])) {
                // If it is, merge the debug info with the paginated properties for the final response
                $paginatedProperties = $result['properties']->toArray();
                $responseData = array_merge($paginatedProperties, ['debug_info' => $result['debug_info']]);

                $response = $this->response(
                    true,
                    $success_message,
                    200,
                    $responseData
                );
            } else {
                $response = $this->response(
                    true,
                    $success_message,
                    200,
                    $result
                );
            }
            $this->executeAfter($function_name, $result);
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
    
    public function executeBefore($function_name, $data) {
        $aspects = $this->aspect_mapper[$function_name];
        foreach ($aspects as $aspect) {
            $object = 'App\\Aspects\\'. $aspect;
            $class = new $object();
            $class->before($function_name, $data);
        }
    }

    public function executeAfter($function_name, $result=null) {
        
        $aspects = $this->aspect_mapper[$function_name];

        foreach ($aspects as $aspect) {
            $object = 'App\\Aspects\\'. $aspect;
            $class = new $object();
            $class->after($function_name, $result);
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