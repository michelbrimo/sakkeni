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
            $table->foreignId('propertyId')
            ->references('id')
            ->on('properties') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate(); 
            $table->integer('floor');
            $table->integer('buildingNumber');
            $table->integer('appartmentNumber');
            $table->string('propertyType');
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
