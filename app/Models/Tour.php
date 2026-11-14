<?php

namespace App\Models;

use App\Models\Concerns\HasVideoEmbed;
use App\Observers\TownContentObserver;
use App\Support\RouteMapPayload;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Tour type that map-based events used before Events got their own module
     * (App\Models\Event). It is no longer offered; any leftover rows of this
     * type stay hidden from tour listings and their old links redirect to the
     * event of the same slug.
     */
    public const LEGACY_EVENT_TYPE = 'christmas';

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Only regular tours (excludes leftover event-type rows).
     */
    public function scopeTours(Builder $query): Builder
    {
        return $query->where('tour_type', '!=', self::LEGACY_EVENT_TYPE);
    }

    /**
     * @return array<string, string>
     */
    public static function getTourTypes(): array
    {
        return [
            'waterfalls' => '💧 Waterfalls',
            'fishing' => '🎣 Fishing',
            'heritage' => '🏛️ Heritage',
            'scenic' => '🌄 Scenic',
        ];
    }

    public function isLegacyEvent(): bool
    {
        return $this->tour_type === self::LEGACY_EVENT_TYPE;
    }

    /**
     * Data for the shared route map script.
     *
     * @return array<string, mixed>
     */
    public function toMapPayload(bool $showPopups = false): array
    {
        return RouteMapPayload::make(
            stops: $this->stops,
            route: $this->driving_route_coordinates,
            iconImage: $this->icon_image_url,
            iconEmoji: $this->tour_icon,
            isChristmas: false,
            showPopups: $showPopups,
        );
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
