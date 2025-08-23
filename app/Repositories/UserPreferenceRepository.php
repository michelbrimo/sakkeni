<?php

namespace App\Repositories;

use App\Models\UserPreference;

class UserPreferenceRepository
{
    /**
     * @param int $userId
     * @return UserPreference|null
     */
    public function getLatestByUserId(int $userId): ?UserPreference
    {
        return UserPreference::where('user_id', $userId)
                             ->latest('updated_at')
                             ->first();
    }
}