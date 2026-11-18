<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EventDayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One day in an event's schedule, with its own hours (e.g. "Sat, Dec 5 · 10:00 AM – 4:00 PM").
 */
class EventDay extends Model
{
    /** @use HasFactory<EventDayFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'date',
        'start_time',
        'end_time',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

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
