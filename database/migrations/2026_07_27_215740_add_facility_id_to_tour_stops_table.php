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
        Schema::table('tour_stops', function (Blueprint $table) {
            $table->foreignId('trail_id')->nullable()->change();
            $table->foreignId('facility_id')->nullable()->after('trail_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_stops', function (Blueprint $table) {
            $table->dropConstrainedForeignId('facility_id');
            $table->foreignId('trail_id')->nullable(false)->change();
        });
    }
};
