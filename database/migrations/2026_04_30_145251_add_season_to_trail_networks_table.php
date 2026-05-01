<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trail_networks', function (Blueprint $table) {
            $table->enum('season', ['summer', 'winter', 'both'])
                ->default('both')
                ->after('type');
        });

        // Backfill sensible defaults from existing type values.
        DB::table('trail_networks')
            ->whereIn('type', ['nordic_skiing', 'downhill_skiing'])
            ->update(['season' => 'winter']);

        DB::table('trail_networks')
            ->whereIn('type', ['hiking', 'mountain_biking'])
            ->update(['season' => 'summer']);
    }

    public function down(): void
    {
        Schema::table('trail_networks', function (Blueprint $table) {
            $table->dropColumn('season');
        });
    }
};
