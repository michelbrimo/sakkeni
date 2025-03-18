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
            $table->foreignId('locationId')
            ->references('id')
            ->on('locations') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();  
            $table->foreignId('ownerId')
            ->references('id')
            ->on('users') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate(); 
            $table->string('area');
            $table->json('exposure');
            $table->integer('bathrooms');
            $table->integer('balconies');
            $table->string('ownershipType');
            $table->string('propertyPhysicalStatus');
            $table->string('availabilityStatus');
            $table->boolean('isFurnished');
            $table->foreignId('adminId')
            ->references('id')
            ->on('users') 
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
        Schema::dropIfExists('properties');
    }
};
