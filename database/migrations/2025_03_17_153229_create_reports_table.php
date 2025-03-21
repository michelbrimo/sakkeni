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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')
            ->constrained('users') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('reported_on_id')
            ->constrained('users') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->string("report");
            $table->string("report_status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
