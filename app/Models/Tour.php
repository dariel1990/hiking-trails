<?php

namespace App\Models;

use App\Models\Concerns\HasVideoEmbed;
use App\Observers\TownContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[ObservedBy(TownContentObserver::class)]
class Tour extends Model
{
    use HasVideoEmbed;

    protected $fillable = [
        'town_id',
        'title',
        'slug',
        'tagline',
        'description',
        'cover_image',
        'video_url',
        'tour_type',
        'icon',
        'icon_image',
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

    public function getIconImageUrlAttribute(): ?string
    {
        return $this->icon_image ? asset('storage/'.$this->icon_image) : null;
    }

    public function getTourIconAttribute(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        $label = static::getTourTypes()[$this->tour_type] ?? '';

        if (preg_match('/^(\X+?)\s/u', $label, $matches)) {
            return $matches[1];
        }

        return '📍';
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

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class);
    }

    /**
     * Limit to records belonging to the given town, accepting either a model or
     * a slug so controllers can pass a `?town=` query parameter straight in.
     */
    public function scopeInTown($query, Town|string $town)
    {
        if ($town instanceof Town) {
            return $query->where('town_id', $town->id);
        }

        return $query->whereHas('town', fn ($q) => $q->where('slug', $town));
    }
}
