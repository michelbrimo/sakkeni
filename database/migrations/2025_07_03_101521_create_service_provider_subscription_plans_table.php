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
        Schema::create('service_provider_subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')
            ->constrained('service_providers') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('subscription_plan_id')
            ->constrained('subscription_plans') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_provider_subscription_plans');
    }
};
