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
}