<?php

namespace App\Repositories;

use App\Enums\AvailabilityStatus;
use App\Models\AdminServiceProvider;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\User;

class ServiceProviderRepository{
    function updateServiceProvider($id, $data) {
        ServiceProvider::where('id', $id)
                       ->update($data);
    }

    function createAdminServiceProvider($data) {
        AdminServiceProvider::create($data);
    }

    function serviceProviderAdjudication($data){
        if($data['approve'] == 1)
            $this->updateServiceProvider($data['service_provider_id'], [
                'availability_status_id' => AvailabilityStatus::Active,
            ]);
        else if($data['approve'] == 0)
            $this->updateServiceProvider($data['service_provider_id'], [
                'availability_status_id' => AvailabilityStatus::Rejected,
            ]);

        $this->createAdminServiceProvider([
            'service_provider_id' => $data['service_provider_id'],
            'admin_id' => $data['admin_id'],
            'approve' => $data['approve'],
            'reason' => $data['reason'] ?? null
         ]);
    }



    function getServiceCategories() {
        return ServiceCategory::with('services')->get();
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
}