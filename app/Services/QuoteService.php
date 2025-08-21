<?php

namespace App\Services;

use App\Repositories\ConversationRepository;
use App\Repositories\QuoteRepository;
use Exception;

class QuoteService
{
    protected $quoteRepository;
    protected $conversationRepository;

    public function __construct()
    {
        $this->quoteRepository = new QuoteRepository();
        $this->conversationRepository = new ConversationRepository();
    }

    public function requestQuote($data)
    {
        $quote = $this->quoteRepository->createQuote([
            'user_id' => $data['user']->id,
            'service_provider_id' => $data['service_provider_id'],
            'service_id' => $data['service_id'],
            'job_description' => $data['job_description'],
        ]);

        $this->conversationRepository->createConversation([
            'user_id' => $quote->user_id,
            'service_provider_id' => $quote->service_provider_id,
            'quote_id' => $quote->id,
        ]);

        return $quote;
    }

    public function viewProviderQuotes($data)
    {
        $user = $data['user'];

        if (!$user->serviceProvider) {
            throw new Exception('User is not a service provider.', 403);
        }

        return $this->quoteRepository->getProviderQuotes($user->serviceProvider->id);
    }

    public function submitQuote($data)
    {
        $user = $data['user'];
        $quote = $data['quote'];

        // Authorization logic
        if (!$user->serviceProvider || $user->serviceProvider->id !== $quote->service_provider_id) {
            throw new Exception('Unauthorized', 403);
        }

        $updated = $this->quoteRepository->updateQuote($quote, [
            'amount' => $data['amount'],
            'scope_of_work' => $data['scope_of_work'],
            'start_date' => $data['start_date'], 
            'status' => 'Pending User Acceptance',
        ]);
        return $updated;
    }

    public function declineUserQuote($data)
    {
        $user = $data['user'];
        $quote = $data['quote'];

        if (!$user->serviceProvider || $user->serviceProvider->id !== $quote->service_provider_id) {
            throw new Exception('Unauthorized', 403);
        }

        $updated = $this->quoteRepository->updateQuote($quote, [
            'status' => 'Declined',
        ]);

        return $updated;
    }

    public function acceptQuote($data)
    {
        $user = $data['user'];
        $quote = $data['quote'];

        if ($user->id !== $quote->user_id) {
            throw new Exception('Unauthorized', 403);
        }

        $this->quoteRepository->updateQuote($quote, ['status' => 'Accepted']);
        return $this->quoteRepository->createServiceActivityFromQuote($quote);
    }

    public function declineQuote($data)
    {
        $user = $data['user'];
        $quote = $data['quote'];

        if ($user->id !== $quote->user_id) {
            throw new Exception('Unauthorized', 403);
        }
        return $this->quoteRepository->updateQuote($quote, ['status' => 'Declined']); 
    }

    public function updateQuoteRequest($data)
    {
        $user = $data['user'];
        $quote = $data['quote'];

        if ($user->id !== $quote->user_id) {
            throw new Exception('Unauthorized', 403);
        }

        if ($quote->status !== 'Pending Provider Response') {
            throw new Exception('This quote can no longer be edited.', 422);
        }

        return $this->quoteRepository->updateQuote($quote, [
            'job_description' => $data['job_description'],
        ]);
    }
}
