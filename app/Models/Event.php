<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'event_time',
        'end_date',
        'end_time',
        'location',
        'venue',
        'organizer',
        'category',
        'image_url',
        'external_url',
        'source_id',
        'is_active',
        'scraped_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'end_date' => 'date',
        'event_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
        'scraped_at' => 'datetime',
    ];

    /**
     * Scope to get upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', Carbon::today())
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
     * Get formatted time
     */
    public function getFormattedTimeAttribute()
    {
        if (!$this->event_time) {
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
            $formatted .= ' at ' . $this->formatted_time;
        }
        
        return $formatted;
    }

    /**
     * Check if event is multi-day
     */
    public function getIsMultiDayAttribute()
    {
        return $this->end_date && !$this->event_date->isSameDay($this->end_date);
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
        if (!$this->end_date) {
            return 1;
        }
        
        return $this->event_date->diffInDays($this->end_date) + 1;
    }
}