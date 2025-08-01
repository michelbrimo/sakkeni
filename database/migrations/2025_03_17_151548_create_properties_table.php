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
            ->constrained('admins')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->float('area');
            $table->integer('bathrooms');
            $table->integer('balconies');
            $table->integer('users_clicks')->default(0);
            $table->foreignId('ownership_type_id')
            ->constrained('ownership_types')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('property_type_id')
            ->constrained('property_types')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('sell_type_id')
            ->constrained('sell_types')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('availability_status_id')
            ->constrained('availability_statuses')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->text('description')->nullable();
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
