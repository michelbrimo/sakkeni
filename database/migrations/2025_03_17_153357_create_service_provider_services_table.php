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
        Schema::create('service_provider_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')
            ->constrained('service_providers') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('service_id')
            ->constrained('services') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('availability_status_id')
            ->constrained('availability_statuses')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_provider_services');
    }
};
