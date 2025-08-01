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
        Schema::create('work_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_service_id')
            ->constrained('service_provider_services') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->string("image_path");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_galleries');
    }
};
