<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\AccountType;
use App\Models\Admin;
use App\Models\Amenity;
use App\Models\AvailabilityStatus;
use App\Models\City;
use App\Models\CommercialPropertyType;
use App\Models\Country;
use App\Models\Direction;
use App\Models\LeasePeriodUnits;
use App\Models\OwnershipType;
use App\Models\PropertyType;
use App\Models\ResidentialPropertyType;
use App\Models\SellType;
use App\Models\Service;
use App\Models\ServiceCategory;
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
        Amenity::factory(10)->create();
        User::factory(10)->create();
        Admin::factory(1)->create();
        $this->call(PaymentPhaseSeeder::class);


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

        $serviceCategories = ['Fixes and Repairs', 'Cleaning', 'Plumbing', 'Flooring', 'Painting & Finishing', 'Electrical & Power', 'Moving & Transport', 'Garden Upkeep'];
        foreach ($serviceCategories as $serviceCategory) {
            ServiceCategory::create(['name' => $serviceCategory]);
        }

        for($i=0; $i < count($serviceCategories); $i++){
            switch ($serviceCategories[$i]) {
                case 'Fixes and Repairs':
                    $services = ['Carpenter', 'Metalwork'];
                    break;

                case 'Cleaning':
                    $services = ['Deep Cleaning', 'Regular Cleaning', 'Water Tank Cleaning'];
                    break;

                case 'Plumbing':
                    $services = ['Plumber', 'Sewage unclogging'];
                    break;

                case 'Flooring':
                    $services = ['Tiler'];
                    break;
                    
                case 'Painting & Finishing':
                    $services = ['Interior', 'Exterior'];
                    break;

                case 'Electrical & Power':
                    $services = ['Electrician', 'Solar', 'Generators'];
                    break;

                case 'Moving & Transport':
                    $services = ['Moving Service'];
                    break;

                case 'Garden Upkeep':
                    $services = ['Irrigation', 'Pest Control'];
                    break;
                
            }

            foreach ($services as $service) {
                Service::create(['name'=>$service, 'service_category_id' => $i+1]);
            }
        }
        $serviceCategories = ['Fixes and Repairs', 'Cleaning', 'Plumbing', 'Flooring', 'Painting & Finishing', 'Electrical & Power', 'Moving & Transport', 'Garden Upkeep'];
        foreach ($serviceCategories as $serviceCategory) {
            ServiceCategory::create(['name' => $serviceCategory]);
        }

        
        $this->call(PropertySeeder::class);

        $this->call(UserSearchSeeder::class);

        $this->call(ReportReasonSeeder::class);

    }
}
