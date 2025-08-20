<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Models\ServiceActivity;

class PaymentRepository
{
    public function findServiceActivityById(int $id): ?ServiceActivity
    {
        return ServiceActivity::find($id);
    }

    public function updateServiceActivityStatus(ServiceActivity $serviceActivity, string $status): bool
    {
        return $serviceActivity->update(['status' => $status]);
    }

    public function createPaymentRecord(array $data): Payment
    {
        return Payment::create($data);
    }
}
