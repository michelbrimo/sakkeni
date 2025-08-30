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
use App\Models\OwnershipType;
use App\Models\PropertyType;
use App\Models\ResidentialPropertyType;
use App\Models\SellType;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        Admin::factory(1)->create();
        $this->call(PaymentPhaseSeeder::class);


        $directions = ['North', 'South', 'East', 'West'];
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

        $availabilityStatus = ['Pending', 'Active', 'InActive', 'Rejected', 'Sold'];
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

        $cities = ['Damascus', 'Rif-Damascus', 'Aleppo', 'Homs', 'Hama', 'Al-Hasakah', 'Latakia', 'Tartus', 'Daraa', 'Deir ez-Zor', 'Raqqa',  'Idlib', 'Qunaitra', 'Sweida'];
        foreach ($cities as $city) {
            City::create(['name' => $city, 'country_id' => 1]);
        }
        
        $accountTypes = ['Personal', 'Company'];
        foreach ($accountTypes as $accountType) {
            AccountType::create(['name' => $accountType]);
        }

        SubscriptionPlan::create(['name' => 'Monthly', 'price' => 10]);
        SubscriptionPlan::create(['name' => 'Yearly', 'price' => 99]);

        
        $propertyTypes = ['Residential', 'Commercial'];
        foreach ($propertyTypes as $propertyType) {
            PropertyType::create(['name' => $propertyType]);
        }
        
        $amenities = [
            'Garage', 'Elevator', 'gym', 'pool',
            'Air Conditioning', 'Heating', 
            'Solar Panels', 'Electric Car Charging Stations', 'Lithium Batteries', 
            'Swimming Pool', 'Rooftop Terrace', 'Home Theater', 'Wine Cellar', 'Spa', 'BBQ', 'Playground', 'Pet-Friendly',
            'Security',
            'Nearby Schools', 'Nearby Shopping Centers', 'Nearby Restaurants', 'Nearby Parks', 'Nearby Hospitals',
            'Wheelchair Ramps',
            'Smart Home', 'High-Speed Internet',
        ];
        foreach ($amenities as $amenity) {
            Amenity::create(['name' => $amenity]);
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
        
        $this->call(PropertySeeder::class);
        $this->call(UserSearchSeeder::class);
        $this->call(ReportReasonSeeder::class);
    }
}
