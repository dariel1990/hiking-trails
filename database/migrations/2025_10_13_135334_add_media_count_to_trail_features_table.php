<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trail_features', function (Blueprint $table) {
            // Cache media count for performance (avoid counting each time)
            $table->integer('media_count')->default(0)->after('coordinates');
        });
    }

    public function down(): void
    {
        Schema::table('trail_features', function (Blueprint $table) {
            $table->dropColumn('media_count');
        });
    }
};