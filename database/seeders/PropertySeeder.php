<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\City;
use App\Models\Amenity;
use App\Models\Location;
use App\Models\Property;
use App\Models\ResidentialProperty;
use App\Models\CommercialProperty;
use App\Models\Apartment;
use App\Models\Villa;
use App\Models\Purchase;
use App\Models\Rent;
use App\Models\OffPlanProperty;
use App\Models\PaymentPhase;
use App\Models\UserClick;
use App\Models\PropertyFavorite;
use App\Models\PropertyImage;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = User::pluck('id');
        $cityIds = City::pluck('id');
        $amenityIds = Amenity::pluck('id');
        $faker = \Faker\Factory::create();

        if ($userIds->isEmpty() || $cityIds->isEmpty() || $amenityIds->isEmpty()) {
            $this->command->error('Users, Cities, or Amenities tables are empty. Please seed them first.');
            return;
        }

        $this->command->info('Seeding 200 properties...');
        $this->command->getOutput()->progressStart(200);

        for ($i = 0; $i < 200; $i++) {
            // --- 1. Create a Location ---
            $location = Location::create([
                'country_id' => 1,
                'city_id' => $cityIds->random(),
                'latitude' => $faker->latitude(32.3, 37.3), 
                'longitude' => $faker->longitude(35.6, 42.4),
                'additional_info' => $faker->streetAddress,
            ]);

            // --- 2. Randomly determine property and sell types ---
            $propertyTypeId = rand(1, 2); 
            $sellTypeId = rand(1, 3);      

            // --- 3. Create the main Property record ---
            $property = Property::create([
                'location_id' => $location->id,
                'owner_id' => $userIds->random(),
                'admin_id' => null, // Can be assigned later
                'area' => $faker->numberBetween(50, 500),
                'bathrooms' => rand(1, 5),
                'balconies' => rand(0, 3),
                'ownership_type_id' => 1, 
                'property_type_id' => $propertyTypeId,
                'sell_type_id' => $sellTypeId,
                'availability_status_id' => 2, 
            ]);

            // Create one placeholder image for the property
            $imageUrls = [
                'https://images.pexels.com/photos/1457842/pexels-photo-1457842.jpeg',
                'https://images.pexels.com/photos/1643383/pexels-photo-1643383.jpeg', 
                'https://images.pexels.com/photos/262048/pexels-photo-262048.jpeg',   
                'https://images.pexels.com/photos/1571463/pexels-photo-1571463.jpeg',
                'https://images.pexels.com/photos/209296/pexels-photo-209296.jpeg',   
                'https://images.pexels.com/photos/534151/pexels-photo-534151.jpeg',   
                'https://images.pexels.com/photos/1080696/pexels-photo-1080696.jpeg', 
                'https://images.pexels.com/photos/106399/pexels-photo-106399.jpeg',   
                'https://images.pexels.com/photos/186077/pexels-photo-186077.jpeg',   
                'https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg',   
                'https://images.pexels.com/photos/1396122/pexels-photo-1396122.jpeg', 
                'https://images.pexels.com/photos/280222/pexels-photo-280222.jpeg',   
                'https://images.pexels.com/photos/259588/pexels-photo-259588.jpeg',   
                'https://images.pexels.com/photos/1438832/pexels-photo-1438832.jpeg', 
                'https://images.pexels.com/photos-271618/pexels-photo-271618.jpeg',   
                'https://images.pexels.com/photos-1457844/pexels-photo-1457844.jpeg', 
            ];

            for ($images = 0; $images < 5; $images++) {
                PropertyImage::create([
                    'property_id' => $property->id,
                    'image_path' => $imageUrls[rand(0, count($imageUrls)-1)] . '?auto=compress&cs=tinysrgb&w=600'
                ]);
            }

            // --- 4. Create sub-type and transaction details based on random types ---
            if ($propertyTypeId == 1) { // Residential
                $residentialTypeId = rand(1, 2); // 1: apartment, 2: villa
                $residentialProperty = ResidentialProperty::create([
                    'property_id' => $property->id,
                    'bedrooms' => rand(1, 6),
                    'residential_property_type_id' => $residentialTypeId,
                ]);

                if ($residentialTypeId == 1) { // Apartment
                    Apartment::create([
                        'residential_property_id' => $residentialProperty->id,
                        'floor' => rand(1, 20),
                        'building_number' => $faker->buildingNumber,
                        'apartment_number' => rand(1, 100),
                    ]);
                } else { // Villa
                    Villa::create([
                        'residential_property_id' => $residentialProperty->id,
                        'floors' => rand(1, 3),
                    ]);
                }
            } else { // Commercial
                CommercialProperty::create([
                    'property_id' => $property->id,
                    'floor' => rand(1, 10),
                    'building_number' => $faker->buildingNumber,
                    'apartment_number' => rand(1, 50), // Office number
                    'commercial_property_type_id' => 1, // Assuming 'office'
                ]);
            }

            // Create transaction details
            if ($sellTypeId == 1) { // Purchase
                Purchase::create([
                    'property_id' => $property->id,
                    'price' => $faker->numberBetween(200, 2000) * 500,
                    'is_furnished' => $faker->boolean,
                ]);
            } elseif ($sellTypeId == 2) { // Rent
                Rent::create([
                    'property_id' => $property->id,
                    'price' => $faker->numberBetween(100, 1000),
                    'lease_period_unit' => $faker->randomElement(['Month', 'Year']),
                    'lease_period_value' => $faker->numberBetween(1, 12),
                    'is_furnished' => $faker->boolean,
                ]);
            } else { // Off-plan
                $overallPayment = $faker->numberBetween(200, 2000) * 500;

                $offPlan = OffPlanProperty::create([
                    'property_id' => $property->id,
                    'delivery_date' => $faker->dateTimeBetween('+1 year', '+3 years'),
                    'overall_payment' => $overallPayment,
                ]);

                $phaseOptions = PaymentPhase::inRandomOrder()->limit(rand(2, 4))->get();

                $totalPercentage = 0;
                $remaining = 100;
                $phasesCount = $phaseOptions->count();

                foreach ($phaseOptions as $index => $phase) {
                    $percentage = ($index === $phasesCount - 1) 
                        ? $remaining 
                        : $faker->randomElement([10, 20, 30]);
                    $remaining -= $percentage;

                    $paymentValue = round(($percentage / 100) * $overallPayment, 2);

                    if (in_array(strtolower($phase->name), ['down payment', 'on handover', 'on completion'])) {
                        $durationValue = null;
                        $durationUnit = null;
                    } else {
                        $durationValue = $faker->randomElement([6, 12, 24, 36]);
                        $durationUnit = $faker->randomElement(['months', 'years']);
                    }

                    $offPlan->paymentPhases()->attach($phase->id, [
                        'payment_percentage' => $percentage,
                        'payment_value' => $paymentValue,
                        'duration_value' => $durationValue,
                        'duration_unit' => $durationUnit,
                    ]);
                }
            }

            $randomAmenities = $amenityIds->random(rand(2, 5));
            $property->amenities()->attach($randomAmenities);
            
            $this->command->getOutput()->progressAdvance();
        }
        $this->command->getOutput()->progressFinish();

        // --- Seed user interactions (clicks and favorites) ---
        $this->command->info("\nSeeding user clicks and favorites...");
        $propertyIds = Property::pluck('id');

        foreach ($userIds as $userId) {
            // Each user clicks on 10 to 30 random properties
            $clickedProperties = $propertyIds->random(rand(10, 30));
            foreach ($clickedProperties as $propertyId) {
                UserClick::create([
                    'user_id' => $userId,
                    'property_id' => $propertyId,
                    'click_count' => rand(1, 5), // Simulate multiple clicks
                ]);
            }

            // Each user favorites 2 to 8 random properties from the ones they clicked
            $favoritedProperties = $clickedProperties->random(rand(2, 8));
            foreach ($favoritedProperties as $propertyId) {
                PropertyFavorite::create([
                    'user_id' => $userId,
                    'property_id' => $propertyId,
                ]);
            }
        }
        $this->command->info('User interactions seeded successfully.');
    }
}
