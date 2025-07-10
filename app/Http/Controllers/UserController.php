<?php

namespace App\Http\Controllers;

use App\Services\ServiceTransformer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $service_transformer;
    
    function __construct(){
        $this->service_transformer = new ServiceTransformer();
    }

    function signUp(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'email' => 'email|required|unique:users,email',
            'password' => 'string|min:8|confirmed|required',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }  

        return $this->executeService($this->service_transformer, $request, [], 'User registered successfully');
    }
    
    function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'email|required',
            'password' => 'required|string',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }  

        return $this->executeService($this->service_transformer, $request, [], 'User logged in successfully');
    }

    public function viewMyProfile(){
        $additionalData = ['id' => auth()->user()->id];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, "User's profile fetched successfully");
    }

    public function updateMyProfile(Request $request){
        $validator = Validator::make($request->all(), [
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

        $additionalData = ['id' => auth()->user()->id];
        return $this->executeService($this->service_transformer, $request, $additionalData, "User's profile updated successfully");
    }

    public function logout(){
        $additionalData = ['id' => auth()->user()->id];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, "User logged out successfully");
    }
    
    public function resetPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|confirmed',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 

        $additionalData = ['user' => auth()->user()];
        return $this->executeService($this->service_transformer, $request, $additionalData, "password reseted successfully");
    }
    
    public function upgradeToSeller(Request $request){
        $validator = Validator::make($request->all(), [
            'account_type_id' => 'integer|required',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 

        $additionalData = ['user' => auth()->user()];
        return $this->executeService($this->service_transformer, $request, $additionalData, "upgraded to seller successfully");
    }
    
    public function upgradeToServiceProvider(Request $request){
        $validator = Validator::make($request->all(), [
            'subscription_plan_id' => 'integer|required',
            'services_id' => 'array'
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 

        $additionalData = ['user' => auth()->user()];
        return $this->executeService($this->service_transformer, $request, $additionalData, "upgraded to service provider successfully");
    }
}
