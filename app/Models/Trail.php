<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trail extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'difficulty_level',
        'distance_km',
        'elevation_gain_m',
        'estimated_time_hours',
        'trail_type',
        'start_coordinates',
        'end_coordinates',
        'route_coordinates',
        'gpx_file_path',
        'gpx_raw_data',                // NEW
        'gpx_calculated_distance',     // NEW
        'gpx_calculated_elevation',    // NEW
        'gpx_calculated_time',         // NEW
        'data_source',                 // NEW
        'gpx_uploaded_at',             // NEW
        'status',
        'best_seasons',
        'directions',
        'parking_info',
        'safety_notes',
        'is_featured',
        'trail_network_id',
        // Fishing lake fields
        'geometry_type',
        'location_type',
        'fishing_location',
        'fishing_distance_from_town',
        'fish_species',
        'best_fishing_time',
        'best_fishing_season',
    ];

    protected $casts = [
        'start_coordinates' => 'array',
        'end_coordinates' => 'array',
        'route_coordinates' => 'array',
        'best_seasons' => 'array',
        'gpx_raw_data' => 'array',              // NEW
        'is_featured' => 'boolean',
        'gpx_uploaded_at' => 'datetime',            // NEW
        'fish_species' => 'array',
    ];

    /**
     * Get all trail media (photos and videos) - NEW
     */
    public function media()
    {
        return $this->hasMany(TrailMedia::class)->ordered();
    }

    /**
     * Backwards-compatible alias for older views that used `trailMedia` relationship
     */
    public function trailMedia()
    {
        return $this->media();
    }

    /**
     * Get only photos - NEW
     */
    public function photoMedia()
    {
        return $this->hasMany(TrailMedia::class)->photos()->ordered();
    }

    /**
     * Get only videos - NEW
     */
    public function videoMedia()
    {
        return $this->hasMany(TrailMedia::class)->videos()->ordered();
    }

    /**
     * Get featured media (can be photo or video) - NEW
     */
    public function featuredMedia()
    {
        // Only consider photos as featured media
        return $this->hasOne(TrailMedia::class)
            ->where('is_featured', true)
            ->where('media_type', 'photo')
            ->orderBy('sort_order');
    }

    /**
     * Accessor to get a safe featured media URL (thumbnail for videos, url for photos)
     */
    public function getFeaturedMediaUrlAttribute()
    {
        // Prefer a featured photo
        $media = $this->trailMedia()
            ->where('is_featured', true)
            ->where('media_type', 'photo')
            ->first();

        // Fallback to first photo only (do not return videos)
        if (! $media) {
            $media = $this->trailMedia()->where('media_type', 'photo')->first();
        }

        if (! $media) {
            return null;
        }

        // Prefer thumbnail (for videos or generated thumbnails), otherwise full url
        if (method_exists($media, 'getThumbnailUrlAttribute')) {
            return $media->thumbnail_url ?? $media->url ?? null;
        }

        return $media->url ?? null;
    }

    /**
     * Get general trail media (not linked to any feature)
     */
    public function generalMedia()
    {
        return $this->hasMany(TrailMedia::class)
            ->doesntHave('features')
            ->ordered();
    }

    /**
     * Get trail features
     */
    public function features()
    {
        return $this->hasMany(TrailFeature::class);
    }

    /**
     * Get activity types for this trail
     */
    public function activities()
    {
        return $this->belongsToMany(ActivityType::class, 'trail_activities')
            ->withPivot(['activity_notes', 'activity_specific_data'])
            ->withTimestamps();
    }

    /**
     * Get seasonal data for this trail
     */
    public function seasonalData()
    {
        return $this->hasMany(SeasonalTrailData::class);
    }

    /**
     * Get difficulty as text
     */
    public function getDifficultyTextAttribute(): string
    {
        $levels = [
            1 => 'Very Easy',
            2 => 'Easy',
            3 => 'Moderate',
            4 => 'Hard',
            5 => 'Very Hard',
        ];

        return $levels[intval($this->difficulty_level)] ?? 'Unknown';
    }

    /**
     * Get trail type as text
     */
    public function getTrailTypeTextAttribute(): string
    {
        return ucwords(str_replace('-', ' ', $this->trail_type));
    }

    /**
     * Check if data is from GPX - NEW
     */
    public function isGpxSourced(): bool
    {
        return in_array($this->data_source, ['gpx', 'mixed']);
    }

    /**
     * Check if data has manual overrides - NEW
     */
    public function hasManualOverrides(): bool
    {
        return $this->data_source === 'mixed';
    }

    /**
     * Check if distance is locked (from GPX) - NEW
     */
    public function isDistanceLocked(): bool
    {
        return $this->data_source === 'gpx' && $this->gpx_calculated_distance !== null;
    }

    /**
     * Check if elevation is locked (from GPX) - NEW
     */
    public function isElevationLocked(): bool
    {
        return $this->data_source === 'gpx' && $this->gpx_calculated_elevation !== null;
    }

    /**
     * Check if time is locked (from GPX) - NEW
     */
    public function isTimeLocked(): bool
    {
        return $this->data_source === 'gpx' && $this->gpx_calculated_time !== null;
    }

    /**
     * Get the source of data (for display) - NEW
     */
    public function getDataSourceTextAttribute(): string
    {
        return match ($this->data_source) {
            'gpx' => 'GPX File',
            'mixed' => 'GPX + Manual',
            'manual' => 'Manual Entry',
            default => 'Unknown'
        };
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * Get seasonal data for a specific season - NEW
     */
    public function getSeasonalData(string $season)
    {
        return $this->seasonalData()->where('season', $season)->first();
    }

    /**
     * Check if trail is recommended for a season - NEW
     */
    public function isRecommendedForSeason(string $season): bool
    {
        $seasonalData = $this->getSeasonalData($season);

        return $seasonalData ? $seasonalData->recommended : true;
    }

    /**
     * Scope for featured trails
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for active trails
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Search scope
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%");
        });
    }

    /**
     * Difficulty filter scope
     */
    public function scopeDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    /**
     * Scope for GPX-sourced trails - NEW
     */
    public function scopeGpxSourced($query)
    {
        return $query->whereIn('data_source', ['gpx', 'mixed']);
    }

    public function highlights()
    {
        return $this->features();
    }

    public function trailNetwork()
    {
        return $this->belongsTo(TrailNetwork::class);
    }

    /**
     * Check if location is a fishing lake
     */
    public function isFishingLake(): bool
    {
        return $this->location_type === 'fishing_lake';
    }

    /**
     * Check if location is a trail
     */
    public function isTrail(): bool
    {
        return $this->location_type === 'trail';
    }

    /**
     * Get list of fish species
     */
    public function getFishSpeciesList(): array
    {
        return $this->fish_species ?? [];
    }
}
