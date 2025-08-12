<?php

namespace Database\Seeders;

use App\Models\ReportReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propertyReasons = [
            'Incorrect location or address',
            'Inaccurate price or details',
            'Misleading photos',
            'Property is no longer available',
        ];

        $serviceProviderReasons = [
            'Unresponsive or unprofessional',
            'Poor quality of work',
            'Misleading service description',
        ];

        $generalReasons = [
            'It\'s a scam or fraudulent',
            'Inappropriate or offensive content',
            'Spam',
            'Other',
        ];

        foreach ($propertyReasons as $reason) {
            ReportReason::create([
                'reason' => $reason,
                'type' => 'property',
            ]);
        }

        foreach ($serviceProviderReasons as $reason) {
            ReportReason::create([
                'reason' => $reason,
                'type' => 'service_provider',
            ]);
        }

        foreach ($generalReasons as $reason) {
            ReportReason::create([
                'reason' => $reason,
                'type' => 'general',
            ]);
        }
    }
}
