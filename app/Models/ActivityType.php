<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityType extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'icon_image', 'color', 'description', 'season_applicable', 'is_active'];

    public function trails()
    {
        return $this->belongsToMany(Trail::class, 'trail_activities')
            ->withPivot(['activity_notes', 'activity_specific_data'])
            ->withTimestamps();
    }

    /**
     * Get the icon, falling back to a stock icon when no emoji or image is set.
     */
    public function getIconAttribute(?string $value): string
    {
        return $value ?: '🥾';
    }
}
