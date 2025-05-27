<?php

namespace App\Services;

use Exception;
use App\Repositories\AdminRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminServices{
    protected $admin_repository;

    public function __construct() {
        $this->admin_repository = new AdminRepository();
    }

    public function adminRegister($data){
        $validator = Validator::make($data, [
            'username' => 'string|required',
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

    public function viewAdmins(){
        return  $this->admin_repository->viewAdmins();
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

}