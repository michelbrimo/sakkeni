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

    function RegisterAdmin(Request $request) {
        return $this->executeService($this->service_transformer, $request, [], 'Admin registered successfully');
    }

    function viewAdmins(Request $request) {
        return $this->executeService($this->service_transformer, $request, [], 'The list of Admins');
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
}
