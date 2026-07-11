<?php

namespace App\Models;

use App\Models\Concerns\HasVideoEmbed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TrailNetwork extends Model
{
    use HasVideoEmbed;

    protected $fillable = [
        'network_name',
        'slug',
        'type',
        'season',
        'icon',
        'image',
        'description',
        'latitude',
        'longitude',
        'address',
        'website_url',
        'video_url',
        'is_always_visible',
        'is_active',
    ];

    protected $casts = [
        'is_always_visible' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($network) {
            if (empty($network->slug)) {
                $base = Str::slug($network->network_name);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.++$i;
                }
                $network->slug = $slug;
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function trails()
    {
        return $this->hasMany(Trail::class);
    }

    public function facilities()
    {
        return $this->hasMany(Facility::class);
    }

    public function activeFacilities()
    {
        return $this->hasMany(Facility::class)->where('is_active', true);
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(TrailNetworkSponsor::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeSponsors(): HasMany
    {
        return $this->hasMany(TrailNetworkSponsor::class)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
