<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * An event added in the admin, a couple of weeks from now.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(3),
            'description' => fake()->paragraph(),
            'event_date' => now()->addDays(14)->toDateString(),
            'event_time' => '18:00',
            'venue' => 'Bovill Square',
            'location' => 'Smithers, BC',
            'category' => 'Community',
            'theme' => Event::THEME_STANDARD,
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    public function christmas(): static
    {
        return $this->state(fn () => ['theme' => Event::THEME_CHRISTMAS]);
    }

    /**
     * An event imported by the SmithersEvents.com scraper.
     */
    public function imported(): static
    {
        return $this->state(fn () => [
            'source_id' => 'smithersevents-'.fake()->unique()->numberBetween(1, 999999),
            'scraped_at' => now(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function past(): static
    {
        return $this->state(fn () => ['event_date' => now()->subDays(10)->toDateString(), 'end_date' => null]);
    }

    /**
     * A drawn route through downtown Smithers ([lng, lat] pairs).
     */
    public function withRoute(): static
    {
        return $this->state(fn () => [
            'route_coordinates' => [[-127.1745, 54.7804], [-127.1702, 54.7811], [-127.1660, 54.7790]],
            'route_distance_km' => 1.2,
        ]);
    }
}
