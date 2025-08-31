<?php

namespace App\Http\Controllers;

use App\Services\ServiceTransformer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

    public function viewMyProfile(){
        $additionalData = ['id' => auth('admin')->user()->id];
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

    function viewLatestAcceptedProperty(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'latest accepted Properties fetched successfully');
    }
    
    function viewLatestRejectedProperty(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'latest rejected Properties fetched successfully');
    }
    
    function viewLatestPropertyAdjudication(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'latest Properties adjudication fetched successfully');
    }

    function viewSoldProperties(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Properties Sold fetched successfully');
    }
    
    function propertyAdjudication(Request $request)
    {
        $additionalData = ['admin_id' => auth('admin')->user()->id];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Property adjudicated Successfully');
    }
    
    
    function viewMyProperties(Request $request, $sell_type_id)
    {
        $additionalData = [
          'page' => $request->query('page', 1),
          'sell_type_id' => $sell_type_id,
          'admin_id' => auth('admin')->user()->id
        ];

        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Your properties fetched successfully');
    }

    function searchId(Request $request)
    {
        $additionalData = [
          'admin_id' => auth('admin')->user()->id
        ];

        return $this->executeService($this->service_transformer, $request, $additionalData, 'Your properties fetched successfully');
    }
    
    function viewPendingServiceProviders(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Pending Service Providers fetched successfully');
    }
    
    function serviceProviderServiceAdjudication(Request $request)
    {
        $additionalData = ['admin_id' => auth('admin')->user()->id];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Service Provider adjudicated Successfully');
    }

    function viewLatestAcceptedServiceProviders(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'latest accepted Service Provider fetched Successfully');
    }

    function viewLatestRejectedServiceProviders(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'latest rejected Service Provider fetched Successfully');
    }

    function viewLatestServiceProvidersAdjudication(Request $request)
    {
        $additionalData = ['page' => $request->input('page', 1)];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'latest Service Provider adjudication fetched Successfully');
    }

    public function viewPropertyReports(Request $request, $status)
    {
        $additionalData = [
            'page' => $request->input('page', 1),
            'status' => $status,
        ];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Property reports fetched successfully');
    }


    public function viewServiceProviderReports(Request $request, $status)
    {
        $additionalData = [
            'page' => $request->input('page', 1),
            'status' => $status,
        ];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Service provider reports fetched successfully');
    }

    public function updateMyProfile(Request $request){
        $additionalData = ['id' => auth('admin')->user()->id];
        return $this->executeService($this->service_transformer, $request, $additionalData, "Admin's profile updated successfully");
    }

    public function processReport(Request $request, int $id)
    {
        $additionalData = [
            'report_id' => $id,
            'admin_id' => auth('admin')->user()->id,
        ];

        return $this->executeService($this->service_transformer, $request, $additionalData, 'Report processed successfully');
    }

    public function viewLog(Request $request)
    {
        $additionalData = [
            'page' => $request->input('page', 1)
        ];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'Log fetched successfully');
    }

    public function getServiceActivity(Request $request)
    {
        $additionalData = [
            'page' => $request->input('page', 1)
        ];
        return $this->executeService($this->service_transformer, new Request(), $additionalData, 'service activity fetched successfully');
    }
}
