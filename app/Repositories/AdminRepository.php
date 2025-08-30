<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Models\User;

class AdminRepository{
    public function create($data) {
        return Admin::create($data);
    }  

    public function viewAdmins($data){
        return Admin::paginate(10, '*','page', $data['page'] ?? 1);
    }

    public function getAdminDetails_byId($id) {
        $admin = Admin::where('id', $id)
                ->first();

        if (!$admin) {
            throw new \Exception('Admin not found', 404);
        }

        return $admin; 
    }

    public function removeAdmin_byId($id)
    {
        $admin = Admin::where('id', $id)
                ->first();

        if (!$admin) {
            throw new \Exception('Admin not found', 404);
        }

        $admin->delete();
        return $admin; 
    }

    public function searchAdmin_byName($data){
        return Admin::where('first_name', 'LIKE', '%' . $data['name'] . '%')
                    ->paginate(10, '*', 'page', $data['page'] ?? 1);

    }

    public function getAdminDetails_byEmail($email) {
        return Admin::where('email', '=', $email)
                   ->first();
    }

    public function updateAdmin($adminId, $data) {
        return Admin::where('id', '=', $adminId)
                   ->update($data);
    }

    public function incrementAcceptedProperties($adminId)
    {   
        return Admin::where('id', $adminId)->increment('accepted_properties');
    }

    public function incrementRejectedProperties($adminId)
    {   
        return Admin::where('id', $adminId)->increment('rejected_properties');
    }

    public function incrementAcceptedServices($adminId)
    {   
        return Admin::where('id', $adminId)->increment('accepted_services');
    }

    public function incrementRejectedServices($adminId)
    {   
        return Admin::where('id', $adminId)->increment('rejected_services');
    }

}