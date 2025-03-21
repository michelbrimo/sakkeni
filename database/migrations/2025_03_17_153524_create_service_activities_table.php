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
        Schema::create('service_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
            ->constrained('users') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('service_provider_id')
            ->constrained('service_providers') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->date("start_date");
            $table->date("end_eate");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_activities');
    }
};
