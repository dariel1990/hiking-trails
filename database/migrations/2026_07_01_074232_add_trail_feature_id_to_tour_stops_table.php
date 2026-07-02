<?php

use App\Models\TrailFeature;
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
            $table->foreignId('trail_feature_id')->nullable()->after('trail_id')->constrained('trail_features')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tour_stops', function (Blueprint $table) {
            $table->dropForeignIdFor(TrailFeature::class);
            $table->dropColumn('trail_feature_id');
        });
    }
};
