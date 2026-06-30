<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tour extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'tagline',
        'description',
        'cover_image',
        'tour_type',
        'difficulty_summary',
        'duration_estimate',
        'total_driving_km',
        'driving_route_coordinates',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'driving_route_coordinates' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'total_driving_km' => 'decimal:2',
        ];
    }

    public function stops(): HasMany
    {
        return $this->hasMany(TourStop::class)->orderBy('stop_order');
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/'.$this->cover_image) : null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getTourTypes(): array
    {
        return [
            'waterfalls' => '💧 Waterfalls',
            'fishing' => '🎣 Fishing',
            'heritage' => '🏛️ Heritage',
            'scenic' => '🌄 Scenic',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Tour $tour) {
            if (empty($tour->slug)) {
                $base = Str::slug($tour->title);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.++$i;
                }
                $tour->slug = $slug;
            }
        });
    }
}
