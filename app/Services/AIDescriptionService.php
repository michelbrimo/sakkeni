<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIDescriptionService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";
    }

    public function generateForProperty(Property $property): ?array
    {
        if (!$this->apiKey) {
            Log::warning('Gemini API key is not set. Skipping AI description generation.');
            return null;
        }

        $prompt = $this->buildPrompt($property);

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->apiUrl . '?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => $prompt]]
                        ]
                    ]
                ]);

             if ($response->successful()) {
                $jsonText = $response->json('candidates.0.content.parts.0.text');

                $cleanedJsonText = str_replace(['```json', '```'], '', $jsonText);

                $data = json_decode(trim($cleanedJsonText), true);
                if (json_last_error() === JSON_ERROR_NONE && isset($data['description'])) {
                    return [
                        'description' => $data['description'],
                        'tags' => $data['tags'] ?? null,
                    ];
                } else {
                    Log::error('Gemini response was not valid JSON after cleaning.', ['raw_response' => $jsonText]);
                    return null; 
                }
            } else {
                Log::error('Gemini API request failed.', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Exception while calling Gemini API.', ['error' => $e->getMessage()]);
            return null;
        }
    }

   
    private function buildPrompt(Property $property): string
    {
        $property->load(
            'propertyType', 'sellType', 'location.city', 'amenities',
            'residential', 'commercial', 'purchase', 'rent', 'offPlan'
        );

        $prompt = "Generate two things for the following property:\n";
        $prompt .= "1. A professional and appealing real estate description. The description should be a single, well-structured paragraph, ideally 4 sentences long, highlighting the key selling points.\n";
        $prompt .= "2. A comma-separated list of 5-10 descriptive search tags. These tags should capture the essence of the property (e.g., 'family-friendly', 'natural light', 'modern kitchen', 'city views', 'quiet neighborhood').\n\n";

        $prompt .= "--- Property Details ---\n";
        $prompt .= "Property Type: " . ($property->propertyType->name ?? 'N/A') . "\n";
        $prompt .= "Status: For " . ($property->sellType->name ?? 'N/A') . "\n";
        $prompt .= "Location: In the city of " . ($property->location->city->name ?? 'N/A') . "\n";
        $prompt .= "Area: " . $property->area . " square meters.\n";

        if ($property->residential) {
            $prompt .= "Bedrooms: " . $property->residential->bedrooms . "\n";
        }
        $prompt .= "Bathrooms: " . $property->bathrooms . "\n";

        if ($property->amenities->isNotEmpty()) {
            $prompt .= "Key Amenities: " . $property->amenities->pluck('name')->implode(', ') . ".\n";
        }

        if ($property->purchase) {
            $prompt .= "Price: " . number_format($property->purchase->price) . ".\n";
        } elseif ($property->rent) {
            $prompt .= "Rent: " . number_format($property->rent->price) . " per " . $property->rent->lease_period . ".\n";
        } elseif ($property->offPlan) {
            $prompt .= "This is an off-plan property with a delivery date of " . $property->offPlan->delivery_date . ". ";
            $prompt .= "Total payment is " . number_format($property->offPlan->overall_payment) . ".\n";
        }

        $prompt .= "--- End of Details ---\n\n";
        $prompt .= "Provide the output in this exact JSON format: {\"description\": \"...\", \"tags\": \"tag1, tag2, ...\"}";

        return $prompt;
    }
   
}
