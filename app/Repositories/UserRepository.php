<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository{
    public function create($data) {
        return User::create($data);
    }

    public function getUserDetails_byEmail($email) {
        return User::where('email', '=', $email)
                   ->first();
    }

    public function getUserDetails_byId($id) {
        return User::find($id);
    }

    public function updateUser($userId, $data) {
        return User::where('id', '=', $userId)
                   ->update($data);
    }
}