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
        'media_count',
        'icon',
        'icon_image',
        'color',
    ];

    protected $appends = ['icon_image_url'];

    protected $casts = [
        'coordinates' => 'array',
    ];

    /**
     * Available highlight (feature) types with their human-readable labels.
     *
     * @return array<string, string>
     */
    public static function getFeatureTypes(): array
    {
        return [
            'viewpoint' => '👁️ Viewpoint',
            'waterfall' => '💧 Waterfall',
            'summit' => '⛰️ Summit',
            'bridge' => '🌉 Bridge',
            'lake' => '🏞️ Lake',
            'wildlife' => '🦌 Wildlife',
            'camping' => '⛺ Camping',
            'shelter' => '🏠 Shelter',
            'forest' => '🌲 Forest',
            'parking' => '🅿️ Parking',
            'restroom' => '🚻 Restroom',
            'picnic' => '🍽️ Picnic',
            'fishing' => '🐟 Fishing',
            'other' => '📍 Other',
        ];
    }

    /**
     * Human-readable label for this feature's type.
     */
    public function getFeatureTypeLabelAttribute(): string
    {
        return self::getFeatureTypes()[$this->feature_type] ?? ucfirst((string) $this->feature_type);
    }

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
     * Full public URL for the custom icon image, or null if not set.
     */
    public function getIconImageUrlAttribute(): ?string
    {
        return $this->icon_image ? asset('storage/'.$this->icon_image) : null;
    }

    /**
     * Get feature icon with fallback to feature_type mapping
     */
    public function getIconAttribute($value): string
    {
        // If custom icon is set, return it
        if (! empty($value)) {
            return $value;
        }

        // Otherwise, fallback to feature_type mapping
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
            'camping' => '⛺',
            'shelter' => '🏠',
            'other' => '📍',
        ];

        return $icons[$this->feature_type] ?? '📍';
    }

    /**
     * Get feature color with fallback to feature_type mapping
     */
    public function getColorAttribute($value): string
    {
        // If custom color is set, return it
        if (! empty($value)) {
            return $value;
        }

        // Otherwise, fallback to feature_type mapping
        $colors = [
            'waterfall' => '#3B82F6',
            'viewpoint' => '#8B5CF6',
            'wildlife' => '#84CC16',
            'bridge' => '#F59E0B',
            'summit' => '#10B981',
            'lake' => '#06B6D4',
            'forest' => '#059669',
            'parking' => '#8B5CF6',
            'restroom' => '#EC4899',
            'picnic' => '#F97316',
            'camping' => '#EF4444',
            'shelter' => '#6B7280',
            'other' => '#6B7280',
        ];

        return $colors[$this->feature_type] ?? '#6B7280';
    }

    /**
     * Get the primary photo URL for this feature
     */
    public function getPhotoUrlAttribute(): ?string
    {
        $primaryMedia = $this->primaryMedia() ?? $this->photos()->first();

        return $primaryMedia?->url;
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
