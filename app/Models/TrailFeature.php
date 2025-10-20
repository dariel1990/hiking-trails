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
        'media_count',  // NEW
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
     * Get the media linked to this feature - NEW
     */
    public function media()
    {
        return $this->belongsToMany(TrailMedia::class, 'trail_feature_media')
                    ->withPivot(['is_primary', 'sort_order', 'caption_override'])
                    ->withTimestamps()
                    ->orderBy('trail_feature_media.sort_order')
                    ->orderBy('trail_feature_media.created_at');
    }

    /**
     * Get only photos linked to this feature - NEW
     */
    public function photos()
    {
        return $this->media()->where('media_type', 'photo');
    }

    /**
     * Get only videos linked to this feature - NEW
     */
    public function videos()
    {
        return $this->media()->whereIn('media_type', ['video', 'video_url']);
    }

    /**
     * Get the primary media for this feature - NEW
     */
    public function primaryMedia()
    {
        return $this->belongsToMany(TrailMedia::class, 'trail_feature_media')
                    ->wherePivot('is_primary', true)
                    ->withPivot(['is_primary', 'sort_order', 'caption_override'])
                    ->withTimestamps()
                    ->first();
    }

    /**
     * Update the cached media count - NEW
     */
    public function updateMediaCount(): void
    {
        $this->media_count = $this->media()->count();
        $this->saveQuietly(); // Save without triggering events
    }

    /**
     * Check if this feature has any media - NEW
     */
    public function hasMedia(): bool
    {
        return $this->media_count > 0;
    }

    /**
     * Check if this feature has reached photo limit (10) - NEW
     */
    public function hasReachedPhotoLimit(): bool
    {
        return $this->photos()->count() >= 10;
    }

    /**
     * Check if this feature has reached video limit (1) - NEW
     */
    public function hasReachedVideoLimit(): bool
    {
        return $this->videos()->count() >= 1;
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
            'camping' => '⛺',      // NEW
            'shelter' => '🏠',     // NEW
            'other' => '📍',       // NEW
        ];

        return $icons[$this->feature_type] ?? '📍';
    }

    /**
     * Boot method to handle model events - NEW
     */
    protected static function boot()
    {
        parent::boot();

        // When a feature is deleted, update media counts
        static::deleting(function ($feature) {
            // Detach all media (this will trigger the pivot table deletion)
            $feature->media()->detach();
        });
    }
}