<?php

namespace App\Models;

use App\Models\Concerns\HasDayHours;
use Database\Factories\CarouselSlideDayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One day in a carousel slide's calendar schedule, with its own hours.
 */
class CarouselSlideDay extends Model
{
    use HasDayHours;

    /** @use HasFactory<CarouselSlideDayFactory> */
    use HasFactory;

    protected $fillable = [
        'carousel_slide_id',
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

    public function slide(): BelongsTo
    {
        return $this->belongsTo(CarouselSlide::class, 'carousel_slide_id');
    }
}
