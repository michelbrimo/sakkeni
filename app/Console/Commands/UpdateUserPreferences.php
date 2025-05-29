<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserClick;
use App\Models\UserSearch;
use App\Models\UserPreference;
use Illuminate\Support\Facades\DB;

class UpdateUserPreferences extends Command
{
    protected $signature = 'preferences:update';
    protected $description = 'Analyze user clicks and searches to update their preferences';

    public function handle()
    {
        $users = DB::table('users')->pluck('id');

        foreach ($users as $userId) {
            $clicks = UserClick::where('user_id', $userId)->with('property')->get();
            $searches = UserSearch::where('user_id', $userId)->get();

            $prefs = $this->extractPreferences($clicks, $searches);

            if ($prefs) {
                UserPreference::updateOrCreate(
                    ['user_id' => $userId],
                    $prefs
                );
            }
        }

        $this->info('User preferences updated.');
    }

    private function extractPreferences($clicks, $searches)
    {
        // Aggregate data (example logic)
        $data = [
            'min_price' => null,
            'max_price' => null,
            'min_area' => null,
            'max_area' => null,
            'min_bedrooms' => null,
            'max_bedrooms' => null,
            'min_bathrooms' => null,
            'max_bathrooms' => null,
            'property_type_id' => null,
            'preferred_locations' => [],
            'must_have_features' => [],
        ];

        // Example: extract from clicked properties
        $prices = $clicks->pluck('property.price')->filter()->all();
        $data['min_price'] = min($prices) ?: null;
        $data['max_price'] = max($prices) ?: null;

        $areas = $clicks->pluck('property.area')->filter()->all();
        $data['min_area'] = min($areas) ?: null;
        $data['max_area'] = max($areas) ?: null;

        $types = $clicks->pluck('property.property_type_id')->filter()->all();
        $data['property_type_id'] = collect($types)->mode()[0] ?? null;

        // Example: extract from searches
        $locations = [];
        $features = [];

        foreach ($searches as $search) {
            if (isset($search->filters['locations'])) {
                $locations = array_merge($locations, (array) $search->filters['locations']);
            }
            if (isset($search->filters['features'])) {
                $features = array_merge($features, (array) $search->filters['features']);
            }
        }

        $data['preferred_locations'] = array_values(array_unique($locations));
        $data['must_have_features'] = array_values(array_unique($features));

        return $data;
    }
}
