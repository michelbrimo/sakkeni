<?php
// app/Repositories/QuoteRepository.php

namespace App\Repositories;

use App\Models\Quote;
use App\Models\ServiceActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;


class QuoteRepository
{
    public function createQuote(array $data): Quote
    {
        return Quote::create($data);
    }

    public function updateQuote(Quote $quote, array $data): bool
    {
        return $quote->update($data);
    }

    public function getProviderQuotes(int $serviceProviderId): Collection
    {
        return Quote::where('service_provider_id', $serviceProviderId)
            ->with('user', 'service') 
            ->latest()
            ->get();
    }

    public function createServiceActivityFromQuote(Quote $quote): ServiceActivity
    {
        return ServiceActivity::create([
            'user_id' => $quote->user_id,
            'service_provider_id' => $quote->service_provider_id,
            'quote_id' => $quote->id,
            'cost' => $quote->amount,
            'start_date' => $quote->start_date, // Placeholder
            'status' => 'Awaiting Payment',
        ]);
    }

    public function findUserForQuote(Quote $quote): ?User
    {
        return User::find($quote->user_id);
    }
}
