<?php

namespace App\Repositories;

use App\Enums\AvailabilityStatus;
use App\Models\AdminServiceProviderServices;
use App\Models\Service;
use App\Models\ServiceActivity;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderService;
use App\Models\ServiceProviderSubscriptionPlan;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ServiceProviderServices;

class ServiceProviderRepository{
    public function createServiceProvider($data) {
        return ServiceProvider::create($data);
    }
    
    public function createServiceProviderSubscriptionPlan($data) {
        return ServiceProviderSubscriptionPlan::create($data);
    }
    
    public function createServiceProviderService($data) {
        return ServiceProviderService::create($data);
    }

    
    function updateServiceProvider($id, $data) {
        ServiceProvider::where('id', $id)
                       ->update($data);
    }

    function updateServiceProviderService($id, $data) {
        ServiceProviderService::where('id', $id)
                              ->update($data);
    }

    function createAdminServiceProvider($data) {
        AdminServiceProviderServices::create($data);
    }

    function serviceProviderServiceAdjudication($data){
        if($data['approve'] == 1)
            $this->updateServiceProviderService($data['service_provider_service_id'], [
                'availability_status_id' => AvailabilityStatus::Active,
            ]);
        else if($data['approve'] == 0)
            $this->updateServiceProviderService($data['service_provider_service_id'], [
                'availability_status_id' => AvailabilityStatus::Rejected,
            ]);

        $this->createAdminServiceProvider([
            'service_id' => $data['service_provider_service_id'],
            'admin_id' => $data['admin_id'],
            'approve' => $data['approve'],
            'reason' => $data['reason'] ?? null
         ]);
    }



    function getServiceCategories() {
        return ServiceCategory::with('services')->get();
    }

    function getSubscriptionPlans() {
        return SubscriptionPlan::get();
    }

    function viewPendingServiceProviders($data) {
        return User::whereHas('serviceProvider', function($query) {
            $query->whereHas('serviceProviderPendingServices');
        })->with('serviceProvider.serviceProviderPendingServices')
          ->simplePaginate(10, [
                'id',
                'first_name',
                'last_name',
                'address',
        ], 'page', $data['page'] ?? 1);
    }

    function getLatestServiceProvidersAdjudication($page) {
        return ServiceProviderService::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->with(['adminServices.admin', 'availabilityStatus', 'serviceProvider.user'])
                                ->simplePaginate(10, ['*'], 'page', $page ?? 1);    }

    function getLatestRejectedServiceProviders($page) {
        return AdminServiceProviderServices::where('approve', 0)
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->with(['services.serviceProvider.user', 'admin'])
                                ->simplePaginate(10, ['*'], 'page', $page ?? 1);
    }

    function getLatestAcceptedServiceProviders($page) {
        return AdminServiceProviderServices::where('approve', 1)
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->with(['services.serviceProvider.user', 'admin'])
                                ->simplePaginate(10, ['*'], 'page', $page ?? 1);
    }

    function getServiceProviders($data) {
        return User::whereHas('serviceProvider', function($query) use ($data) {
            $query->whereHas('serviceProviderServices', function($subQuery) use ($data) {
                $subQuery->where('service_id', $data['service_id']);
            });
        })->with(['serviceProvider'])
          ->simplePaginate(10, [
                'id',
                'first_name',
                'last_name',
                'address',
                'profile_picture_path'
        ], 'page', $data['page'] ?? 1);
    }

    function getBestServiceProviders($data) {
        return ServiceProvider::whereHas('serviceProviderServices')
                              ->with(['user', 'serviceProviderServices.service.serviceCategory'])
                              ->orderBy('rate', 'desc')
                              ->paginate(10, ['*'], 'page', $data['page'] ?? 1);
    }

    function updateService($serviceProviderServiceId, $data) {
        return ServiceProviderService::where('id', $serviceProviderServiceId)
                                     ->update($data);
    }

    function getServiceProviderServices($serviceProviderId) {
        return ServiceProviderService::where('service_provider_id', $serviceProviderId)
                                    ->with(['service.serviceCategory', 'availabilityStatus'])
                                    ->get();
    }


    function getServiceProviderDetails($serviceProviderId) {
        return User::whereHas('serviceProvider', function($query) use ($serviceProviderId) {
            $query->where('id', $serviceProviderId);
        })->with(['serviceProvider.serviceProviderServices.service', 'serviceProvider.serviceProviderServices.availabilityStatus'])
          ->first();
    }

    function getServiceProviderByUserId($userId) {
        return ServiceProvider::where('user_id', $userId)->first();
    }

    function getServiceByName($service) {
        return Service::where('name', $service)->first();
    }

    function getServiceProviderServiceGallery($serviceProviderServiceId) {
        return ServiceProviderService::where('id', $serviceProviderServiceId)
                                     ->with(['service', 'gallery'])
                                     ->get();
    }

    function addService($serviceProviderServiceId) {
        return ServiceProviderService::where('id', $serviceProviderServiceId)
                                     ->with(['service', 'gallery'])
                                     ->get();
    }

    public function deleteServiceProviderService($id) {
        return ServiceProviderService::where('id', $id)
                                     ->delete();
    }

    public function updateStatus(ServiceActivity $serviceActivity, string $status): bool
    {
        return $serviceActivity->update(['status' => $status]);
    }
    public function getServiceProviderById($id)
    {
        return ServiceProvider::where('id', $id)->first();
    }


}