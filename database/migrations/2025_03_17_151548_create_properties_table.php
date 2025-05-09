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
            ->nullable()
            ->constrained('users')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->float('area');
            $table->integer('bathrooms');
            $table->integer('balconies');
            $table->string('ownership_type');
            $table->foreignId('physical_status_type_id')
            ->constrained('physical_status_types')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('propert_type_id')
            ->constrained('property_types')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->string('availability_status');             
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
