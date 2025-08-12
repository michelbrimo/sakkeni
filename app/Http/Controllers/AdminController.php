<?php

namespace App\Http\Controllers;

use App\Services\ServiceTransformer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    protected $service_transformer;
    
    function __construct(){
        $this->service_transformer = new ServiceTransformer();
    }

    function adminRegister(Request $request) {
        $validator = Validator::make($request->all(), [
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

        return $this->executeService($this->service_transformer, $request, [], 'Admin registered successfully');
    }

    function adminLogin(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'email|required',
            'password' => 'required|string',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }  


        return $this->executeService($this->service_transformer, $request, [], 'admin logged in successfully');
    }

    public function adminLogout(){
        $additionalData = ['id' => auth()->guard('admin')->user()->id];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, "admin logged out successfully");
    }

    function viewAdmins(Request $request) {
        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'The list of Admins');
    }

    public function viewAdminProfile($admin_id){
        $additionalData = ['id' => $admin_id];
        try{
            return $this->executeService($this->service_transformer, new Request(), $additionalData, "Admin's profile fetched successfully");
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function removeAdmin($admin_id){
        $additionalData = ['id' => $admin_id];
        try {
            return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Admin removed successfully');
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function searchAdmin(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'string|required',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }

        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, $request, $additionalData, "Admins fetched successfully");
    }

    function viewPendingProperties(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Pending Properties fetched successfully');
    }
    
    function propertyAdjudication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'integer|required',
            'approve' => 'boolean|required',
            'reason' => 'string',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }

        $additionalData = ['admin_id' => auth('admin')->user()->id];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Property adjudicated Successfully');
    }
    
    function viewPendingServiceProviders(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Pending Service Providers fetched successfully');
    }
    
    function serviceProviderServiceAdjudication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_provider_service_id' => 'integer|required',
            'approve' => 'boolean|required',
            'reason' => 'string',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }

        $additionalData = ['admin_id' => auth('admin')->user()->id];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Service Provider adjudicated Successfully');
    }

    public function viewPropertyReports(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Property reports fetched successfully');
    }

    public function viewServiceProviderReports(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Service provider reports fetched successfully');
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

        $additionalData = ['id' => auth('admin')->user()->id];
        return $this->executeService($this->service_transformer, $request, $additionalData, "Admin's profile updated successfully");
    }

}
