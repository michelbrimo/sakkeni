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
        Schema::create('residential_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')
            ->constrained('properties') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->integer("bedrooms");
            $table->foreignId('residential_property_type_id')
            ->constrained('residential_property_types') 
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
        Schema::dropIfExists('residential_properties');
    }
};
