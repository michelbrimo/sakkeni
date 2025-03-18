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
        Schema::create('rents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('readyPropertyId')
            ->references('id')
            ->on('ready_to_move_in_properties') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate(); 
            $table->float("price");
            $table->float("leasePeriod");
            $table->string("paymentPlan");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rents');
    }
};
