<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventStop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventStop>
 */
class EventStopFactory extends Factory
{
    /**
     * An activity pinned on the map in downtown Smithers.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'latitude' => 54.7804,
            'longitude' => -127.1745,
            'is_roaming' => false,
            'stop_order' => 0,
            'stop_label' => fake()->words(2, true),
            'time_label' => '6:00 – 8:00 PM',
            'description' => fake()->sentence(),
        ];
    }

    /**
     * An activity with no fixed spot (listed on the page, no map pin).
     */
    public function roaming(): static
    {
        return $this->state(fn () => ['latitude' => null, 'longitude' => null, 'is_roaming' => true]);
    }
}
