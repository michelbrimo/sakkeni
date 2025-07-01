<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserSearch;
use App\Models\UserPreference;

class FillUserPreferencesFromSearches extends Command
{
    protected $signature = 'preferences:fill-from-searches';
    protected $description = 'Fill user preferences table based on latest user search filters';

    public function handle()
    {
        $this->info("🛠  Processing user searches...");

        $searches = UserSearch::latest('created_at')->get()->groupBy('user_id');

        
        foreach ($searches as $userId => $userSearches) {
            foreach ($userSearches as $search) {
                $filters = $search->filters;

                if (!is_array($filters)) {
                    $filters = json_decode($filters, true);
                }

                if (!$filters) {
                    continue;
                }

                $preferenceData = [
                    'user_id' => $userId,
                    'property_type_id'      => $filters['property_type_id'] ?? null,
                    'sell_type_id'          => $search->sell_type_id ?? null,
                    'min_bedrooms'          => $filters['bedrooms'] ?? null,
                    'max_bedrooms'          => $filters['bedrooms'] ?? null,
                    'min_balconies'         => $filters['balconies'] ?? null,
                    'max_balconies'         => $filters['balconies'] ?? null,
                    'min_area'              => $filters['min_area'] ?? null,
                    'max_area'              => $filters['max_area'] ?? null,
                    'min_bathrooms'         => $filters['bathrooms'] ?? null,
                    'max_bathrooms'         => $filters['bathrooms'] ?? null,
                    'min_price'             => $filters['min_price'] ?? null,
                    'max_price'             => $filters['max_price'] ?? null,
                    'preferred_locations'   => isset($filters['preferred_locations']) ? json_encode($filters['preferred_locations']) : null,
                    'must_amenity'          => isset($filters['must_amenity']) ? json_encode($filters['must_amenity']) : null,
                    'is_furnished'          => $filters['is_furnished'] ?? null,
                    'min_first_pay'         => $filters['min_first_pay'] ?? null,
                    'max_first_pay'         => $filters['max_first_pay'] ?? null,
                    'delivery_date'         => $filters['delivery_date'] ?? null,
                    'lease_period'          => $filters['lease_period'] ?? null,
                ];

                UserPreference::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'sell_type_id' => $search->sell_type_id ?? null,
                        'property_type_id' => $filters['property_type_id'] ?? null,
                    ],
                    $preferenceData
                );
            }
        }

        $this->info("✅ User preferences updated from search filters.");
    }
}
