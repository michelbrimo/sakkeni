<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\AccountType;
use App\Models\Amenity;
use App\Models\AvailabilityStatus;
use App\Models\City;
use App\Models\CommercialPropertyType;
use App\Models\Country;
use App\Models\Direction;
use App\Models\LeasePeriodUnits;
use App\Models\OwnershipType;
use App\Models\PhysicalStatusType;
use App\Models\PropertyType;
use App\Models\ResidentialProperty;
use App\Models\ResidentialPropertyType;
use App\Models\SellType;
use App\Models\SubscriptionPlan;
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

        $propertyTypes = ['Residential', 'Commercial'];
        foreach ($propertyTypes as $propertyType) {
            PropertyType::create(['name' => $propertyType]);
        }

        $residentialPropertyTypes = ['apartment', 'villa'];
        foreach ($residentialPropertyTypes as $residentialPropertyType) {
            ResidentialPropertyType::create(['name' => $residentialPropertyType]);
        }

        $commercialPropertyTypes = ['office'];
        foreach ($commercialPropertyTypes as $commercialPropertyType) {
            CommercialPropertyType::create(['name' => $commercialPropertyType]);
        }
        
        $sellTypes = ['purchase', 'rent', 'off-plan'];
        foreach ($sellTypes as $sellType) {
            SellType::create(['name' => $sellType]);
        }

        $availabilityStatus = ['Pending', 'Active', 'InActive', 'Rejected'];
        foreach ($availabilityStatus as $status) {
            AvailabilityStatus::create(['name' => $status]);
        }
        
        $onwershipType = ['Freehold'];
        foreach ($onwershipType as $type) {
            OwnershipType::create(['name' => $type]);
        }

        $countries = ['Syria'];
        foreach ($countries as $country) {
            Country::create(['name' => $country]);
        }

        $cities = ['Damascus', 'Aleppo', 'Homs', 'Latakia', 'Hama', 'Daraa', 'Deir ez-Zor', 'Raqqa', 'Tartus', 'Idlib', 'Qamishli', 'Al-Hasakah', 'Palmyra', 'Apamea'];
        foreach ($cities as $city) {
            City::create(['name' => $city, 'country_id' => 1]);
        }
        
        $accountTypes = ['Personal', 'Company'];
        foreach ($accountTypes as $accountType) {
            AccountType::create(['name' => $accountType]);
        }

        $subscriptionPlans = ['Monthly', 'Yearly'];
        foreach ($subscriptionPlans as $subscriptionPlan) {
            SubscriptionPlan::create(['name' => $subscriptionPlan, 'price' => rand(10000, 50000)]);
        }

        Amenity::factory(10)->create();
    }
}
