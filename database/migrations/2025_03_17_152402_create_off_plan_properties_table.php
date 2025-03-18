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
            $table->foreignId('propertyId')
            ->references('id')
            ->on('properties') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate(); 
            $table->date("deliveryDate");
            $table->float("firstPay");
            $table->json("payPlan");
            $table->float("overallPayment");
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
