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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_provider_id')->constrained('service_providers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->text('job_description');
            $table->decimal('amount', 8, 2)->nullable();// The price quoted by the service provider.
            $table->text('scope_of_work')->nullable();
            $table->string('status')->default('Pending Provider Response');// (e.g., 'Pending Provider Response', 'Pending User Acceptance', 'Accepted', 'Declined').
            $table->date('start_date')->nullable()->after('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
