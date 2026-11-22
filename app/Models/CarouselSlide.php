<?php

namespace App\Models;

use App\Models\Concerns\HasScheduleDays;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * An image in the /events slideshow. Optionally also an entry on the /events calendar
 * (`show_on_calendar`), titled with its caption unless an admin changes the calendar title,
 * with its own dates, hours, location and details,
 * either one date range or a per-day schedule (`days`), like a local event.
 */
class CarouselSlide extends Model
{
    use HasScheduleDays;

    protected $fillable = [
        'filename',
        'caption',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
        'show_on_calendar',
        'calendar_title',
        'calendar_category',
        'calendar_date',
        'calendar_end_date',
        'calendar_start_time',
        'calendar_end_time',
        'calendar_location',
        'calendar_description',
        'calendar_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'show_on_calendar' => 'boolean',
            'calendar_date' => 'date',
            'calendar_end_date' => 'date',
            'calendar_start_time' => 'datetime',
            'calendar_end_time' => 'datetime',
            'calendar_ended_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Calendar dates moved into the future: it can be switched off again after its new
        // last day. An admin switching a finished slide back on: leave it on.
        static::saving(function (CarouselSlide $slide) {
            if (! $slide->show_on_calendar || ! $slide->calendar_date) {
                return;
            }

            if (! $slide->isCalendarFinished()) {
                $slide->calendar_ended_at = null;
            } elseif ($slide->exists && $slide->is_active && $slide->isDirty('is_active')) {
                $slide->calendar_ended_at ??= now();
            }
        });
    }

    /**
     * Whether the slide's calendar event has had its last day (multi-day events run until
     * their end date / final schedule day).
     */
    public function isCalendarFinished(): bool
    {
        $lastDay = ($this->calendar_end_date ?? $this->calendar_date)?->toDateString();

        return $this->show_on_calendar && $lastDay !== null && $lastDay < static::today();
    }

    /**
     * Last day of the calendar event, e.g. for "Event ended Dec 7".
     */
    public function getCalendarLastDayAttribute(): ?Carbon
    {
        return $this->calendar_end_date ?? $this->calendar_date;
    }

    protected function scheduleStartDateColumn(): string
    {
        return 'calendar_date';
    }

    protected function scheduleEndDateColumn(): string
    {
        return 'calendar_end_date';
    }

    protected function scheduleStartTimeColumn(): string
    {
        return 'calendar_start_time';
    }

    protected function scheduleEndTimeColumn(): string
    {
        return 'calendar_end_time';
    }

    /**
     * Optional per-day calendar schedule (any dates, each with its own hours).
     */
    public function days(): HasMany
    {
        return $this->hasMany(CarouselSlideDay::class)->orderBy('date')->orderBy('start_time');
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
     * Turn off slides that are done, so they read as inactive in the admin too (not just
     * hidden from the site). Returns how many were turned off.
     *
     * - Slides not on the calendar: once their "Show until" date has passed.
     * - Slides on the calendar: once the calendar event's last day has passed (they stay
     *   on the calendar until then, even after leaving the slideshow). Each is turned off
     *   once, so an admin can switch it back on.
     */
    public static function deactivateExpired(): int
    {
        $today = static::today();

        $slideshowEnded = static::query()
            ->where('is_active', true)
            ->where('show_on_calendar', false)
            ->whereNotNull('ends_at')
            ->whereDate('ends_at', '<', $today)
            ->update(['is_active' => false]);

        $calendarEnded = static::query()
            ->where('is_active', true)
            ->where('show_on_calendar', true)
            ->whereNull('calendar_ended_at')
            ->whereNotNull('calendar_date')
            ->where(fn (Builder $q) => $q
                ->whereDate('calendar_end_date', '<', $today)
                ->orWhere(fn (Builder $q) => $q->whereNull('calendar_end_date')->whereDate('calendar_date', '<', $today)))
            ->update(['is_active' => false, 'calendar_ended_at' => now()]);

        return $slideshowEnded + $calendarEnded;
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

    /**
     * Title on the calendar: the slide caption automatically, unless an admin has set
     * a different calendar title.
     */
    public function getCalendarDisplayTitleAttribute(): string
    {
        return $this->calendar_title ?: ($this->caption ?: 'Event');
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

    /**
     * Active slides shown on the /events calendar during the given month. A slide that
     * was switched off after its event finished is hidden until an admin switches it on.
     */
    public function scopeOnCalendarInMonth(Builder $query, int $year, int $month): void
    {
        $query->where('is_active', true)
            ->where('show_on_calendar', true)
            ->whereNotNull('calendar_date')
            ->overlappingMonth($year, $month);
    }

    /**
     * Calendar slides that have not finished yet (multi-day ones stay until their last
     * day), soonest first, for the /events list view.
     */
    public function scopeUpcomingOnCalendar(Builder $query): void
    {
        $today = static::today();

        $query->where('is_active', true)
            ->where('show_on_calendar', true)
            ->whereNotNull('calendar_date')
            ->where(fn (Builder $q) => $q
                ->whereDate('calendar_end_date', '>=', $today)
                ->orWhere(fn (Builder $q) => $q->whereNull('calendar_end_date')->whereDate('calendar_date', '>=', $today)))
            ->orderBy('calendar_date')
            ->orderBy('calendar_start_time');
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * What the /events calendar modal shows for this slide.
     *
     * @return array{id: int, title: string, category: ?string, image_url: string, date_label: string, time_label: ?string, day_times: array<int, array{date: string, short_date: string, weekday: string, time: string}>, location: ?string, description: ?string, url: ?string, google_calendar_url: string}
     */
    public function toCalendarPayload(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->calendar_display_title,
            'category' => $this->calendar_category,
            'image_url' => $this->url,
            'date_label' => $this->scheduleDateRangeLabel(),
            'time_label' => $this->scheduleTimeRangeLabel(),
            'day_times' => $this->scheduleDayTimes()->all(),
            'location' => $this->calendar_location,
            'description' => $this->calendar_description,
            'url' => $this->calendar_url,
            'google_calendar_url' => $this->googleCalendarUrl(),
        ];
    }

    /**
     * "Add to Google Calendar" link for the whole date range (first day's hours).
     */
    private function googleCalendarUrl(): string
    {
        $start = $this->calendar_date;
        $end = $this->calendar_end_date ?? $start;
        $startTime = $this->calendar_start_time?->format('His') ?? '000000';
        $endTime = $this->calendar_end_time?->format('His') ?? '235959';

        return 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            .'&text='.urlencode($this->calendar_display_title)
            .'&dates='.$start->format('Ymd').'T'.$startTime.'/'.$end->format('Ymd').'T'.$endTime
            .'&details='.urlencode($this->calendar_description ?? '')
            .'&location='.urlencode($this->calendar_location ?? '');
    }
}
