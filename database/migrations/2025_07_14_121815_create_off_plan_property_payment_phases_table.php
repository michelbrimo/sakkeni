<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffPlanPropertyPaymentPhasesTable extends Migration
{
    public function up(): void
    {
        Schema::create('off_plan_property_payment_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('off_plan_property_id')
                  ->constrained('off_plan_properties')
                  ->onDelete('cascade');
            $table->foreignId('payment_phase_id')
                  ->constrained('payment_phases')
                  ->onDelete('cascade');
            $table->decimal('payment_percentage')->nullable();
            $table->decimal('payment_value')->nullable();   
            $table->integer('duration_value')->nullable(); 
            $table->string('duration_unit')->nullable();  //'months', 'years', or 'on_handover'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('off_plan_property_payment_phases');
    }
}
