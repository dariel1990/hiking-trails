<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        ];

        return $icons[$this->facility_type] ?? '📍';
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