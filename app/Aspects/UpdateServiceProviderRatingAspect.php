<?php

namespace App\Aspects;

use App\Repositories\ServiceProviderRepository;
use Exception;
use Illuminate\Support\Facades\Log;

class UpdateServiceProviderRatingAspect
{
    protected $serviceProviderRepository;

    public function __construct()
    {
        $this->serviceProviderRepository = new ServiceProviderRepository();
    }

    public function before($function_name, $data)
    {

    }

    public function after($function_name, $result = null)
    {

        if ($function_name === 'submitReview' && $result) {
            try {
                $review = $result;
                $serviceProvider = $this->serviceProviderRepository->getServiceProviderById($review->service_provider_id);

                if ($serviceProvider) {
                    $currentNumOfRating = $serviceProvider->num_of_rating;
                    $currentTotalRating = $serviceProvider->rate * $currentNumOfRating;
                    
                    $newNumOfRating = $currentNumOfRating + 1;
                    $newRate = ($currentTotalRating + $review->rating) / $newNumOfRating;


                    $this->serviceProviderRepository->updateServiceProvider(
                        $serviceProvider->id,
                        [
                            'rate' => $newRate,
                            'num_of_rating' => $newNumOfRating,
                        ]
                    );
                }
            } catch (Exception $e) {
                Log::error('Could not update service provider rating: ' . $e->getMessage());
            }
        }
    }

    public function exception($function_name)
    {
    }
}