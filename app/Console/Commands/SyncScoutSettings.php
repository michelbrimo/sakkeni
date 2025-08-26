<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MeiliSearch\Client as MeiliSearchClient;

class SyncScoutSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:sync-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually syncs the Meilisearch index settings from the scout config file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Attempting to sync Meilisearch settings...');

        try {
            // Connect to Meilisearch
            $client = new MeiliSearchClient(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

            // Define your index name (check your model if it's different)
            $indexName = 'properties';
            $index = $client->index($indexName);

            // Get the settings from your config/scout.php file
            $settings = config('scout.meilisearch.index-settings.' . $indexName);

            if (!$settings) {
                $this->error("No settings found for index '{$indexName}' in config/scout.php");
                return 1;
            }

            // Update Filterable Attributes
            $filterable = $settings['filterableAttributes'] ?? [];
            if (!empty($filterable)) {
                $index->updateFilterableAttributes($filterable);
                $this->info('Successfully updated filterable attributes.');
                $this->line(implode(', ', $filterable));
            } else {
                $this->warn('No filterable attributes found in config to sync.');
            }

            // Update Sortable Attributes
            $sortable = $settings['sortableAttributes'] ?? [];
            if (!empty($sortable)) {
                $index->updateSortableAttributes($sortable);
                $this->info('Successfully updated sortable attributes.');
                $this->line(implode(', ', $sortable));
            } else {
                $this->warn('No sortable attributes found in config to sync.');
            }

            $this->info('Settings sync complete!');

        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}