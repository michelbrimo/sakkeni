<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Amenity;
use App\Models\City;
use App\Models\CommercialPropertyType;
use App\Models\Country;
use App\Models\Direction;
use App\Models\PropertyType;
use App\Models\ResidentialProperty;
use App\Models\ResidentialPropertyType;
use App\Models\SellType;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $directions = ['North', 'South', 'East', 'West', 'North-East', 'North-West', 'South-East', 'South-West'];
        foreach ($directions as $direction) {
            Direction::create(['name' => $direction]);
        }

        Country::create(['name' => 'Syria']);
        City::create(['name' => 'Damascus', 'country_id' => 1]);

        $propertyTypes = ['Commercial', 'Residential'];
        foreach ($propertyTypes as $propertyType) {
            PropertyType::create(['name' => $propertyType]);
        }

        $residentialPropertyTypes = ['Appartment', 'Villa'];
        foreach ($residentialPropertyTypes as $residentialPropertyType) {
            ResidentialPropertyType::create(['name' => $residentialPropertyType]);
        }

        $commercialPropertyTypes = ['Office'];
        foreach ($commercialPropertyTypes as $commercialPropertyType) {
            CommercialPropertyType::create(['name' => $commercialPropertyType]);
        }
        
        $sellTypes = ['Purchase', 'Rent'];
        foreach ($sellTypes as $sellType) {
            SellType::create(['name' => $sellType]);
        }
       
        $propertyPhysicalStatusTypes = ['Ready To Move In', 'Off Plan'];
        foreach ($propertyPhysicalStatusTypes as $propertyPhysicalStatusType) {
            CommercialPropertyType::create(['name' => $propertyPhysicalStatusType]);
        }

        User::factory(10)->create();
        Country::factory(10)->create();
        City::factory(10)->create();
        Amenity::factory(10)->create();
    }
}
