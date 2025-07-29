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

    public function generateForProperty(Property $property): ?string
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
                return $response->json('candidates.0.content.parts.0.text');
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

        $prompt = " Generate a professional and appealing real estate description for the following property.
                    Be creative and highlight the key selling points.  
                    The description should be a single, 
                    The summary must be minimal and short, ideally 4 sentences long, 
                    in a well structured paragraph\n\n";

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
        $prompt .= "Based on these details, write the description.";

        return $prompt;
    }
    // private function buildPrompt(Property $property): string
    // {
    //     $property->load(
    //         'propertyType', 'sellType', 'location.city', 'amenities',
    //         'residential', 'commercial', 'purchase', 'rent', 'offPlan'
    //     );

    //     $prompt = "Generate a professional real estate description using EXACTLY this 4-sentence structure:\n\n";
    //     $prompt .= "1. [Creative introduction mentioning property type and location]\n";
    //     $prompt .= "2. [Key features: bedrooms/bathrooms/area with positive adjectives]\n";
    //     $prompt .= "3. [Amenities and special features as comma-separated list]\n";
    //     $prompt .= "4. [Pricing/availability call-to-action]\n\n";
    //     $prompt .= "--- STRICT RULES ---\n";
    //     $prompt .= "- Maintain exactly 4 sentences in this order\n";
    //     $prompt .= "- End each sentence with a period\n";
    //     $prompt .= "- Keep amenities list to 3 key items max\n";
    //     $prompt .= "- Use professional real estate language\n\n";
    //     $prompt .= "--- Property Details ---\n";
    //     $prompt .= "Property Type: " . ($property->propertyType->name ?? 'N/A') . "\n";
    //     $prompt .= "Status: For " . ($property->sellType->name ?? 'N/A') . "\n";
    //     $prompt .= "Location: In the city of " . ($property->location->city->name ?? 'N/A') . "\n";
    //     $prompt .= "Area: " . $property->area . " square meters\n";

    //     if ($property->residential) {
    //         $prompt .= "Bedrooms: " . $property->residential->bedrooms . "\n";
    //     }
    //     $prompt .= "Bathrooms: " . $property->bathrooms . "\n";

    //     if ($property->amenities->isNotEmpty()) {
    //         $prompt .= "Key Amenities: " . $property->amenities->pluck('name')->implode(', ') . "\n";
    //     }

    //     if ($property->purchase) {
    //         $prompt .= "Price: " . number_format($property->purchase->price) . "\n";
    //     } elseif ($property->rent) {
    //         $prompt .= "Rent: " . number_format($property->rent->price) . " per " . $property->rent->lease_period . "\n";
    //     } elseif ($property->offPlan) {
    //         $prompt .= "Delivery Date: " . $property->offPlan->delivery_date . "\n";
    //         $prompt .= "Total Payment: " . number_format($property->offPlan->overall_payment) . "\n";
    //     }

    //     $prompt .= "--- End of Details ---\n\n";
    //     $prompt .= "Generate the description following the 4-sentence template exactly.";

    //     return $prompt;
    // }
}
