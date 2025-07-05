<?php

namespace App\Repositories;

use App\Models\Seller;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderSubscriptionPlan;
use App\Models\User;

class UserRepository{
    public function create($data) {
        return User::create($data);
    }
    
    public function createSeller($data) {
        return Seller::create($data);
    }
    
    public function createServiceProvider($data) {
        return ServiceProvider::create($data);
    }
    
    public function createServiceProviderSubscriptionPlan($data) {
        return ServiceProviderSubscriptionPlan::create($data);
    }

    public function getUserDetails_byEmail($email) {
        return User::where('email', '=', $email)
                   ->first();
    }

    public function getUserDetails_byId($id) {
       return User::where('id', $id)->with('seller.accountType')->first();
    }

    public function updateUser($userId, $data) {
        return User::where('id', '=', $userId)
                   ->update($data);
    }

}