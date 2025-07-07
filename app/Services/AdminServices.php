<?php

namespace App\Services;

use Exception;
use App\Repositories\AdminRepository;
use App\Repositories\PropertyRepository;
use App\Repositories\ServiceProviderRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminServices{
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

    function adminLogout($data) {
        $validator = Validator::make($data, [
            'id' => 'integer|required',
        ]);
    
        if ($validator->fails()) {
            throw new Exception(
                $validator->errors()->first(),
                422
            );
        }
    
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
        
        $result = $this->admin_repository->searchAdmin_byName($data);

        return $result;
    }

    function viewPendingProperties($data) {
        return $this->property_repository->viewPendingProperties($data);
    }
    
    function propertyAdjudication($data) {
        return $this->property_repository->propertyAdjudication($data);
    }
    
    function serviceProviderAdjudication($data) {
        return $this->service_provider_repository->serviceProviderAdjudication($data);
    }

}