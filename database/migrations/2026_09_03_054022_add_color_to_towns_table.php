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
        Schema::table('towns', function (Blueprint $table) {
            /**
             * Hex colour used to tint this town's pins on the interactive map
             * and its swatch in the legend. Nullable rather than defaulted so
             * "never configured" stays distinguishable from a deliberate grey.
             */
            $table->string('color', 7)->nullable()->after('map_zoom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('towns', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
