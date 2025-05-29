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
        Schema::create('property_similarity_scores_tabel', function (Blueprint $table) {
            $table->foreignId('property_id_1')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('property_id_2')->constrained('properties')->cascadeOnDelete();
            $table->float('overall_similarity', 3, 2)->index();
            $table->float('price_similarity', 3, 2)->nullable();
            $table->float('feature_similarity', 3, 2)->nullable();
            $table->float('location_similarity', 3, 2)->nullable();
            $table->timestamp('last_calculated')->useCurrent();

            $table->primary(['property_id_1', 'property_id_2']);
            $table->index(['property_id_1', 'overall_similarity'], 'psst_prop1_similarity_idx');
            $table->index(['property_id_2', 'overall_similarity'], 'psst_prop2_similarity_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_similarity_scores_tabel');
    }
};