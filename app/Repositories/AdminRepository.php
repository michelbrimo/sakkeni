<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Models\User;

class AdminRepository{
    public function create($data) {
        return Admin::create($data);
    }  

    public function viewAdmins($data){
        // return Admin::get();
        return Admin::simplePaginate(10, '*','page', $data['page'] ?? 1);
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
        return Admin::where('username', 'LIKE', '%' . $data['name'] . '%')
        ->get();
    }

    public function getAdminDetails_byEmail($email) {
        return Admin::where('email', '=', $email)
                   ->first();
    }
}