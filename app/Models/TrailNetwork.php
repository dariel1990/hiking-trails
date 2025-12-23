<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TrailNetwork extends Model
{
    protected $fillable = [
        'network_name',
        'slug',
        'type',
        'description',
        'latitude',
        'longitude',
        'address',
        'website_url',
        'is_always_visible',
    ];

    protected $casts = [
        'is_always_visible' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($network) {
            if (empty($network->slug)) {
                $network->slug = Str::slug($network->network_name);
            }
        });
    }

    public function trails()
    {
        return $this->hasMany(Trail::class);
    }

    /**
     * Get all facilities for this trail network
     */
    public function facilities()
    {
        return $this->hasMany(NetworkFacility::class);
    }

    /**
     * Get active facilities only
     */
    public function activeFacilities()
    {
        return $this->hasMany(NetworkFacility::class)->where('is_active', true);
    }
}