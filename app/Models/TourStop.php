<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourStop extends Model
{
    protected $fillable = [
        'tour_id',
        'trail_id',
        'trail_feature_id',
        'facility_id',
        'latitude',
        'longitude',
        'is_roaming',
        'stop_order',
        'stop_label',
        'icon',
        'icon_image',
        'description',
        'image',
        'driving_notes',
        'estimated_visit_time',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_roaming' => 'boolean',
        ];
    }

    public function getIconImageUrlAttribute(): ?string
    {
        return $this->icon_image ? asset('storage/'.$this->icon_image) : null;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    /**
     * Whether the stop has its own map icon (an uploaded image or an emoji).
     */
    public function hasIcon(): bool
    {
        return (bool) ($this->icon_image || $this->icon);
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function trail(): BelongsTo
    {
        return $this->belongsTo(Trail::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(TrailFeature::class, 'trail_feature_id');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Whether this stop is a point pinned directly on the map rather than a
     * linked trail, trail feature or facility.
     */
    public function isPinned(): bool
    {
        return $this->latitude !== null
            && $this->longitude !== null
            && ! $this->trail_id
            && ! $this->facility_id;
    }

    /**
     * Name shown for the stop: the label override, else the linked item's name.
     */
    public function displayName(string $fallback = 'Stop'): string
    {
        return $this->stop_label
            ?: ($this->feature?->name ?? $this->facility?->name ?? $this->trail?->name ?? $fallback);
    }

    /**
     * Best [lat, lng] for this stop (null for roaming activities with no fixed spot):
     * 1. A point pinned directly on the map
     * 2. The linked trail feature's coordinates
     * 3. The linked facility's location
     * 4. The trail's first route point, first feature with coordinates, or start coordinates
     *
     * @return array{0: float, 1: float}|null
     */
    public function coordinates(): ?array
    {
        if ($this->is_roaming) {
            return null;
        }

        if ($this->latitude !== null && $this->longitude !== null) {
            return [$this->latitude, $this->longitude];
        }

        if ($this->trail_feature_id && $this->feature?->coordinates) {
            return $this->feature->coordinates;
        }

        if ($this->facility_id && $this->facility) {
            return [(float) $this->facility->latitude, (float) $this->facility->longitude];
        }

        $trail = $this->trail;
        if (! $trail) {
            return null;
        }

        if (! empty($trail->route_coordinates)) {
            return $trail->route_coordinates[0];
        }

        $feature = $trail->features->whereNotNull('coordinates')->first();
        if ($feature) {
            return $feature->coordinates;
        }

        return $trail->start_coordinates ?: null;
    }
}
