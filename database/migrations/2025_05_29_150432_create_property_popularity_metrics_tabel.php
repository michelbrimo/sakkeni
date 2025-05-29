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
        Schema::create('property_popularity_metrics_tabel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->unsignedInteger('total_views')->default(0);
            $table->unsignedInteger('total_favorites')->default(0);
            $table->unsignedInteger('total_contacts')->default(0);
            $table->float('view_to_contact_ratio')->default(0);
            $table->timestamp('last_updated')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_popularity_metrics_tabel');
    }
};
