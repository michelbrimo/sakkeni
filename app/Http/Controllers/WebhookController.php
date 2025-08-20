<?php
// app/Http/Controllers/WebhookController.php

namespace App\Http\Controllers;

use App\Services\ServiceTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // <-- Import the Log facade
use Stripe\Webhook;
use UnexpectedValueException;

class WebhookController extends Controller
{
    protected $service_transformer;

    public function __construct()
    {
        $this->service_transformer = new ServiceTransformer();
    }

    public function handleStripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->server('HTTP_STRIPE_SIGNATURE');
        $endpoint_secret = config('services.stripe.webhook.secret');
        $event = null;

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (UnexpectedValueException | \Stripe\Exception\SignatureVerificationException $e) {
            // --- ADDED LOGGING ---
            // Log the specific error to help with debugging.
            Log::error('Stripe webhook signature verification failed.', ['exception' => $e->getMessage()]);
            // --- END LOGGING ---
            return response()->json(['error' => 'Webhook error'], 400);
        }

        if ($event->type == 'payment_intent.succeeded') {
            $additionalData = ['payment_intent' => $event->data->object];
            return $this->executeService($this->service_transformer, $request, $additionalData, 'Webhook handled.');
        }

        return response()->json(['status' => 'ignored']);
    }
}
