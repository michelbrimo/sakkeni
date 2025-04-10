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
        Schema::create('appartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('residential_property_id')
            ->constrained('residential_properties') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->integer("floor");
            $table->integer('building_number');
            $table->integer('appartment_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appartments');
    }
};
