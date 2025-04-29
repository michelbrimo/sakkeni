<?php

namespace App\Repositories;
use App\Models\User;

class AdminRepository{
    public function create($data) {
        return User::create($data);
    }  

    public function viewAdmins(){
        return User::where('is_admin', 1)->get();
    }

    public function getAdminDetails_byId($id) {
        $admin = User::where('id', $id)
                ->where('is_admin', 1)
                ->first();

        if (!$admin) {
            throw new \Exception('Admin not found', 404);
        }

        return $admin; 
    }

    public function removeAdmin_byId($id)
    {
        $admin = User::where('id', $id)
                ->where('is_admin', 1)
                ->first();

        if (!$admin) {
            throw new \Exception('Admin not found', 404);
        }

        $admin->delete();
        return $admin; 
    }

    public function searchAdmin_byName($data){
        return User::where('is_admin', 1)
        ->where('username', 'LIKE', '%' . $data['name'] . '%')
        ->get();
    }
}