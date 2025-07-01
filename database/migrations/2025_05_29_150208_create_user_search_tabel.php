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
        Schema::create('user_search_tabel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sell_type_id')->constrained('sell_types')->cascadeOnDelete();
            // $table->string('search_query')->nullable();
            $table->json('filters')->nullable(); // Stores all search filters as JSON
            $table->timestamps(); // Adds both created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_search_tabel');
    }
};
