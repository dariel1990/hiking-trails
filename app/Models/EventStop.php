<?php

namespace App\Models;

use Database\Factories\EventStopFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An activity or stop on an event's map: a pin on the map, or a roaming
 * activity (e.g. carollers) that is listed without a pin.
 */
class EventStop extends Model
{
    /** @use HasFactory<EventStopFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'latitude',
        'longitude',
        'is_roaming',
        'stop_order',
        'stop_label',
        'time_label',
        'notes',
        'icon',
        'icon_image',
        'description',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_roaming' => 'boolean',
            'stop_order' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
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

    public function displayName(string $fallback = 'Stop'): string
    {
        return $this->stop_label ?: $fallback;
    }

    /**
     * [lat, lng] of the pin, or null for roaming activities.
     *
     * @return array{0: float, 1: float}|null
     */
    public function coordinates(): ?array
    {
        if ($this->is_roaming || $this->latitude === null || $this->longitude === null) {
            return null;
        }

        return [$this->latitude, $this->longitude];
    }

    /**
     * @return array<string, mixed>
     */
    public function toMapStop(int $index, ?int $number): array
    {
        return [
            'index' => $index,
            'number' => $number,
            'label' => $this->displayName(),
            'coords' => $this->coordinates(),
            'time' => $this->time_label,
            'note' => $this->notes,
            'description' => $this->description,
            'icon' => $this->icon,
            'iconImage' => $this->icon_image_url,
            'image' => $this->image_url,
        ];
    }
}
