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
            $table->foreignId('reporterId')
            ->references('id')
            ->on('users') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('reportedOnId')
            ->references('id')
            ->on('users') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->string("report");
            $table->string("reportStatus");
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
