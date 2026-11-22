<?php

namespace App\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Something on the /events calendar with either one date range with one set of hours,
 * or a per-day schedule (`days`, any dates, each with its own hours). Shared by Event
 * and CarouselSlide; the summary columns always span the first to the last day.
 */
trait HasScheduleDays
{
    abstract protected function scheduleStartDateColumn(): string;

    abstract protected function scheduleEndDateColumn(): string;

    abstract protected function scheduleStartTimeColumn(): string;

    abstract protected function scheduleEndTimeColumn(): string;

    /**
     * Whether there is a per-day schedule rather than one date range with one set of hours.
     */
    public function hasSchedule(): bool
    {
        return $this->days->isNotEmpty();
    }

    /**
     * The summary columns for a list of schedule days: first and last date, and the
     * first day's hours. Keeps lists, sorting and filters working unchanged.
     *
     * @param  array<int, array{date: string, start_time?: ?string, end_time?: ?string}>  $days
     * @return array{start_date: string, end_date: ?string, start_time: ?string, end_time: ?string}
     */
    public static function scheduleSummaryFromDays(array $days): array
    {
        $sorted = collect($days)->sortBy('date')->values();
        $first = $sorted->first();
        $last = $sorted->last();

        return [
            'start_date' => $first['date'],
            'end_date' => $last['date'] !== $first['date'] ? $last['date'] : null,
            'start_time' => $first['start_time'] ?? null,
            'end_time' => $first['end_time'] ?? null,
        ];
    }

    /**
     * Rows whose first-to-last-day span overlaps the given month.
     */
    public function scopeOverlappingMonth(Builder $query, int $year, int $month): void
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();
        $startColumn = $this->scheduleStartDateColumn();
        $endColumn = $this->scheduleEndDateColumn();

        $query->whereDate($startColumn, '<=', $end->toDateString())
            ->where(fn (Builder $q) => $q
                ->whereDate($endColumn, '>=', $start->toDateString())
                ->orWhere(fn (Builder $q) => $q->whereNull($endColumn)->whereDate($startColumn, '>=', $start->toDateString())));
    }

    /**
     * Whether it happens on the given date: only on its schedule days when it has a
     * schedule (gaps skipped), otherwise every day from the start to the end date.
     */
    public function occursOn(Carbon $date): bool
    {
        if ($this->hasSchedule()) {
            return $this->days->contains(fn (Model $day) => $day->date->isSameDay($date));
        }

        $start = $this->scheduleStartDate();
        if (! $start) {
            return false;
        }

        $end = $this->scheduleEndDate() ?? $start;

        return $date->copy()->startOfDay()->betweenIncluded($start->copy()->startOfDay(), $end->copy()->startOfDay());
    }

    /**
     * Hours on the given date, e.g. "10 AM – 4 PM"; null when it has no times that day.
     */
    public function timeLabelOn(Carbon $date): ?string
    {
        if ($this->hasSchedule()) {
            $labels = $this->days
                ->filter(fn (Model $day) => $day->date->isSameDay($date))
                ->map(fn (Model $day) => $day->compact_time_label)
                ->filter();

            return $labels->isNotEmpty() ? $labels->implode(', ') : null;
        }

        $start = $this->scheduleStartTime();
        if (! $start) {
            return null;
        }

        $format = fn (Carbon $time) => $time->format($time->minute === 0 ? 'g A' : 'g:i A');
        $end = $this->scheduleEndTime();

        return $format($start).($end ? ' – '.$format($end) : '');
    }

    /**
     * Date range for display, e.g. "Saturday, Dec 5, 2026", "Dec 5 – Dec 7, 2026" or
     * "Dec 5, 12 & 19, 2026" for a few separate schedule days.
     */
    public function scheduleDateRangeLabel(): string
    {
        if ($this->hasSchedule() && $this->days->count() > 1 && $this->days->count() <= 4 && ! $this->scheduleIsConsecutive()) {
            return $this->listedDatesLabel();
        }

        $start = $this->scheduleStartDate();
        $end = $this->scheduleEndDate();

        if (! $end || $start->isSameDay($end)) {
            return $start->format('l, M j, Y');
        }

        $sameYear = $start->year === $end->year;

        return $start->format($sameYear ? 'M j' : 'M j, Y').' – '.$end->format('M j, Y');
    }

    /**
     * Time range for display, e.g. "6:00 PM – 8:00 PM"; null without a start time.
     * With different hours each day: "Nov 21: 7 PM – 9 PM, Nov 22: 2 PM – 6:30 PM".
     */
    public function scheduleTimeRangeLabel(): ?string
    {
        if ($this->hasSchedule()) {
            $hours = $this->days->map(fn (Model $day) => $day->hoursKey())->unique();

            if ($hours->count() === 1) {
                return $this->days->first()->time_range_label;
            }

            return $this->scheduleDayTimes()
                ->map(fn (array $day) => $day['short_date'].': '.$day['time'])
                ->implode(', ');
        }

        $start = $this->scheduleStartTime();
        if (! $start) {
            return null;
        }

        $end = $this->scheduleEndTime();

        return $start->format('g:i A').($end ? ' – '.$end->format('g:i A') : '');
    }

    /**
     * Each day with its own hours, for showing one line per day. Empty when there is no
     * schedule or every day has the same hours (the time label says it all).
     *
     * @return Collection<int, array{date: string, short_date: string, weekday: string, time: string}>
     */
    public function scheduleDayTimes(): Collection
    {
        if (! $this->hasSchedule() || $this->days->map(fn (Model $day) => $day->hoursKey())->unique()->count() === 1) {
            return collect();
        }

        return $this->days->map(fn (Model $day) => [
            'date' => $day->date->toDateString(),
            'short_date' => $day->date->format('M j'),
            'weekday' => $day->date->format('D'),
            'time' => $day->compact_time_label ?? 'All day',
        ])->values();
    }

    protected function scheduleStartDate(): ?Carbon
    {
        return $this->{$this->scheduleStartDateColumn()};
    }

    protected function scheduleEndDate(): ?Carbon
    {
        return $this->{$this->scheduleEndDateColumn()};
    }

    protected function scheduleStartTime(): ?Carbon
    {
        $time = $this->{$this->scheduleStartTimeColumn()};

        return $time ? Carbon::parse($time) : null;
    }

    protected function scheduleEndTime(): ?Carbon
    {
        $time = $this->{$this->scheduleEndTimeColumn()};

        return $time ? Carbon::parse($time) : null;
    }

    /**
     * Whether the schedule days follow each other with no gaps.
     */
    private function scheduleIsConsecutive(): bool
    {
        $dates = $this->days->pluck('date')->unique(fn (Carbon $date) => $date->toDateString())->values();

        return $dates->every(fn (Carbon $date, int $i) => $i === 0 || $dates[$i - 1]->copy()->addDay()->isSameDay($date));
    }

    /**
     * Listed schedule dates, e.g. "Dec 5, 12 & 19, 2026" or "Nov 28, Dec 5 & 12, 2026".
     */
    private function listedDatesLabel(): string
    {
        $dates = $this->days->pluck('date')->unique(fn (Carbon $date) => $date->toDateString())->values();
        $last = $dates->last();
        $parts = $dates->map(function (Carbon $date, int $i) use ($dates, $last) {
            $previous = $i > 0 ? $dates[$i - 1] : null;
            $format = $previous && $previous->isSameMonth($date) ? 'j' : 'M j';

            return $date->format($date->year !== $last->year ? $format.', Y' : $format);
        });

        return $parts->slice(0, -1)->implode(', ').' & '.$parts->last().', '.$last->year;
    }
}
