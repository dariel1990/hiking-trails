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
     * Whether the slide is within its scheduled date window (or has no window set).
     */
    public function getIsWithinScheduleAttribute(): bool
    {
        $today = Carbon::today();

        if ($this->starts_at && $today->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $today->gt($this->ends_at)) {
            return false;
        }

        return true;
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
        $today = Carbon::today()->toDateString();

        $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today));
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
