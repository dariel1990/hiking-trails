<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('trail_features', function (Blueprint $table) {
            $table->string('icon', 50)->nullable()->after('feature_type');
            $table->string('color', 7)->nullable()->after('icon');
        });

        // Set default icon and color based on existing feature_type
        $this->backfillIconsAndColors();
    }

    public function down()
    {
        Schema::table('trail_features', function (Blueprint $table) {
            $table->dropColumn(['icon', 'color']);
        });
    }

    /**
     * Backfill existing records with default icons and colors
     */
    private function backfillIconsAndColors()
    {
        $defaults = [
            'waterfall' => ['icon' => '💧', 'color' => '#3B82F6'],
            'viewpoint' => ['icon' => '👁️', 'color' => '#8B5CF6'],
            'wildlife' => ['icon' => '🦌', 'color' => '#84CC16'],
            'bridge' => ['icon' => '🌉', 'color' => '#F59E0B'],
            'summit' => ['icon' => '⛰️', 'color' => '#10B981'],
            'lake' => ['icon' => '🏞️', 'color' => '#06B6D4'],
            'forest' => ['icon' => '🌲', 'color' => '#059669'],
            'parking' => ['icon' => '🅿️', 'color' => '#8B5CF6'],
            'restroom' => ['icon' => '🚻', 'color' => '#EC4899'],
            'picnic' => ['icon' => '🍽️', 'color' => '#F97316'],
            'camping' => ['icon' => '⛺', 'color' => '#EF4444'],
            'shelter' => ['icon' => '🏠', 'color' => '#6B7280'],
            'other' => ['icon' => '📍', 'color' => '#6B7280'],
        ];

        foreach ($defaults as $type => $values) {
            DB::table('trail_features')
                ->where('feature_type', $type)
                ->whereNull('icon')
                ->update([
                    'icon' => $values['icon'],
                    'color' => $values['color'],
                ]);
        }
    }
};