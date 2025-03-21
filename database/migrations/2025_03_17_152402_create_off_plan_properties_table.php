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
        Schema::create('off_plan_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')
            ->constrained('properties')
            ->cascadeOnDelete()
            ->cascadeOnUpdate(); 
            $table->date("delivery_date");
            $table->float("first_pay");
            $table->json("pay_plan");
            $table->float("overall_payment");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('off_plan_properties');
    }
};
