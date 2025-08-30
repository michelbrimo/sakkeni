<?php

namespace App\Services;

use App\Models\ServiceActivity;
use App\Models\SubscriptionPlan;
use App\Repositories\PaymentRepository;
use App\Repositories\ServiceProviderRepository;
use Exception;
use Illuminate\Support\Facades\Log; 
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Transfer;

class PaymentService
{
    protected $paymentRepository;

    public function __construct()
    {
        $this->paymentRepository = new PaymentRepository();
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent($data)
    {
        $user = $data['user'];
        $serviceActivity = $data['service_activity'];

        if ($user->id !== $serviceActivity->user_id) {
            throw new Exception('Unauthorized', 403);
        }

        $paymentIntent = PaymentIntent::create([
            'amount' => $serviceActivity->cost * 100, // Stripe expects amount in cents
            'currency' => 'usd',
            'metadata' => [
                'service_activity_id' => $serviceActivity->id,
                'user_id' => $user->id,
            ],
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ]);

        return ['clientSecret' => $paymentIntent->client_secret];
    }

    

    public function createTransfer(ServiceActivity $serviceActivity)
    {
        $serviceProviderUser = $serviceActivity->serviceProvider->user;

        if (!$serviceProviderUser->stripe_account_id) {
            throw new Exception('Service provider has not connected a payout account.');
        }

        $payment = $serviceActivity->payment;
        if (!$payment) {
            throw new Exception('Cannot create transfer. Original payment record not found.');
        }

        $platformFee = $serviceActivity->cost * 0.10; 
        $payoutAmount = $serviceActivity->cost - $platformFee;

        $transfer = Transfer::create([
            'amount' => $payoutAmount * 100,
            'currency' => 'usd',
            'destination' => $serviceProviderUser->stripe_account_id,
            'transfer_group' => 'SERVICE_ACTIVITY_' . $serviceActivity->id,
            'source_transaction' => $payment->payment_gateway_transaction_id,
        ]);


        return $transfer;
    }

    public function createSubscriptionPaymentIntent($data)
    {
        $user = $data['user'];
        $serviceProvider = $user->serviceProvider;

        if (!$serviceProvider || $serviceProvider->status !== 'pending_payment') {
            throw new Exception('This account is not eligible for payment.', 403);
        }
        
        $pendingPlanId = $serviceProvider->pending_subscription_plan_id;
        if (!$pendingPlanId) {
            throw new Exception('Could not find a selected subscription plan.', 422);
        }
        
        $plan = SubscriptionPlan::find($pendingPlanId);

        $paymentIntent = PaymentIntent::create([
            'amount' => $plan->price * 100,
            'currency' => 'usd',
            'metadata' => [
                'type' => 'subscription_payment', // Critical for webhook handling
                'user_id' => $user->id,
                'service_provider_id' => $serviceProvider->id,
                'subscription_plan_id' => $plan->id,
            ],
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ]);

        return ['clientSecret' => $paymentIntent->client_secret];
    }


    public function handleWebhook($data)
    {
        $paymentIntent = $data['payment_intent'];
        $metadata = $paymentIntent->metadata;

        if (isset($metadata->type) && $metadata->type === 'subscription_payment') {
            $this->handleSubscriptionPayment($paymentIntent);
        } else if (isset($metadata->service_activity_id)) {
            $this->handleServiceActivityPayment($paymentIntent);
        } else {
            Log::warning('Webhook ignored: Missing identifying metadata.', ['pi_id' => $paymentIntent->id]);
        }
    }

    public function handleSubscriptionPayment(PaymentIntent $paymentIntent)
    {
        $metadata = $paymentIntent->metadata;
        $serviceProviderId = $metadata->service_provider_id;
        $planId = $metadata->subscription_plan_id;

        $providerRepository = new ServiceProviderRepository();
        $serviceProvider = $providerRepository->getServiceProviderById($serviceProviderId);

        if ($serviceProvider && $serviceProvider->status === 'pending_payment') {
            
            $providerRepository->updateServiceProvider($serviceProviderId, [
                'status' => 'active',
                'pending_subscription_plan_id' => null 
            ]);

            $plan = SubscriptionPlan::find($planId);
            $endDate = null;
            if (strtolower($plan->name) === 'monthly') {
                $endDate = now()->addMonth();
            } else if (strtolower($plan->name) === 'yearly') {
                $endDate = now()->addYear();
            }

            $providerRepository->createServiceProviderSubscriptionPlan([
                'service_provider_id' => $serviceProviderId,
                'subscription_plan_id' => $planId,
                'start_date' => now(),
                'end_date' => $endDate,
            ]);
            
            Log::info('Subscription activated for provider ID: ' . $serviceProviderId);

        } else {
            Log::warning('Webhook skipped for subscription payment.', [
                'pi_id' => $paymentIntent->id,
                'reason' => 'Provider not found or not in pending_payment status.'
            ]);
        }
    }

    public function handleServiceActivityPayment(PaymentIntent $paymentIntent)
    {
        if (!isset($paymentIntent->metadata->service_activity_id)) {
            Log::warning('Webhook ignored: Missing service_activity_id in metadata.', ['pi_id' => $paymentIntent->id]); // <-- ADDED LOG
            return;
        }

        $serviceActivityId = $paymentIntent->metadata->service_activity_id;
        Log::info('Processing webhook for Service Activity ID: ' . $serviceActivityId); // <-- ADDED LOG

        $serviceActivity = $this->paymentRepository->findServiceActivityById($serviceActivityId);

        if ($serviceActivity && $serviceActivity->status !== 'In Progress') {
            Log::info('Service Activity found. Updating status to In Progress.'); // <-- ADDED LOG
            $this->paymentRepository->updateServiceActivityStatus($serviceActivity, 'In Progress');

            Log::info('Creating payment record in database.'); // <-- ADDED LOG
            $platformFeeRate = config('services.platform_fee_percentage', 0.10);
            $this->paymentRepository->createPaymentRecord([
                'service_activity_id' => $serviceActivity->id,
                'payment_gateway_transaction_id' => $paymentIntent->latest_charge,
                'amount' => $paymentIntent->amount / 100,
                'platform_fee' => ($paymentIntent->amount / 100) * $platformFeeRate,
                'status' => 'succeeded',
            ]);
            Log::info('Webhook processing complete for Service Activity ID: ' . $serviceActivityId); // <-- ADDED LOG
        } else {
            // This will tell us if the job was not found or was already processed
            Log::warning('Webhook skipped for Service Activity ID: ' . $serviceActivityId, [
                'found' => !!$serviceActivity,
                'status' => $serviceActivity ? $serviceActivity->status : 'not_found'
            ]); // <-- ADDED LOG
        }
    }
}
