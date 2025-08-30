<?php

namespace App\Services;

use Exception;
use App\Repositories\AdminRepository;
use App\Repositories\PropertyRepository;
use App\Repositories\ServiceProviderRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminServices extends ImageServices{
    protected $admin_repository;
    protected $property_repository;
    protected $service_provider_repository;

    public function __construct() {
        $this->admin_repository = new AdminRepository();
        $this->property_repository = new PropertyRepository();
        $this->service_provider_repository = new ServiceProviderRepository();
    }

    public function adminRegister($data){        
        $validator = Validator::make($data, [
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'email' => 'email|required',
            'password' => 'string|min:8|confirmed|required',
            'phone_number' => 'string|min:10|required',
            'address' => 'string|required',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }  

        $result = $this->admin_repository->create($data);
        $result['token'] = $result->createToken('personal access token')->plainTextToken;
            
        return $result;
    }

    public function adminLogin($data){
        $validator = Validator::make($data, [
            'email' => 'email|required',
            'password' => 'required|string',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }  

        $result = $this->admin_repository->getAdminDetails_byEmail($data['email']);

        if ($result && Hash::check($data['password'], $result->password)) {
            $result['token'] = $result->createToken('personal access token')->plainTextToken;
            return $result;
        }

        else
            throw new Exception("Email or Password are incorrect", 400);
    }

    function adminLogout() {
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            $admin->tokens->each(fn($token) => $token->delete());
        }
    }

    public function viewAdmins($data){
        return  $this->admin_repository->viewAdmins($data);
    }

    public function viewAdminProfile($data){
        $admin = $this->admin_repository->getAdminDetails_byId($data['id']); 
        if(!$admin){
            throw new Exception('Admin not found', 404);
        }
        return $admin;
    }

    public function removeAdmin($data){
        $admin = $this->admin_repository->removeAdmin_byId($data['id']);
        if (!$admin) {
            throw new Exception('Admin not found', 404);
        }
        return $admin;
    } 

    public function searchAdmin($data){
        $validator = Validator::make($data, [
            'name' => 'string|required',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }
        return $this->admin_repository->searchAdmin_byName($data);
    }

    function viewPendingProperties($data) {
        return $this->property_repository->viewPendingProperties($data);
    }

    function viewLatestAcceptedProperty($data) {
        return $this->property_repository->viewLatestAcceptedProperty($data['page']);
    }
    
    function viewLatestRejectedProperty($data) {
        return $this->property_repository->viewLatestRejectedProperty($data['page']);
    }
    
    function viewLatestPropertyAdjudication($data) {
        return $this->property_repository->viewLatestPropertyAdjudication($data['page']);
    }
    
    function viewSoldProperties($data) {
        return $this->property_repository->getSoldProperties($data['page']);
    }
    
    function propertyAdjudication($data) {
        $validator = Validator::make($data, [
            'property_id' => 'integer|required',
            'approve' => 'boolean|required',
            'reason' => 'string',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }

        $this->property_repository->propertyAdjudication($data);
        
        if($data['approve'] == 1){
            $this->admin_repository->incrementAcceptedProperties($data['admin_id']);     
        }
        else{
            $this->admin_repository->incrementRejectedProperties($data['admin_id']);     
        }
    }

    function viewPendingServiceProviders($data) {
        return $this->service_provider_repository->viewPendingServiceProviders($data);
    }

    function viewLatestAcceptedServiceProviders($data) {
        return $this->service_provider_repository->getLatestAcceptedServiceProviders($data['page']);
    }

    function viewLatestRejectedServiceProviders($data) {
        return $this->service_provider_repository->getLatestRejectedServiceProviders($data['page']);
    }

    function viewLatestServiceProvidersAdjudication($data) {
        return $this->service_provider_repository->getLatestServiceProvidersAdjudication($data['page']);
    }
    
    function serviceProviderServiceAdjudication($data) {
        $validator = Validator::make($data, [
            'service_provider_service_id' => 'integer|required',
            'approve' => 'boolean|required',
            'reason' => 'string',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }

        $this->service_provider_repository->serviceProviderServiceAdjudication($data);

        if($data['approve'] == 1){   
            // Get the service provider associated with the service being approved
            $serviceProvider = $this->service_provider_repository->getServiceProviderByServiceId($data['service_provider_service_id']);

            if ($serviceProvider && $serviceProvider->status === 'pending_approval') {
                $this->service_provider_repository->updateServiceProvider($serviceProvider->id, ['status' => 'pending_payment']);                
            }

            $this->admin_repository->incrementAcceptedServices($data['admin_id']);     
        }
        else{
            $this->admin_repository->incrementRejectedServices($data['admin_id']);     
        }

        $this->service_provider_repository->serviceProviderServiceAdjudication($data);
    }

    public function updateAdminProfile($data) {        
        $adminId = $data['id'];
        unset($data['id']);

        if(isset($data['profile_picture'])){
            $data['profile_picture_path'] = $this->_storeImage($data['profile_picture'], 'admin_profile', auth('admin')->user()->id);
            unset($data['profile_picture']);
        }

        $this->admin_repository->updateAdmin($adminId, $data);
    }

}