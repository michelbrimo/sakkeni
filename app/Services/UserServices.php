<?php

namespace App\Services;

use Exception;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserServices
{
    protected $user_repository;

    public function __construct() {
        $this->user_repository = new UserRepository();
    }

    public function signUp($data){
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
        
        $result = $this->user_repository->create($data);
        $result['token'] = $result->createToken('personal access token')->plainTextToken;
            
        return $result;
    
    }

    public function login($data){
        $validator = Validator::make($data, [
            'email' => 'email|required',
            'password' => 'required|string',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }  
        
        $result = $this->user_repository->getUserDetails_byEmail($data['email']);

        if ($result && Hash::check($data['password'], $result->password)) {
            $result['token'] = $result->createToken('personal access token')->plainTextToken;
            return $result;
        }

        else
            throw new Exception("Email or Password are incorrect", 400);
    }

    public function viewUserProfile($data) {
        $validator = Validator::make($data, [
            'id' => 'integer|required'
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 
        
        $result = $this->user_repository->getUserDetails_byId($data['id']);

        if ($result)
            return $result;
    
        else
            throw new Exception('User not found', 400);            
    }

    public function updateUserProfile($data) {
        $validator = Validator::make($data, [
            'id' => 'integer|required',
            'address' => 'string',
            'phone_number' => 'string',
            'profile_picture_path' => 'string',
            'is_admin' => 'boolean',
            'is_super_admin' => 'boolean'
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 
        
        $userId = $data['id'];
        unset($data['id']);

        $this->user_repository->updateUserProfile($userId, $data);        
    }

    function logout($data){
        $validator = Validator::make($data, [
            'id' => 'integer|required',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 

        Auth::user()->tokens->each(function($token, $key) {
            $token->delete();
          });
        }
}
