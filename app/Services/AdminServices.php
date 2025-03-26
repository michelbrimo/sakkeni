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

    public function RegisterAdmin($data){
        $validator = Validator::make($data, [
            'username' => 'string|required',
            'email' => 'email|required',
            'password' => 'string|min:8|confirmed|required',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }  

        $data['is_admin'] = 1;
        
        $result = $this->admin_repository->create($data);
        $result['token'] = $result->createToken('personal access token')->plainTextToken;
            
        return $result;
    
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

}