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
        return $this->admin_repository->getAdminDetails_byId($data['id']);
        
    }

}