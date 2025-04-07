<?php

namespace App\Repositories;

use App\Models\City;
use App\Models\Country;
use App\Models\Location;

class LocationRepository{
    public function create($data) {
        return Location::create($data);
    }

    public function getCountry_byName($name) {
        return Country::where('name', '=', $name)
                       ->first();
    }

    public function getCity_byName($name) {
        return City::where('name', '=', $name)
                       ->first();
    }
}