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
        Schema::table('trails', function (Blueprint $table) {
            $table->foreignId('trail_network_id')->nullable()->after('id')->constrained('trail_networks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trails', function (Blueprint $table) {
            $table->dropForeign(['trail_network_id']);
            $table->dropColumn('trail_network_id');
        });
    }
};
