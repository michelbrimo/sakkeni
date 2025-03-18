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
        Schema::create('property_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userId')
            ->references('id')
            ->on('users') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('propertyId')
            ->references('id')
            ->on('properties') 
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
        Schema::dropIfExists('property_favorites');
    }
};
