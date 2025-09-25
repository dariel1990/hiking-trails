<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrailHighlight extends Model
{
    use HasFactory;

    protected $fillable = [
        'trail_id',
        'name',
        'description',
        'type',
        'coordinates',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'coordinates' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the trail that owns the highlight
     */
    public function trail()
    {
        return $this->belongsTo(Trail::class);
    }

    /**
     * Get icon for highlight type
     */
    public function getDefaultIconAttribute(): string
    {
        $icons = [
            'viewpoint' => '👁️',
            'waterfall' => '💧',
            'summit' => '⛰️',
            'lake' => '🏞️',
            'bridge' => '🌉',
            'wildlife' => '🦌',
            'camping' => '⛺',
            'parking' => '🅿️',
            'picnic' => '🍽️',
            'restroom' => '🚻',
            'danger' => '⚠️',
            'photo_spot' => '📷',
        ];

        return $icons[$this->type] ?? '📍';
    }

    /**
     * Get the display icon (custom or default)
     */
    public function getDisplayIconAttribute(): string
    {
        return $this->icon ?? $this->default_icon;
    }
}