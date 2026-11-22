<?php

namespace App\Models;

use App\Models\Concerns\HasDayHours;
use Database\Factories\EventDayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One day in an event's schedule, with its own hours (e.g. "Sat, Dec 5 · 10:00 AM – 4:00 PM").
 */
class EventDay extends Model
{
    use HasDayHours;

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
}
