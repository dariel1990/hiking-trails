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
        Schema::table('facility_media', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable()->after('file_path');
        });

        Schema::table('business_media', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable()->after('file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_media', function (Blueprint $table) {
            $table->dropColumn('thumbnail_path');
        });

        Schema::table('business_media', function (Blueprint $table) {
            $table->dropColumn('thumbnail_path');
        });
    }
};
