<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->foreignId('trail_network_id')->nullable()->constrained('trail_networks')->nullOnDelete()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\TrailNetwork::class);
            $table->dropColumn('trail_network_id');
        });
    }
};
