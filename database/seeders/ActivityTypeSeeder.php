<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityType;

class ActivityTypeSeeder extends Seeder
{
    public function run(): void
    {
        $activities = [
            // Summer Activities
            [
                'name' => 'Hiking',
                'slug' => 'hiking',
                'icon' => '🥾',
                'color' => '#10B981',
                'description' => 'Walking trails and mountain paths',
                'season_applicable' => 'summer',
                'is_active' => true
            ],
            [
                'name' => 'Fishing',
                'slug' => 'fishing',
                'icon' => '🎣',
                'color' => '#3B82F6',
                'description' => 'Fishing spots and lakes',
                'season_applicable' => 'summer',
                'is_active' => true
            ],
            [
                'name' => 'Camping',
                'slug' => 'camping',
                'icon' => '⛺',
                'color' => '#F59E0B',
                'description' => 'Camping areas and facilities',
                'season_applicable' => 'summer',
                'is_active' => true
            ],
            [
                'name' => 'Viewpoints',
                'slug' => 'viewpoint',
                'icon' => '👁️',
                'color' => '#8B5CF6',
                'description' => 'Scenic overlooks and vistas',
                'season_applicable' => 'both',
                'is_active' => true
            ],
            
            // Winter Activities
            [
                'name' => 'Snowshoeing',
                'slug' => 'snowshoeing',
                'icon' => '🥾',
                'color' => '#06B6D4',
                'description' => 'Winter hiking with snowshoes',
                'season_applicable' => 'winter',
                'is_active' => true
            ],
            [
                'name' => 'Ice Fishing',
                'slug' => 'ice-fishing',
                'icon' => '🎣',
                'color' => '#0EA5E9',
                'description' => 'Ice fishing locations',
                'season_applicable' => 'winter',
                'is_active' => true
            ],
            [
                'name' => 'Cross-Country Skiing',
                'slug' => 'cross-country-skiing',
                'icon' => '⛷️',
                'color' => '#3B82F6',
                'description' => 'Nordic skiing trails',
                'season_applicable' => 'winter',
                'is_active' => true
            ],
            [
                'name' => 'Downhill Skiing',
                'slug' => 'downhill-skiing',
                'icon' => '🎿',
                'color' => '#6366F1',
                'description' => 'Alpine skiing areas',
                'season_applicable' => 'winter',
                'is_active' => true
            ],
        ];

        foreach ($activities as $activity) {
            ActivityType::updateOrCreate(
                ['slug' => $activity['slug']],
                $activity
            );
        }
    }
}