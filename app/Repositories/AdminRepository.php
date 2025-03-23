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
        return User::find($id);
    }
}