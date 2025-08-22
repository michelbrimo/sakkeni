<?php

namespace App\Services;

use App\Enums\AvailabilityStatus;
use App\Repositories\PaymentRepository;
use App\Repositories\ServiceProviderRepository;
use Exception;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserServices extends ImageServices
{
    protected $user_repository;
    protected $service_provider_repository;
    protected $payment_service;
    protected $payment_repository;


    public function __construct() {
        $this->user_repository = new UserRepository();
        $this->service_provider_repository = new ServiceProviderRepository();
        $this->payment_repository = new PaymentRepository(); 
        $this->payment_service = new PaymentService();

    }

    public function signUp($data){
        $validator = Validator::make($data, [
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'email' => 'email|required|unique:users,email',
            'password' => 'string|min:8|confirmed|required',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }  

        $result = $this->user_repository->create($data);
        $result['token'] = $result->createToken('personal access token')->plainTextToken;
        return $result;
    }

    public function login($data){
        $validator = Validator::make($data, [
            'email' => 'email|required',
            'password' => 'required|string',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        }  

        $result = $this->user_repository->getUserDetails_byEmail($data['email']);

        if ($result && Hash::check($data['password'], $result->password)) {
            $result['token'] = $result->createToken('personal access token')->plainTextToken;
            $result['account_type'] = $this->user_repository->getAccountType($result->id);
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
        $validator = Validator::make($data, [
            'first_name' => 'string',
            'last_name' => 'string',
            'address' => 'string',
            'phone_number' => 'string',
            'profile_image' => 'file'
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 

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
        $validator = Validator::make($data, [
            'account_type_id' => 'integer|required',
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 

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
        $validator = Validator::make($data, [
            'subscription_plan_id' => 'integer|required',
            'services_id' => 'array | required',
            'description' => 'string'
        ]);

        if($validator->fails()){
            throw new Exception(
                $validator->errors()->first(),
                422);
        } 

        if(!$data['user']->address  || !$data['user']->phone_number){
            throw new Exception(
                'Please fill the address and phone number fields in your profile first',
                422
            );
        }

        $serviceProvider = $this->service_provider_repository->createServiceProvider([
            'user_id' => $data['user']->id,
            'description'=> $data['description'],
        ]);

        $this->service_provider_repository->createServiceProviderSubscriptionPlan([
            'service_provider_id' => $serviceProvider->id,
            'subscription_plan_id' => $data['subscription_plan_id'],
        ]);

        foreach ($data['services_id'] as $service_id){
            $this->service_provider_repository->createServiceProviderService([
                'service_provider_id' => $serviceProvider->id,
                'service_id' => $service_id,
                'availability_status_id'=> AvailabilityStatus::Pending,
            ]);
        }
    }

   public function markAsComplete($data)
    {
        $user = $data['user'];
        $serviceActivity = $data['service_activity'];

        if ($user->id !== $serviceActivity->user_id) {
            throw new Exception('Unauthorized', 403);
        }

        if ($serviceActivity->status !== 'In Progress') {
            throw new Exception('This service is not in a state that can be completed.', 422);
        }

        return DB::transaction(function () use ($serviceActivity) {
            $this->payment_repository->updateServiceActivityStatus($serviceActivity, 'Completed');

            $this->payment_service->createTransfer($serviceActivity);

            return $serviceActivity; 
        });
    }

    public function submitReview($data)
    {
        $validator = Validator::make($data, [
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first(), 422);
        }

        $user = $data['user'];
        $serviceActivity = $data['service_activity'];

        if ($user->id !== $serviceActivity->user_id) {
            throw new Exception('Unauthorized', 403);
        }

        if ($serviceActivity->status !== 'Completed') {
            throw new Exception('You can only review a completed service.', 422);
        }
        
        if ($serviceActivity->review) {
            throw new Exception('A review has already been submitted for this service.', 422);
        }

        $review = $this->user_repository->createReview([
            'service_activity_id' => $serviceActivity->id,
            'user_id' => $user->id,
            'service_provider_id' => $serviceActivity->service_provider_id,
            'rating' => $data['rating'],
            'review_text' => $data['review_text'] ?? null,
        ]);

        return $review;
    }
}
