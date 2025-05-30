<?php

namespace Tests\Feature;

use App\Enums\CommercialPropertyType;
use App\Enums\Exposure;
use App\Enums\PhysicalStatusType;
use App\Enums\PropertyType;
use App\Enums\ResidentialPropertyType;
use App\Enums\SellType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Country;
use App\Models\City;
use App\Models\Amenity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PropertyTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $country;
    protected $city;
    protected $amenity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh --seed');

        Storage::fake('public');

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->country = Country::factory()->create();
        $this->city = City::factory()->create(['country_id' => $this->country->id]);
        $this->amenity = Amenity::factory()->create();
    }

    public function test_add_ready_residential_property_for_purchase_successfully()
    {
        $propertyData = [
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'latitude' => 12,
            'longitude' => 13,
            'additional_info' => 'Additional information',

            'area' => 75,
            'bathrooms' => 1,
            'balconies' => 1,
            'ownership_type' => 'Freehold',

            'images' => [
                UploadedFile::fake()->image('property1.jpg'),
                UploadedFile::fake()->image('property2.png'),
            ],
            'exposure' => [Exposure::SOUTH_WEST],
            'amenities' => [$this->amenity->id],

            'is_furnished' => 1,

            'property_type_id' => PropertyType::RESIDENTIAL,
            'bedrooms' => 1,

            'sell_type_id' => SellType::PURCHASE,
            'price' => 10000,

            'residential_property_type_id' => ResidentialPropertyType::APARTMENT,
            'floor' => 5,
            'building_number' => 53,
            'apartment_number' => 5,

           
        ];

        $response = $this->postJson(route('Property.addProperty'), $propertyData);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $this->assertDatabaseHas('properties', [
            'owner_id' => $this->user->id,
            'area' => $propertyData['area'],
        ]);

        $this->assertDatabaseHas('purchases', [
            'price' => $propertyData['price'],
        ]);

        $this->assertDatabaseHas('residential_properties', [
            'bedrooms' => $propertyData['bedrooms'],
        ]);

        $this->assertDatabaseHas('apartments', [
            'floor' => $propertyData['floor'],
        ]);
    }

    public function test_add_ready_residential_property_for_rent_successfully()
    {
        $propertyData = [
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'latitude' => 12,
            'longitude' => 13,
            'additional_info' => 'Additional information',
            
            'area' => 600,
            'bathrooms' => 4,
            'balconies' => 2,
            'ownership_type' => 'Freehold',

            'images' => [
                UploadedFile::fake()->image('property1.jpg'),
                UploadedFile::fake()->image('property2.png'),
            ],
            'exposure' => [Exposure::EAST, Exposure::SOUTH],
            'amenities' => [$this->amenity->id],

            'is_furnished' => 1,

            'property_type_id' => PropertyType::RESIDENTIAL,
            'bedrooms' => 3,

            'sell_type_id' => SellType::RENT,
            'price' => 10000,
            'lease_period' => 'Annual',
            'payment_plan' => 'full pay at once',

            'residential_property_type_id' => ResidentialPropertyType::VILLA,
            'floors' => 2,
        ];

        $response = $this->postJson(route('Property.addProperty'), $propertyData);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $this->assertDatabaseHas('properties', [
            'owner_id' => $this->user->id,
            'area' => $propertyData['area'],
        ]);

        $this->assertDatabaseHas('rents', [
            'price' => $propertyData['price'],
        ]);

        $this->assertDatabaseHas('residential_properties', [
            'bedrooms' => $propertyData['bedrooms'],
        ]);

        $this->assertDatabaseHas('villas', [
            'floors' => $propertyData['floors'],
        ]);
    }

    public function test_add_offPlan_commercial_property_successfully()
    {
        $propertyData = [
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'latitude' => 12,
            'longitude' => 13,
            'additional_info' => 'Additional information',

            'area' => 75,
            'bathrooms' => 1,
            'balconies' => 1,
            'ownership_type' => 'Freehold',

            'images' => [
                UploadedFile::fake()->image('property1.jpg'),
                UploadedFile::fake()->image('property2.png'),
            ],
            'exposure' => [Exposure::NORTH],
            'amenities' => [$this->amenity->id],
            
            'sell_type_id' => SellType::OFF_PLAN,
            'delivery_date' => '2025-05-25',
            'first_pay' => 100,
            'pay_plan' => json_encode([
                "month 1" => 500,
                "month 2" => 300,
                "month 3" => 100
            ]),
            'overall_payment' => 1000,
            
            'property_type_id' => PropertyType::COMMERCIAL,
            'commercial_property_type_id' => CommercialPropertyType::OFFICE,
            'building_number' => 25,
            'apartment_number' => 3,
            'floor' => 3,
        ];

        $response = $this->postJson(route('Property.addProperty'), $propertyData);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $this->assertDatabaseHas('properties', [
            'owner_id' => $this->user->id,
            'area' => $propertyData['area'],
        ]);

        $this->assertDatabaseHas('off_plan_properties', [
            'first_pay' => $propertyData['first_pay'],
        ]);

        $this->assertDatabaseHas('commercial_properties', [
            'floor' => $propertyData['floor'],
        ]);
    }

    public function test_missing_attributes_on_adding_properties()
    {
        $propertyData = [
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'latitude' => 12,
            'longitude' => 13,
            'additional_info' => 'Additional information',

            // 'area' => 75,
            // 'bathrooms' => 1,
            'balconies' => 1,
            'ownership_type' => 'Freehold',

            'images' => [
                UploadedFile::fake()->image('property1.jpg'),
                UploadedFile::fake()->image('property2.png'),
            ],
            'exposure' => [Exposure::NORTH],
            'amenities' => [$this->amenity->id],
            
            'sell_type_id' => SellType::OFF_PLAN,
            'delivery_date' => '2025-05-25',
            'first_pay' => 100,
            'pay_plan' => json_encode([
                "month 1" => 500,
                "month 2" => 300,
                "month 3" => 100
            ]),
            'overall_payment' => 1000,
            
            'property_type_id' => PropertyType::COMMERCIAL,
            'commercial_property_type_id' => CommercialPropertyType::OFFICE,
            'building_number' => 25,
            'apartment_number' => 3,
            'floor' => 3,
        ];

        $response = $this->postJson(route('Property.addProperty'), $propertyData);

        $response->assertStatus(422);

        $this->assertDatabaseEmpty('properties');
        $this->assertDatabaseEmpty('off_plan_properties');
        $this->assertDatabaseEmpty('commercial_properties');
    }
}
