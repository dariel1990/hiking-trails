<?php

namespace App\Models\Concerns;

use Carbon\Carbon;

/**
 * Hours labels for one day in a schedule (`date`, `start_time`, `end_time`), shared by
 * EventDay and CarouselSlideDay.
 */
trait HasDayHours
{
    /**
     * Hours for display, e.g. "10:00 AM – 4:00 PM"; null without a start time.
     */
    public function getTimeRangeLabelAttribute(): ?string
    {
        if (! $this->start_time) {
            return null;
        }

        $label = Carbon::parse($this->start_time)->format('g:i A');

        if ($this->end_time) {
            $label .= ' – '.Carbon::parse($this->end_time)->format('g:i A');
        }

        return $label;
    }

    /**
     * Short hours for tight spaces, e.g. "8 PM – 11:30 PM" (minutes only when not :00);
     * null without a start time.
     */
    public function getCompactTimeLabelAttribute(): ?string
    {
        if (! $this->start_time) {
            return null;
        }

        $format = fn (Carbon $time) => $time->format($time->minute === 0 ? 'g A' : 'g:i A');

        return $format($this->start_time).($this->end_time ? ' – '.$format($this->end_time) : '');
    }

    /**
     * Start/end as "H:i" for comparing days and for the admin form.
     */
    public function hoursKey(): string
    {
        return ($this->start_time?->format('H:i') ?? '').'-'.($this->end_time?->format('H:i') ?? '');
    }
}
