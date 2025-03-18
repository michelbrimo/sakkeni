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
        Schema::create('ready_to_move_in_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propertyId')
            ->references('id')
            ->on('properties') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->string("sellType");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ready_to_move_in_properties');
    }
};
