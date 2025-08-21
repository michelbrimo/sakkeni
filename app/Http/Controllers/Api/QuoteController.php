<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\ServiceTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuoteController extends Controller
{
    protected $service_transformer;

    public function __construct()
    {
        $this->service_transformer = new ServiceTransformer();
    }

    public function requestQuote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_provider_id' => 'required|exists:service_providers,id',
            'service_id' => 'required|exists:services,id',
            'job_description' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 422);
        }

        $additionalData = ['user' => $request->user()];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Quote requested successfully.');
    }

    public function updateQuoteRequest(Request $request, Quote $quote)
    {
        $validator = Validator::make($request->all(), [
            'job_description' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 422);
        }

        $additionalData = [
            'user' => $request->user(),
            'quote' => $quote
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Quote request updated successfully.');
    }


    public function viewProviderQuotes(Request $request)
    {
        $additionalData = ['user' => $request->user()];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Provider quotes fetched successfully.');
    }

    public function submitQuote(Request $request, Quote $quote)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'scope_of_work' => 'required|string|max:2000',
            'start_date' => 'required|date|after_or_equal:today', 
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 422);
        }

        $additionalData = [
            'user' => $request->user(),
            'quote' => $quote
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Quote submitted successfully.');
    }

    public function acceptQuote(Request $request, Quote $quote)
    {
        $additionalData = [
            'user' => $request->user(),
            'quote' => $quote
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Quote accepted. Please proceed to payment.');
    }

    public function declineQuote(Request $request, Quote $quote)
    {
        $additionalData = [
            'user' => $request->user(),
            'quote' => $quote
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Quote Declined.');
    }

    public function declineUserQuote(Request $request, Quote $quote)
    {
        $additionalData = [
            'user' => $request->user(),
            'quote' => $quote
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Quote submitted successfully.');
    }

    
}