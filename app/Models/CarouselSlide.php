<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CarouselSlide extends Model
{
    protected $fillable = [
        'filename',
        'caption',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/slide-show/'.rawurlencode($this->filename));
    }

    /**
     * Today's date in the site's local timezone (Smithers), as "Y-m-d". Schedules flip at
     * local midnight, so a slide ending Nov 15 shows through the end of Nov 15 locally.
     */
    public static function today(): string
    {
        return Carbon::now(config('app.local_timezone'))->toDateString();
    }

    /**
     * Turn off slides whose end date has passed, so they read as inactive in the admin
     * too (not just hidden from the site). Returns how many were turned off.
     */
    public static function deactivateExpired(): int
    {
        return static::query()
            ->where('is_active', true)
            ->whereNotNull('ends_at')
            ->whereDate('ends_at', '<', static::today())
            ->update(['is_active' => false]);
    }

    /**
     * Whether the slide is within its scheduled date window (or has no window set).
     */
    public function getIsWithinScheduleAttribute(): bool
    {
        $today = static::today();

        if ($this->starts_at && $today < $this->starts_at->toDateString()) {
            return false;
        }

        if ($this->ends_at && $today > $this->ends_at->toDateString()) {
            return false;
        }

        return true;
    }

    /**
     * Whether the end date has passed.
     */
    public function getHasEndedAttribute(): bool
    {
        return $this->ends_at !== null && static::today() > $this->ends_at->toDateString();
    }

    /**
     * Whether the slide is actually visible on the site right now:
     * manually active AND (no schedule set, or within the schedule window).
     */
    public function getIsCurrentlyActiveAttribute(): bool
    {
        return $this->is_active && $this->is_within_schedule;
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Slides that are manually active and, if scheduled, within their date window.
     */
    public function scopeCurrentlyActive(Builder $query): void
    {
        $today = static::today();

        $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today));
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
