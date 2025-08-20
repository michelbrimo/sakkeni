<?php

namespace App\Repositories;

use App\Models\Seller;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderService;
use App\Models\ServiceProviderSubscriptionPlan;
use App\Models\User;

class UserRepository{
    public function create($data) {
        return User::create($data);
    }
    
    public function createSeller($data) {
        return Seller::create($data);
    }
    
    public function getUserDetails_byEmail($email) {
        return User::where('email', '=', $email)
                   ->first();
    }

    public function getAccountType($id) {
        $user = User::with(['seller', 'serviceProvider'])->find($id);

        if ($user->seller) {
            $roles[] = 'Seller';
        }

        if ($user->serviceProvider) {
            $roles[] = 'Service Provider';
        }

        return empty($roles) ? ['User'] : $roles;
    }

    public function getUserDetails_byId($id) {
       return User::where('id', $id)->with('seller.accountType')->first();
    }

    public function updateUser($userId, $data) {
        return User::where('id', '=', $userId)
                   ->update($data);
    }

}