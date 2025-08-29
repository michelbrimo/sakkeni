<?php

namespace App\Services;

use App\Models\Amenity;
use App\Models\City;
use App\Models\CommercialPropertyType;
use App\Models\Country;
use App\Models\PropertyType;
use App\Models\ResidentialPropertyType;
use App\Models\SellType;
use Illuminate\Support\Facades\Cache;

class SearchDictionaryService
{
    
    public static function get(): array
    {
        return Cache::remember('search.dictionary', 1440, function () {

            $amenities = Amenity::pluck('name')->map(fn($name) => strtolower($name))->all();
            $countries = Country::pluck('name')->map(fn($name) => strtolower($name))->all(); // <-- 2. FETCH COUNTRIES
            $cities = City::pluck('name')->map(fn($name) => strtolower($name))->all();
            $residentialTypes = ResidentialPropertyType::pluck('name')->map(fn($name) => strtolower($name))->all();
            $commercialTypes = CommercialPropertyType::pluck('name')->map(fn($name) => strtolower($name))->all();
            $allPropertyTypes = array_merge($residentialTypes, $commercialTypes);
            $sellTypes = SellType::pluck('name')->map(fn($name) => strtolower($name))->all();
            $residentialTypes = ResidentialPropertyType::pluck('name')->map(fn($name) => strtolower($name))->all();
            $commercialTypes = CommercialPropertyType::pluck('name')->map(fn($name) => strtolower($name))->all();


            $attributes = [
                'area', 'price', 'bedrooms', 'bathrooms', 'balconies'
            ];

            $keywords = [
                'in','between', 'and', 'under', 'over', 'above', 'less', 'more', 'than',
                'with', 'without', 'no', 'furnished', 'unfurnished'
            ];

            $stopwords = [
                'in', 'between', 'and', 'under', 'over', 'above', 'less', 'more', 'than', 'with'
            ];
            
            $sorting = [
                'cheapest', 'priciest', 'newest', 'oldest', 'largest', 'smallest'
            ];

            $synonyms = [
                'apt' => 'apartment',
                'br' => 'bedrooms', 
                'bed' => 'bedrooms', 
                'beds' => 'bedrooms',
                'bedroom' => 'bedrooms', 
                'bath' => 'bathrooms', 
                'baths' => 'bathrooms',
                'bathroom' => 'bathrooms',
                'balcony' => 'balconies', 
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
                'countries' => $countries,
                'property_types' => array_merge($allPropertyTypes, ['home', 'house', 'property']),
                'residential_types' => $residentialTypes,
                'commercial_types' => $commercialTypes,
                'sell_types' => $sellTypes,
                'amenities' => $amenities,
                'sorting' => $sorting,
                'synonyms' => $synonyms,
                'stopwords' => $stopwords
            ];
        });
    }
}