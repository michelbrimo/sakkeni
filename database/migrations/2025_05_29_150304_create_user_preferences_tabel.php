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
        Schema::create('user_preferences_tabel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_type_id')->constrained('property_types')->cascadeOnDelete();
            $table->foreignId('sell_type_id')->constrained('sell_types')->cascadeOnDelete();
            $table->unique(['user_id', 'sell_type_id', 'property_type_id'], 'user_pref_unique');
            $table->unsignedTinyInteger('min_bedrooms')->nullable();
            $table->unsignedTinyInteger('max_bedrooms')->nullable();
            $table->unsignedTinyInteger('min_balconies')->nullable();
            $table->unsignedTinyInteger('max_balconies')->nullable();
            $table->unsignedTinyInteger('min_area')->nullable();
            $table->unsignedTinyInteger('max_area')->nullable();
            $table->unsignedTinyInteger('min_bathrooms')->nullable();
            $table->unsignedTinyInteger('max_bathrooms')->nullable();
            $table->unsignedInteger('min_price')->nullable();
            $table->unsignedInteger('max_price')->nullable();
            $table->json('preferred_locations')->nullable(); // Array of location IDs
            $table->json('must_amenity')->nullable(); // Array of amenities IDs
            $table->boolean('is_furnished')->nullable();
            $table->timestamps(); // Adds both created_at and updated_at
            //off plan sell_type_idsell_type_id=3
            $table->unsignedInteger('min_first_pay')->nullable();
            $table->unsignedInteger('max_first_pay')->nullable();
            $table->timestamp('delivery_date')->nullable();
            //rent sell_type_idsell_type_id=2
            $table->string('lease_period')->nullable();

        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences_tabel');
    }
};
