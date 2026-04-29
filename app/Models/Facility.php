<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_type',
        'name',
        'latitude',
        'longitude',
        'description',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    protected $appends = ['media_count'];

    /**
     * Get all media for this facility
     */
    public function media()
    {
        return $this->hasMany(FacilityMedia::class)->orderBy('sort_order')->orderBy('created_at');
    }

    /**
     * Get only photos
     */
    public function photos()
    {
        return $this->media()->where('media_type', 'photo');
    }

    /**
     * Get only videos
     */
    public function videos()
    {
        return $this->media()->where('media_type', 'video_url');
    }

    /**
     * Get primary media
     */
    public function primaryMedia()
    {
        return $this->media()->where('is_primary', true)->first();
    }

    /**
     * Get media count attribute
     */
    public function getMediaCountAttribute()
    {
        return $this->media()->count();
    }

    /**
     * Check if facility has media
     */
    public function hasMedia(): bool
    {
        return $this->media()->count() > 0;
    }

    /**
     * Get icon based on facility type
     */
    public function getIconAttribute($value)
    {
        // Return custom icon if set, otherwise default based on type
        if ($value) {
            return $value;
        }

        return $this->getDefaultIcon();
    }

    /**
     * Get default icon for facility type
     */
    public function getDefaultIcon()
    {
        $icons = [
            'parking' => '🅿️',
            'toilets' => '🚻',
            'emergency_kit' => '🏥',
            'lodge' => '🏠',
            'viewpoint' => '👁️',
            'info' => 'ℹ️',
            'picnic' => '🍽️',
            'water' => '💧',
            'shelter' => '⛺',
            'camping_site' => '🏕️',
            'point_of_interest' => '📍'
        ];

        return $icons[$this->facility_type] ?? '📍';
    }

    /**
     * Get available facility types
     */
    public static function getFacilityTypes(): array
    {
        return [
            'parking' => '🅿️ Parking',
            'toilets' => '🚻 Toilets',
            'emergency_kit' => '🏥 Emergency Kit',
            'lodge' => '🏠 Lodge',
            'viewpoint' => '👁️ Viewpoint',
            'info' => 'ℹ️ Information',
            'picnic' => '🍽️ Picnic Area',
            'water' => '💧 Water',
            'shelter' => '⛺ Shelter',
            'camping_site' => '🏕️ Camping Site',
            'point_of_interest' => '📍 Point of Interest',
        ];
    }

    /**
     * Scope for active facilities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by facility type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('facility_type', $type);
    }

    /**
     * Get facility type label
     */
    public function getFacilityTypeLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->facility_type));
    }
}
