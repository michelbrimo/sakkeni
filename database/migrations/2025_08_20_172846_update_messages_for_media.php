<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // in the new ...update_messages_for_media.php file

    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Add a type column to know what kind of message it is
            $table->string('type')->default('text')->after('body');

            // Add a file_path column to store the URL or path to the media
            $table->string('file_path')->nullable()->after('type');

            // Make the original body column nullable
            $table->text('body')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['type', 'file_path']);
            $table->text('body')->nullable(false)->change();
        });
    }
};
