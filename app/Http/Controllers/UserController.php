<?php

namespace App\Http\Controllers;

use App\Services\ServiceTransformer;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $service_transformer;
    
    function __construct(){
        $this->service_transformer = new ServiceTransformer();
    }

    function signUp(Request $request) {
        return $this->executeService($this->service_transformer, $request, [], 'User registered successfully');
    }
    
    function login(Request $request) {
        return $this->executeService($this->service_transformer, $request, [], 'User logged in successfully');
    }

    public function viewMyProfile(){
        $additionalData = ['id' => auth()->user()->id];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, "User's profile fetched successfully");
    }

    public function updateMyProfile(Request $request){
        $additionalData = ['id' => auth()->user()->id];
        return $this->executeService($this->service_transformer, $request, $additionalData, "User's profile updated successfully");
    }

    public function logout(){
        $additionalData = ['id' => auth()->user()->id];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, "User logged out successfully");
    }

}
