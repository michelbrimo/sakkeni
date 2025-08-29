<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_activity_id')->constrained('service_activities')->cascadeOnDelete();
            $table->string('payment_gateway_transaction_id')->unique();
            $table->decimal('amount', 8, 2);
            $table->decimal('platform_fee', 8, 2);
            $table->string('status');// (e.g., 'succeeded', 'failed', 'refunded').
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
