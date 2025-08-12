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
        Schema::create('report_on_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->comment('The user who made the report')
                  ->constrained('users')->cascadeOnDelete();

            // These two columns create the polymorphic relationship
            $table->morphs('reportable'); // This will create `reportable_id` (unsignedBigInteger) and `reportable_type` (string)

            $table->foreignId('report_reason_id')
                  ->constrained('report_reasons')->cascadeOnDelete();
            
            $table->text('additional_comments')->nullable();
            $table->enum('status', ['pending', 'resolved', 'dismissed'])->default('pending');

            $table->foreignId('admin_id')->comment('The admin who reviewed the report')->nullable()
                  ->constrained('admins')->nullOnDelete();

            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports_on_services');
    }
};
