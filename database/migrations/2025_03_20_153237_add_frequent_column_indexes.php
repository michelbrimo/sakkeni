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
        // Properties Table
        Schema::table('properties', function (Blueprint $table) {
            $table->index('ownership_type'); 
            $table->index('created_at'); 
        });

        // Users Table (email is already unique, but verify index exists)
        Schema::table('users', function (Blueprint $table) {
            $table->index('email'); // Usually added automatically for unique()
        });

        // Apartments Table (corrected from apartments)
        Schema::table('apartments', function (Blueprint $table) {
            $table->index('apartment_number');
        });

        // Cities Table
        Schema::table('cities', function (Blueprint $table) {
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['ownership_type']);
            $table->dropIndex(['created_at']);
        });
    
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
        });
    
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropIndex(['apartment_number']);
        });
    
        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};
