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
            // Core type fields
            $table->enum('geometry_type', ['point', 'linestring'])->default('linestring')->after('id');
            $table->enum('location_type', ['trail', 'fishing_lake'])->default('trail')->after('geometry_type');
            
            // Fishing lake specific fields
            $table->string('fishing_location')->nullable()->after('location');
            $table->string('fishing_distance_from_town')->nullable()->after('fishing_location');
            $table->json('fish_species')->nullable()->after('fishing_distance_from_town');
            $table->string('best_fishing_time')->nullable()->after('fish_species');
            $table->string('best_fishing_season')->nullable()->after('best_fishing_time');
            
            // Make trail-specific fields nullable
            $table->decimal('difficulty_level', 2, 1)->nullable()->change();
            $table->decimal('distance_km', 8, 2)->nullable()->change();
            $table->integer('elevation_gain_m')->nullable()->change();
            $table->decimal('estimated_time_hours', 4, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trails', function (Blueprint $table) {
            $table->dropColumn([
                'geometry_type',
                'location_type',
                'fishing_location',
                'fishing_distance_from_town',
                'fish_species',
                'best_fishing_time',
                'best_fishing_season',
            ]);
        });
    }
};
