<?php

namespace App\Services;

use App\Models\Amenity;
use App\Models\City;
use App\Models\PropertyType;
use App\Models\SellType;
use Illuminate\Support\Facades\Cache;

class SearchDictionaryService
{
    /**
     * Build and return a comprehensive search dictionary, pulling data
     * from the database and caching it for performance.
     */
    public static function get(): array
    {
        return Cache::remember('search.dictionary', 1440, function () {

            $amenities = Amenity::pluck('name')->map(fn($name) => strtolower($name))->all();
            $cities = City::pluck('name')->map(fn($name) => strtolower($name))->all();
            $propertyTypes = PropertyType::pluck('name')->map(fn($name) => strtolower($name))->all();
            $sellTypes = SellType::pluck('name')->map(fn($name) => strtolower($name))->all();


            $attributes = [
                'area', 'price', 'bedrooms', 'bedroom', 'bathrooms', 'bathroom', 'balconies', 'balcony'
            ];

            $keywords = [
                'between', 'and', 'under', 'over', 'above', 'less', 'more', 'than',
                'with', 'without', 'no', 'furnished', 'unfurnished'
            ];
            
            $sorting = [
                'cheapest', 'priciest', 'newest', 'oldest', 'largest', 'smallest'
            ];

            $synonyms = [
                'apt' => 'apartment',
                'br' => 'bedroom',
                'beds' => 'bedrooms',
                'baths' => 'bathrooms',
                'for sale' => 'purchase',
                'for rent' => 'rent',
                'buy' => 'purchase',
                'house' => 'villa',
                'home' => 'villa',
                'cheapest' => 'price',
                'priciest' => 'price',
                'newest' => 'created_at',
                'oldest' => 'created_at',
                'largest' => 'area',
                'smallest' => 'area',
            ];


            return [
                'attributes' => $attributes,
                'keywords' => $keywords,
                'locations' => $cities,
                'property_types' => array_merge($propertyTypes, ['home', 'house', 'property']),
                'sell_types' => $sellTypes,
                'amenities' => $amenities,
                'sorting' => $sorting,
                'synonyms' => $synonyms,
            ];
        });
    }
}