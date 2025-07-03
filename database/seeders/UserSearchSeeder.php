<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserSearch;
use App\Models\PropertyType;
use App\Models\SellType;
use App\Models\City;
use App\Models\Amenity;

class UserSearchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Seeding user searches...');

        $userIds = User::pluck('id');
        $propertyTypeIds = PropertyType::pluck('id');
        $sellTypeIds = SellType::pluck('id');
        $cityIds = City::pluck('id');
        $amenityIds = Amenity::pluck('id');
        $faker = \Faker\Factory::create();

        if ($userIds->isEmpty()) {
            $this->command->error('No users found. Please seed users first.');
            return;
        }

        foreach ($userIds as $userId) {
            // Each user makes between 2 and 5 searches
            for ($i = 0; $i < rand(2, 5); $i++) {
                $sellTypeId = $sellTypeIds->random();
                $propertyTypeId = $propertyTypeIds->random();

                // Build a realistic filter object
                $filters = [
                    'property_type_id' => $propertyTypeId,
                    'min_price' => $faker->numberBetween(50000, 200000),
                    'max_price' => $faker->numberBetween(250000, 1000000),
                    'min_area' => $faker->numberBetween(50, 100),
                    'max_area' => $faker->numberBetween(120, 500),
                    'bedrooms' => rand(1, 5),
                    'bathrooms' => rand(1, 4),
                    'preferred_locations' => $cityIds->random(rand(1,3))->toArray(),
                    'must_amenity' => $amenityIds->random(rand(1,2))->toArray(),
                    'is_furnished' => $faker->boolean,
                ];

                UserSearch::create([
                    'user_id' => $userId,
                    'sell_type_id' => $sellTypeId,
                    'filters' => json_encode($filters),
                ]);
            }
        }

        $this->command->info('User searches seeded successfully.');
    }
}
