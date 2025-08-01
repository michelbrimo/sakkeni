<?php

namespace App\Repositories;

use App\Enums\AvailabilityStatus;
use App\Models\AdminServiceProvider;
use App\Models\AdminServiceProviderServices;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderService;
use App\Models\User;

class ServiceProviderRepository{
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

    function viewPendingServiceProviders($data) {
        return User::whereHas('serviceProvider', function($query) {
            $query->whereHas('serviceProviderServices', function($query){
                $query->where('availability_status_id', AvailabilityStatus::Pending);
            });
        })->with('serviceProvider')
          ->simplePaginate(10, [
                'id',
                'first_name',
                'last_name',
                'address',
        ], 'page', $data['page'] ?? 1);
    }

    function getServiceProviders($data) {
        return User::whereHas('serviceProvider', function($query) use ($data) {
            $query->whereHas('serviceProviderServices', function($subQuery) use ($data) {
                $subQuery->where('service_id', $data['service_id']);
            });
        })->with('serviceProvider')
          ->simplePaginate(10, [
                'id',
                'first_name',
                'last_name',
                'address',
        ], 'page', $data['page'] ?? 1);
    }

    function getServiceProviderDetails($serviceProviderId) {
        return User::whereHas('serviceProvider', function($query) use ($serviceProviderId) {
            $query->where('id', $serviceProviderId);
        })->with(['serviceProvider.serviceProviderServices.service'])
          ->first();
    }

    function getServiceProviderServiceGallery($serviceProviderServiceId) {
        return ServiceProviderService::where('id', $serviceProviderServiceId)
                                     ->with(['service', 'gallery'])
                                     ->get();
    }
}