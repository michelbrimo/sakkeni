<?php

namespace App\Repositories;

use App\Enums\AvailabilityStatus;
use App\Models\City;
use App\Models\Location;
use App\Models\Property;
use App\Models\Service;
use App\Models\ServiceProviderService;
use App\Models\User;

class DashboardRepository{

   function getUserStats() {
        $users = User::with(['seller', 'serviceProvider'])->get();
        
        return [
            'total' => $users->count(),
            'sellers' => $users->where('seller', '!=', null)->count(),
            'service_providers' => $users->where('serviceProvider', '!=', null)->count()
        ];
    }

   function getPropertyStats() {
        return Property::with(['commercial', 'residential', 'offPlan', 'purchase', 'rent'])
            ->get()
            ->reduce(function ($carry, $property) {
                $carry['total']++;
                
                if ($property->commercial) {
                    $carry['commercial']['total']++;
                    if ($property->commercial->type === 'office') {
                        $carry['commercial']['office']++;
                    }
                }
                
                if ($property->residential) {
                    $carry['residential']['total']++;
                    if ($property->residential->villa) {
                        $carry['residential']['villa']++;
                    }
                    if ($property->residential->apartment) {
                        $carry['residential']['apartment']++;
                    }
                }
                
                if ($property->offPlan) $carry['off-plan']++;
                if ($property->purchase) $carry['purchase']++;
                if ($property->rent) $carry['rent']++;
                
                return $carry;
            }, [
                'total' => 0,
                'off-plan' => 0,
                'purchase' => 0,
                'rent' => 0,
                'commercial' => ['total' => 0, 'office' => 0],
                'residential' => ['total' => 0, 'villa' => 0, 'apartment' => 0]
            ]);
    }
   
    function getPropertyStatusStats() {
        return Property::with(['availabilityStatus'])
            ->get()
            ->reduce(function ($carry, $property) {
                switch($property->availability_status_id){
                    case AvailabilityStatus::Pending:
                        $carry['pending']++;
                        break;

                    case AvailabilityStatus::Active:
                        $carry['active']++;
                        break;

                    case AvailabilityStatus::Rejected:
                        $carry['rejected']++;
                        break;
                }
                
                return $carry;
            }, [
                'pending' => 0,
                'active' => 0,
                'rejected' => 0,
            ]);
    }

    function getServiceStatusStats() {
        return ServiceProviderService::with(['availabilityStatus'])
            ->get()
            ->reduce(function ($carry, $property) {
                switch($property->availability_status_id){
                    case AvailabilityStatus::Pending:
                        $carry['pending']++;
                        break;

                    case AvailabilityStatus::Active:
                        $carry['active']++;
                        break;

                    case AvailabilityStatus::Rejected:
                        $carry['rejected']++;
                        break;
                }

                return $carry;
            }, [
                'pending' => 0,
                'active' => 0,
                'rejected' => 0,
            ]);
    }

    function getPropertiesLocations() {
        $cities = City::get();

        $initialArray = $cities->mapWithKeys(function($city) {
            return [$city->name => 0];
        })->toArray();
        
        return Location::with('city') 
                       ->get()
                       ->reduce(function ($carry, $location) {
                           $carry[$location->city->name]++;
                           return $carry;
                        }, $initialArray);
    }
}