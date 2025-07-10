<?php

namespace App\Services;

use App\Enums\AvailabilityStatus;
use Exception;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserServices extends ImageServices
{
    protected $user_repository;

    public function __construct() {
        $this->user_repository = new UserRepository();
    }

    public function signUp($data){
        $result = $this->user_repository->create($data);
        $result['token'] = $result->createToken('personal access token')->plainTextToken;
        return $result;
    }

    public function login($data){
        $result = $this->user_repository->getUserDetails_byEmail($data['email']);

        if ($result && Hash::check($data['password'], $result->password)) {
            $result['token'] = $result->createToken('personal access token')->plainTextToken;
            return $result;
        }
        else
            throw new Exception("Email or Password are incorrect", 400);
    }

    public function viewUserProfile($data) {
        $result = $this->user_repository->getUserDetails_byId($data['id']);

        if ($result)
            return $result;
        else
            throw new Exception('User not found', 400);            
    }

    public function updateUserProfile($data) {        
        $userId = $data['id'];
        unset($data['id']);

        if(isset($data['profile_picture'])){
            $data['profile_picture_path'] = $this->_storeImage($data['profile_picture'], 'profile', auth()->user()->id);
            unset($data['profile_picture']);
        }

        $this->user_repository->updateUser($userId, $data);
    }

    function logout($data){
        $user = Auth::user();
        if ($user) {
            $user->tokens->each(fn($token) => $token->delete());
        }
    }

    function resetPassword($data){
        if(!Hash::check($data['currentPassword'], $data['user']->password)) {
            throw new Exception(
                'Current password is incorrect',
                422
            );
        }

        $this->user_repository->updateUser($data['user']->id, ['password' => Hash::make($data['newPassword'])]);
        return;
    }    

    function upgradeToSeller($data) {
        if(!$data['user']->address  || !$data['user']->phone_number){
            throw new Exception(
                'Please fill the address and phone number fields in your profile first',
                422
            );
        }

        $this->user_repository->createSeller([
            'user_id' => $data['user']->id, 'account_type_id'=>$data['account_type_id']
        ]);
    }
    
    function upgradeToServiceProvider($data) {
        if(!$data['user']->address  || !$data['user']->phone_number){
            throw new Exception(
                'Please fill the address and phone number fields in your profile first',
                422
            );
        }

        $serviceProvider = $this->user_repository->createServiceProvider([
            'user_id' => $data['user']->id,
            'availability_status_id'=> AvailabilityStatus::Pending,
        ]);

        $this->user_repository->createServiceProviderSubscriptionPlan([
            'service_provider_id' => $serviceProvider->id,
            'subscription_plan_id' => $data['subscription_plan_id'],
        ]);

        foreach ($data['services_id'] as $service_id){
            $this->user_repository->createServiceProviderServices([
                'service_provider_id' => $serviceProvider->id,
                'service_id' => $service_id,
            ]);
        }
    }
}
