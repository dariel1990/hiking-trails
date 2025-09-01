<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrailFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'trail_id',
        'feature_type',
        'name',
        'description',
        'coordinates',
    ];

    protected $casts = [
        'coordinates' => 'array',
    ];

    /**
     * Get the trail that owns the feature
     */
    public function trail()
    {
        return $this->belongsTo(Trail::class);
    }

    /**
     * Get feature type as icon
     */
    public function getIconAttribute(): string
    {
        $icons = [
            'waterfall' => '💧',
            'viewpoint' => '👁️',
            'wildlife' => '🦌',
            'bridge' => '🌉',
            'summit' => '⛰️',
            'lake' => '🏞️',
            'forest' => '🌲',
            'parking' => '🅿️',
            'restroom' => '🚻',
            'picnic' => '🍽️',
        ];

        return $icons[$this->feature_type] ?? '📍';
    }
}