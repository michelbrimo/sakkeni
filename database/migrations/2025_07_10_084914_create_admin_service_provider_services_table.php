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
        Schema::create('admin_service_provider_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')
            ->constrained('service_provider_services') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('admin_id')
            ->constrained('admins') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->boolean('approve');
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_service_provider_services');
    }
};
