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
        // Locations Table
        Schema::table('locations', function (Blueprint $table) {
            $table->index(['country_id', 'city_id']); // Geo-queries
        });

        // Properties Table
        Schema::table('properties', function (Blueprint $table) {
            // $table->index(['availability_status', 'is_furnished']); // Common filters
            $table->index(['availability_status', 'created_at']); // New listings
        });

        // Building Amenities Table
        Schema::table('amenity_property', function (Blueprint $table) {
            $table->index(['amenity_id', 'property_id']); // Amenity lookups
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex(['country_id', 'city_id']);
        });
    
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['availability_status', 'is_furnished']);
            $table->dropIndex(['availability_status', 'created_at']);
        });
    
        Schema::table('amenity_building', function (Blueprint $table) {
            $table->dropIndex(['amenity_id', 'property_id']);
        });
    }
};
