<?php

namespace App\Repositories;

use App\Models\Property;

class PropertyRepository{
    public function create($data) {
        return Property::create($data);
    }
}