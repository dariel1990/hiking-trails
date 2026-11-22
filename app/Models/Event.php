<?php

namespace App\Models;

use App\Models\Concerns\HasScheduleDays;
use App\Models\Concerns\HasUrlKey;
use App\Models\Concerns\HasVideoEmbed;
use App\Support\AccentPalette;
use App\Support\RouteMapPayload;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * A calendar event. Shown on /events (the calendar iframed on xploresmithers.com,
 * looked up by id) and on /local-events/{slug} (the event page with its map).
 * Events are added in the admin; older rows came from the SmithersEvents.com
 * scraper and carry its source_id.
 */
class Event extends Model
{
    use HasFactory;
    use HasScheduleDays;
    use HasUrlKey;
    use HasVideoEmbed;

    public const THEME_STANDARD = 'standard';

    public const THEME_CHRISTMAS = 'christmas';

    protected $fillable = [
        'town_id',
        'title',
        'slug',
        'tagline',
        'description',
        'event_date',
        'event_time',
        'end_date',
        'end_time',
        'location',
        'venue',
        'organizer',
        'category',
        'theme',
        'icon',
        'icon_image',
        'route_coordinates',
        'route_distance_km',
        'image_url',
        'cover_image',
        'video_url',
        'external_url',
        'source_id',
        'is_active',
        'is_featured',
        'scraped_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'end_date' => 'date',
            'event_time' => 'datetime',
            'end_time' => 'datetime',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'auto_deactivated_at' => 'datetime',
            'scraped_at' => 'datetime',
            'route_coordinates' => 'array',
            'route_distance_km' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->slug)) {
                $event->slug = static::uniqueSlug($event->title);
            }
        });

        // Dates moved into the future: it can be switched off again after its new last day.
        // An admin switching a finished event back on: leave it on (don't switch it off again).
        static::saving(function (Event $event) {
            if (! $event->event_date) {
                return;
            }

            if (! $event->isFinished()) {
                $event->auto_deactivated_at = null;
            } elseif ($event->exists && $event->is_active && $event->isDirty('is_active')) {
                $event->auto_deactivated_at ??= now();
            }
        });
    }

    /**
     * Today's date in the site's local timezone (Smithers), as "Y-m-d", so events
     * finish at local midnight after their last day.
     */
    public static function localToday(): string
    {
        return Carbon::now(config('app.local_timezone'))->toDateString();
    }

    /**
     * Switch off active events whose last day (end date, or the start date for one-day
     * events) has passed. Each event is switched off once; an admin can switch it back on.
     * Returns how many were switched off.
     */
    public static function deactivateFinished(): int
    {
        return static::query()
            ->where('is_active', true)
            ->whereNull('auto_deactivated_at')
            ->finished()
            ->update(['is_active' => false, 'auto_deactivated_at' => now()]);
    }

    /**
     * Whether its last day is before today (multi-day events run until their end date).
     */
    public function isFinished(): bool
    {
        $lastDay = ($this->end_date ?? $this->event_date)?->toDateString();

        return $lastDay !== null && $lastDay < static::localToday();
    }

    /**
     * Events whose last day is before today.
     */
    public function scopeFinished(Builder $query): void
    {
        $today = static::localToday();

        $query->where(fn (Builder $q) => $q
            ->whereDate('end_date', '<', $today)
            ->orWhere(fn (Builder $q) => $q->whereNull('end_date')->whereDate('event_date', '<', $today)));
    }

    protected function scheduleStartDateColumn(): string
    {
        return 'event_date';
    }

    protected function scheduleEndDateColumn(): string
    {
        return 'end_date';
    }

    protected function scheduleStartTimeColumn(): string
    {
        return 'event_time';
    }

    protected function scheduleEndTimeColumn(): string
    {
        return 'end_time';
    }

    /**
     * A slug based on the title that no other event uses.
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'event';
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.++$i;
        }

        return $slug;
    }

    /**
     * @return array<string, string>
     */
    public static function getThemes(): array
    {
        return [
            self::THEME_STANDARD => 'Standard',
            self::THEME_CHRISTMAS => '🎄 Christmas',
        ];
    }

    public function stops(): HasMany
    {
        return $this->hasMany(EventStop::class)->orderBy('stop_order');
    }

    /**
     * Optional per-day schedule (any dates, each with its own hours).
     */
    public function days(): HasMany
    {
        return $this->hasMany(EventDay::class)->orderBy('date')->orderBy('start_time');
    }

    /**
     * The event's summary columns for a list of schedule days: first and last date,
     * and the first day's hours. Keeps lists, sorting and filters working unchanged.
     *
     * @param  array<int, array{date: string, start_time?: ?string, end_time?: ?string}>  $days
     * @return array{event_date: string, end_date: ?string, event_time: ?string, end_time: ?string}
     */
    public static function summaryFromDays(array $days): array
    {
        $summary = static::scheduleSummaryFromDays($days);

        return [
            'event_date' => $summary['start_date'],
            'end_date' => $summary['end_date'],
            'event_time' => $summary['start_time'],
            'end_time' => $summary['end_time'],
        ];
    }

    /**
     * Gallery photos and videos, in display order.
     */
    public function media(): HasMany
    {
        return $this->hasMany(EventMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class);
    }

    /**
     * Added in the admin rather than imported by the scraper.
     */
    public function isManual(): bool
    {
        return $this->source_id === null;
    }

    public function isChristmasEvent(): bool
    {
        return $this->theme === self::THEME_CHRISTMAS;
    }

    /**
     * Whether the event page has anything to show on a map.
     */
    public function hasMap(): bool
    {
        return ! empty($this->route_coordinates)
            || $this->stops->contains(fn (EventStop $stop) => ! $stop->is_roaming);
    }

    /**
     * Button, icon and map colour from Settings → Events (Christmas events have their own).
     */
    public function accentColor(): string
    {
        $key = $this->isChristmasEvent() ? 'event_christmas_accent_color' : 'event_accent_color';
        $color = setting($key);

        return AccentPalette::isValidHex($color) ? $color : config("settings.definitions.{$key}.default");
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if ($this->cover_image) {
            return asset('storage/'.$this->cover_image);
        }

        return $this->image_url ?: null;
    }

    public function getIconImageUrlAttribute(): ?string
    {
        return $this->icon_image ? asset('storage/'.$this->icon_image) : null;
    }

    /**
     * Emoji for the event's card and map markers.
     */
    public function getMarkerIconAttribute(): string
    {
        return $this->icon ?: ($this->isChristmasEvent() ? '🎄' : '📍');
    }

    /**
     * Active events that have not finished yet (multi-day events stay until their end date).
     */
    public function scopeCurrent(Builder $query): Builder
    {
        $today = static::localToday();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->where('end_date', '>=', $today)
                ->orWhere(fn ($q) => $q->whereNull('end_date')->where('event_date', '>=', $today)));
    }

    /**
     * Data for the shared route map script.
     *
     * @return array<string, mixed>
     */
    public function toMapPayload(): array
    {
        // Event stops are custom pins, so their details always open in a popup.
        return RouteMapPayload::make(
            stops: $this->stops,
            route: $this->route_coordinates,
            iconImage: $this->icon_image_url,
            iconEmoji: $this->marker_icon,
            isChristmas: $this->isChristmasEvent(),
            showPopups: true,
        );
    }

    /**
     * Active events that have not finished yet, soonest first. Multi-day events stay
     * until their last day, not just their first.
     */
    public function scopeUpcoming($query)
    {
        $today = static::localToday();

        return $query->where(fn (Builder $q) => $q
            ->whereDate('end_date', '>=', $today)
            ->orWhere(fn (Builder $q) => $q->whereNull('end_date')->whereDate('event_date', '>=', $today)))
            ->where('is_active', true)
            ->orderBy('event_date', 'asc')
            ->orderBy('event_time', 'asc');
    }

    /**
     * Scope to get past events
     */
    public function scopePast($query)
    {
        return $query->where('event_date', '<', Carbon::today())
            ->where('is_active', true)
            ->orderBy('event_date', 'desc');
    }

    /**
     * Scope to get today's events
     */
    public function scopeToday($query)
    {
        return $query->whereDate('event_date', Carbon::today())
            ->where('is_active', true)
            ->orderBy('event_time', 'asc');
    }

    /**
     * Scope to get events in a specific month
     */
    public function scopeInMonth($query, $year, $month)
    {
        return $query->whereYear('event_date', $year)
            ->whereMonth('event_date', $month)
            ->where('is_active', true)
            ->orderBy('event_date', 'asc')
            ->orderBy('event_time', 'asc');
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return $this->event_date->format('F j, Y');
    }

    /**
     * Date range for display, e.g. "Dec 5, 2026" or "Dec 5 – Dec 7, 2026".
     */
    public function getDateRangeLabelAttribute(): string
    {
        return $this->scheduleDateRangeLabel();
    }

    /**
     * Time range for display, e.g. "6:00 PM – 8:00 PM"; null without a start time.
     */
    public function getTimeRangeLabelAttribute(): ?string
    {
        return $this->scheduleTimeRangeLabel();
    }

    /**
     * Each day with its own hours, for showing one line per day. Empty when the
     * event has no schedule or every day has the same hours (the time label says it all).
     *
     * @return Collection<int, array{date: string, short_date: string, weekday: string, time: string}>
     */
    public function getDayTimesAttribute(): Collection
    {
        return $this->scheduleDayTimes();
    }

    /**
     * Get formatted time
     */
    public function getFormattedTimeAttribute()
    {
        if (! $this->event_time) {
            return null;
        }

        return Carbon::parse($this->event_time)->format('g:i A');
    }

    /**
     * Get formatted date and time
     */
    public function getFormattedDateTimeAttribute()
    {
        $formatted = $this->formatted_date;

        if ($this->event_time) {
            $formatted .= ' at '.$this->formatted_time;
        }

        return $formatted;
    }

    /**
     * Check if event is multi-day
     */
    public function getIsMultiDayAttribute()
    {
        return $this->end_date && ! $this->event_date->isSameDay($this->end_date);
    }

    /**
     * Check if event has passed
     */
    public function getHasPassedAttribute()
    {
        $endDate = $this->end_date ?? $this->event_date;

        return $endDate->isPast();
    }

    /**
     * Check if event is happening today
     */
    public function getIsHappeningTodayAttribute()
    {
        return $this->event_date->isToday();
    }

    /**
     * Get event duration in days
     */
    public function getDurationInDaysAttribute()
    {
        if (! $this->end_date) {
            return 1;
        }

        return $this->event_date->diffInDays($this->end_date) + 1;
    }
}
