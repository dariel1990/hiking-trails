<?php

namespace Database\Factories;

use App\Models\CarouselSlide;
use App\Models\CarouselSlideDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarouselSlideDay>
 */
class CarouselSlideDayFactory extends Factory
{
    /**
     * A day in the next two weeks, open 10 AM – 4 PM, on a slide shown on the calendar.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'carousel_slide_id' => fn () => CarouselSlide::create([
                'filename' => fake()->unique()->slug(2).'.webp',
                'caption' => fake()->sentence(3),
                'is_active' => true,
                'show_on_calendar' => true,
                'calendar_date' => now()->addDay()->toDateString(),
            ])->id,
            'date' => now()->addDays(fake()->numberBetween(1, 14))->toDateString(),
            'start_time' => '10:00',
            'end_time' => '16:00',
            'note' => null,
        ];
    }
}
