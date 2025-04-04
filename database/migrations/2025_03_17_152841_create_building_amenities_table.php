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
        Schema::create('building_amenities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('amenity_id')
            ->constrained('amenities') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('property_id')
            ->constrained('properties') 
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
        Schema::dropIfExists('building_amenities');
    }
};
