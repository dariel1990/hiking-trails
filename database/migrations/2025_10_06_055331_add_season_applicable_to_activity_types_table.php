<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_types', function (Blueprint $table) {
            $table->string('season_applicable')->default('both')->after('color');
            // Values: 'summer', 'winter', 'both'
        });
    }

    public function down(): void
    {
        Schema::table('activity_types', function (Blueprint $table) {
            $table->dropColumn('season_applicable');
        });
    }
};