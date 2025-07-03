<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PropertyPopularityMetrics; 
use App\Models\Property; 
use App\Models\UserClick; 
use App\Models\PropertyFavorite; 

class UpdatePropertyPopularityMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:update-property-popularity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates property popularity metrics based on user interactions (clicks and favorites).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔄 Updating property popularity metrics...");

        Property::chunk(500, function ($properties) {
            foreach ($properties as $property) {
                $totalViews = UserClick::where('property_id', $property->id)->sum('click_count');

                $totalFavorites = PropertyFavorite::where('property_id', $property->id)->count();

                PropertyPopularityMetrics::updateOrCreate(
                    ['property_id' => $property->id], 
                    [
                        'total_views' => $totalViews,
                        'total_favorites' => $totalFavorites,
                        // 'total_contacts'  removed
                        // 'view_to_contact_ratio'  removed
                        'last_updated' => now() 
                    ]
                );
            }
        });

        $this->info("✅ Property popularity metrics updated.");
    }
}