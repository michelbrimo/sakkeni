<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.recommendation_service.url');
    }

    public function getRecommendedIds(int $userId, int $limit = 50): array
    {
        if (!$this->baseUrl) {
            Log::error('Recommendation service URL is not configured.');
            return [];
        }

        $endpoint = $this->baseUrl . '/api/v1/recommendations/' . $userId;

        try {
            $response = Http::timeout(10)->post($endpoint, [
                'limit' => $limit
            ]);

            if ($response->successful()) {
                return $response->json('recommendations', []);
            } else {
                Log::error('Failed to fetch recommendations from Python service.', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [];
            }
        } catch (\Exception $e) {
            Log::error('Could not connect to recommendation service.', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

}
