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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
            ->constrained('users') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->foreignId('account_type_id')
            ->constrained('account_types') 
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->integer("free_ads_left")->default(3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
