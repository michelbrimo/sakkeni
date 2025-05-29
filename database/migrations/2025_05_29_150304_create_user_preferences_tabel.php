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
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_type_id')->constrained('property_types')->cascadeOnDelete();;//preferred_property_type
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
            $table->json('must_have_features')->nullable(); // Array of feature IDs
            $table->timestamp('updated_at')->useCurrent();
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
