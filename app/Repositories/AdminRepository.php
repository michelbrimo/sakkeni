<?php

namespace App\Repositories;

use App\Enums\AvailabilityStatus;
use App\Enums\SellType;
use App\Models\Admin;
use App\Models\Log;
use App\Models\Property;
use App\Models\ServiceActivity;
use App\Models\User;

class AdminRepository{
    public function create($data) {
        return Admin::create($data);
    }  

    public function viewAdmins($data){
        return Admin::paginate(10, '*','page', $data['page'] ?? 1);
    }

    public function getAdminDetails_byId($id) {
        $admin = Admin::where('id', $id)
                ->first();

        if (!$admin) {
            throw new \Exception('Admin not found', 404);
        }

        return $admin; 
    }

    public function removeAdmin_byId($id)
    {
        $admin = Admin::where('id', $id)
                ->first();

        if (!$admin) {
            throw new \Exception('Admin not found', 404);
        }

        $admin->delete();
        return $admin; 
    }

    public function searchAdmin_byName($data){
        return Admin::where('first_name', 'LIKE', '%' . $data['name'] . '%')
                    ->paginate(10, '*', 'page', $data['page'] ?? 1);

    }

    public function getAdminDetails_byEmail($email) {
        return Admin::where('email', '=', $email)
                   ->first();
    }

    public function updateAdmin($adminId, $data) {
        return Admin::where('id', '=', $adminId)
                   ->update($data);
    }

    public function incrementAcceptedProperties($adminId)
    {   
        return Admin::where('id', $adminId)->increment('accepted_properties');
    }

    
    public function incrementRejectedProperties($adminId)
    {   
        return Admin::where('id', $adminId)->increment('rejected_properties');
    }

    public function searchPropertyId($data)
    {   
        return Property::where('admin_id', $data['admin_id'])
                       ->where('id', $data['property_id'])
                       ->where('availability_status_id', AvailabilityStatus::Active)
                       ->first();
    }


    public function getMyProperties($data)
    {   
        $query = Property::where('admin_id', $data['admin_id'])
                         ->where('sell_type_id', $data['sell_type_id'])
                         ->where('availability_status_id', AvailabilityStatus::Active)
                         ->with([
                                'coverImage',
                                'availabilityStatus',
                                'owner',
                                'propertyType',
                                'location.country',
                                'location.city',
                                'residential.residentialPropertyType',
                                'commercial.commercialPropertyType',
                            ]);

        
        if($data['sell_type_id'] == SellType::OFF_PLAN)
            $query->with('offPlan');
        else if($data['sell_type_id'] == SellType::RENT)
            $query->with('rent');
        else if($data['sell_type_id'] == SellType::PURCHASE)
            $query->with('purchase');


        return $query->simplePaginate(10, [
                    'id',
                    'location_id',
                    'property_type_id',
                    'owner_id',
                    'availability_status_id',
            ], 'page', $data['page'] ?? 1);
    }

    public function incrementAcceptedServices($adminId)
    {   
        return Admin::where('id', $adminId)->increment('accepted_services');
    }

    public function incrementRejectedServices($adminId)
    {   
        return Admin::where('id', $adminId)->increment('rejected_services');
    }

    function getLog($page) {
        return Log::orderBy('created_at', 'desc')
                    ->simplePaginate(10, '*', 'page', $page ?? 1);
    }

    function getServiceActivity($page) {
    return ServiceActivity::where('status', 'Declined')
                ->orderBy('created_at', 'desc')
                ->with('user', 'service_provider.user', 'quote')
                ->simplePaginate(10, '*', 'page', $page ?? 1);
}

}