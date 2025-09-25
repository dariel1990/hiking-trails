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
        'status',
        'best_seasons',
        'directions',
        'parking_info',
        'safety_notes',
        'is_featured',
    ];

    protected $casts = [
        'start_coordinates' => 'array',
        'end_coordinates' => 'array',
        'route_coordinates' => 'array',
        'best_seasons' => 'array',
        'is_featured' => 'boolean',
        'difficulty_level' => 'decimal:1',
        'distance_km' => 'decimal:2',
        'estimated_time_hours' => 'decimal:2',
    ];

    /**
     * Get trail photos
     */
    public function photos()
    {
        return $this->hasMany(TrailPhoto::class)->orderBy('sort_order');
    }

    /**
     * Get featured photo
     */
    public function featuredPhoto()
    {
        return $this->hasOne(TrailPhoto::class)->where('is_featured', true);
    }

    /**
     * Get trail features
     */
    public function features()
    {
        return $this->hasMany(TrailFeature::class);
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
            5 => 'Very Hard'
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
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
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
        return $query->where(function($q) use ($term) {
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

    // app/Models/Trail.php - Add these methods

    public function activities()
    {
        return $this->belongsToMany(ActivityType::class, 'trail_activities')
                    ->withPivot(['activity_notes', 'activity_specific_data'])
                    ->withTimestamps();
    }

    public function seasonalData()
    {
        return $this->hasMany(SeasonalTrailData::class);
    }

    public function getSeasonalData($season)
    {
        return $this->seasonalData()->where('season', $season)->first();
    }

    public function isRecommendedForSeason($season): bool
    {
        $seasonalData = $this->getSeasonalData($season);
        return $seasonalData ? $seasonalData->recommended : true;
    }

    public function getRouteCoordinatesAttribute($value)
    {
        if (empty($value)) {
            return null;
        }
        
        // If it's already an array, return it
        if (is_array($value)) {
            return $value;
        }
        
        // If it's a JSON string (wrapped in quotes), decode it
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            
            // If decoding successful and not empty, return the array
            if (json_last_error() === JSON_ERROR_NONE && !empty($decoded)) {
                return $decoded;
            }
        }
        
        return null;
    }

    public function setRouteCoordinatesAttribute($value)
    {
        if (empty($value) || $value === null) {
            $this->attributes['route_coordinates'] = null;
            return;
        }
        
        // If it's already a string, store it directly
        if (is_string($value)) {
            $this->attributes['route_coordinates'] = $value;
            return;
        }
        
        // If it's an array, encode it to JSON
        $this->attributes['route_coordinates'] = json_encode($value);
    }

    /**
     * Get trail highlights (viewpoints, etc.)
     */
    public function highlights()
    {
        return $this->hasMany(TrailHighlight::class)->where('is_active', true)->orderBy('sort_order');
    }
}