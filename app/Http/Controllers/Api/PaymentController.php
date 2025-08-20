<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceActivity;
use App\Services\ServiceTransformer;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $service_transformer;

    public function __construct()
    {
        $this->service_transformer = new ServiceTransformer();
    }

    public function createPaymentIntent(Request $request, ServiceActivity $serviceActivity)
    {
        $additionalData = [
            'user' => $request->user(),
            'service_activity' => $serviceActivity
        ];
        return $this->executeService($this->service_transformer, $request, $additionalData, 'Payment intent created successfully.');
    }
}