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
        Schema::create('commercial_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')
            ->constrained('properties') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate(); 
            $table->integer('floor');
            $table->integer('building_number');
            $table->integer('apartment_number');
            $table->foreignId('commercial_property_type_id')
            ->constrained('commercial_property_types') 
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
        Schema::dropIfExists('commercial_properties');
    }
};
