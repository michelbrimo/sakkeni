<?php

namespace App\Repositories;

use App\Models\OffPlanProperty;
use App\Models\Property;

class PropertyRepository{
    public function create($data) {
        return Property::create($data);
    }

    public function createOffPlanProperty($data) {
        return OffPlanProperty::create($data);
    }
}