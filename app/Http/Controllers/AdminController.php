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
        $response = $this->executeService($this->service_transformer, new Request(), $additionalData, "Admin's profile fetched successfully");

        $responseData = $response->getData(true);

        if (empty($responseData['data'])) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        return $response;
    }
}
