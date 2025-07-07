<?php

namespace App\Repositories;

use App\Enums\AvailabilityStatus;
use App\Models\AdminServiceProvider;
use App\Models\ServiceProvider;

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

    
}