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
        Schema::table('trail_networks', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('is_always_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trail_networks', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
