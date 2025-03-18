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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('countryId')
            ->references('id')
            ->on('countries') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();  
            $table->foreignId('cityId')
            ->references('id')
            ->on('cities') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->string("altitude");
            $table->string("longtitude");  
            $table->string("additionalInfo");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
