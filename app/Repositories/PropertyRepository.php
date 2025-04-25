<?php

namespace App\Repositories;

use App\Models\Appartment;
use App\Models\CommercialProperty;
use App\Models\OffPlanProperty;
use App\Models\Property;
use App\Models\Purchase;
use App\Models\ReadyToMoveInProperty;
use App\Models\Rent;
use App\Models\ResidentialProperty;
use App\Models\Villa;
use Illuminate\Support\Facades\DB;

class PropertyRepository{
    public function create($data) {
        return Property::create($data);
    }

    public function createOffPlanProperty($data) {
        return OffPlanProperty::create($data);
    }

    public function createReadyToMoveInProperty($data) {
        return ReadyToMoveInProperty::create($data);
    }
    
    public function createRent($data) {
        return Rent::create($data);
    }
    
    public function createPurchase($data) {
        return Purchase::create($data);
    }

    public function createCommercialProperty($data) {
        return CommercialProperty::create($data);
    }

    public function createResidentialProperty($data) {
        return ResidentialProperty::create($data);
    }

    public function createVilla($data) {
        return Villa::create($data);
    }

    public function createAppartment($data) {
        return Appartment::create($data);
    }


    public function getProperties($filters){
        return Property::query()
        ->join('locations', 'properties.location_id', '=', 'locations.id')
        ->join('countries', 'locations.country_id', '=', 'countries.id')
        ->join('cities', 'locations.city_id', '=', 'cities.id')

        ->filterByCountry($filters['country'] ?? null)
        ->filterByCity($filters['city'] ?? null)
        ->filterByPrice($filters['min_price'] ?? null, $filters['max_price'] ?? null)
        ->paginate(10, [
            "properties.id",
            DB::raw('COALESCE(purchases.price, CONCAT(rents.price, " per ", rents.lease_period), off_plan_properties.overall_payment) AS price'),
            "countries.name as country",
            "cities.name as city",
            "locations.additional_info"
        ], 'page', $filters['page']);
    }
}