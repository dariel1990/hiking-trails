<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventDay>
 */
class EventDayFactory extends Factory
{
    /**
     * A day in the next two weeks, open 10 AM – 4 PM.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'date' => now()->addDays(fake()->numberBetween(1, 14))->toDateString(),
            'start_time' => '10:00',
            'end_time' => '16:00',
            'note' => null,
        ];
    }
}
