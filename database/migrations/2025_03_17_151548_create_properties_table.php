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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')
            ->constrained('locations') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();   
            $table->foreignId('owner_id')
            ->constrained('users')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('admin_id')
            ->constrained('users')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->string('area');
            $table->json('exposure');
            $table->integer('bathrooms');
            $table->integer('balconies');
            $table->string('ownership_type');
            $table->string('property_physical_status');
            $table->string('availability_status');
            $table->boolean('is_furnished');
             
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
