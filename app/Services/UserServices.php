<?php

namespace App\Services;

use Exception;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserServices extends ImageServices
{
    protected $user_repository;

    public function __construct() {
        $this->user_repository = new UserRepository();
    }

    public function signUp($data){
        $validator = Validator::make($data, [
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'email' => 'email|required|unique:users,email',
            'password' => 'string|min:8|confirmed|required',
            'address' => 'string',
            'phone_number' => 'string'
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
            'first_name' => 'string',
            'last_name' => 'string',
            'address' => 'string',
            'phone_number' => 'string',
            'profile_image' => 'file'
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 
        
        $userId = $data['id'];
        unset($data['id']);


        if(isset($data['profile_picture'])){
            $data['profile_picture_path'] = $this->_storeImage($data['profile_picture'], 'profile', auth()->user()->id);
            unset($data['profile_picture']);
        }

        $this->user_repository->updateUser($userId, $data);        
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

        $user = Auth::user();
        if ($user) {
            $user->tokens->each(fn($token) => $token->delete());
        }
    }

    function resetPassword($data){
        $validator = Validator::make($data, [
            'user' => 'required',
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|confirmed',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 
        
        if(!Hash::check($data['currentPassword'], $data['user']->password)) {
            throw new Exception(
                'Current password is incorrect',
                422
            );
        }

        $this->user_repository->updateUser($data['user']->id, ['password' => Hash::make($data['newPassword'])]);
        return;
    }    

}
