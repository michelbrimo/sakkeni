<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class TriggerModelRetraining extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recommendations:retrain-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Triggers the Python recommendation service to retrain its models and recalculate similarities.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('▶️  Sending request to Python microservice to start model retraining...');

        // Get the microservice URL from your configuration.
        // It's best practice to store this in your .env file.
        $serviceUrl = config('services.recommendation_service.url');

        if (!$serviceUrl) {
            $this->error('❌ Error: Recommendation service URL is not configured.');
            $this->comment('Please add RECOMMENDATION_SERVICE_URL to your .env file.');
            return 1; // Return a non-zero exit code for failure
        }

        // The full endpoint URL
        $endpoint = $serviceUrl . '/api/v1/retrain_models';

        // Make a POST request to the Python service endpoint
        $response = Http::timeout(300) // Set a long timeout (e.g., 5 minutes) as training can take time
                         ->post($endpoint);

        if ($response->successful()) {
            $this->info('✅ Success! The recommendation service has started the retraining process.');
            $this->line('   Response: ' . $response->json('message'));
        } else {
            $this->error('❌ Failed to trigger model retraining.');
            $this->error('   Status Code: ' . $response->status());
            if ($response->json()) {
                $this->error('   Error Message: ' . $response->json('detail'));
            }
        }

        return 0; // Return 0 for success
    }
}
