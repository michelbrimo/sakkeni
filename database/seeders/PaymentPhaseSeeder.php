<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentPhase;

class PaymentPhaseSeeder extends Seeder
{
    public function run(): void
    {
        $phases = [
            'down_payment',
            'during_construction',
            'on_completion',
            'post_handover',
            'installment_plan',
        ];

        foreach ($phases as $phase) {
            PaymentPhase::firstOrCreate(['name' => $phase]);
        }
    }
}
