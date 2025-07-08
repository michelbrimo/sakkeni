<?php

namespace App\Http\Controllers;

use App\Services\ServiceTransformer;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $service_transformer;
    
    function __construct(){
        $this->service_transformer = new ServiceTransformer();
    }

    function adminRegister(Request $request) {
        return $this->executeService($this->service_transformer, $request, [], 'Admin registered successfully');
    }

    function adminLogin(Request $request) {
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
        $additionalData = ['admin_id' => auth('admin')->user()->id];
        
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Property adjudicated Successfully');
    }
    
    function viewPendingServiceProviders(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];

        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Pending Service Providers fetched successfully');
    }
    
    function serviceProviderAdjudication(Request $request)
    {
        $additionalData = ['admin_id' => auth('admin')->user()->id];

        return $this->executeService($this->service_transformer, $request, $additionalData, 'Service Provider adjudicated Successfully');
    }
}
