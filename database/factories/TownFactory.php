<?php

namespace Database\Factories;

use App\Models\Town;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Town>
 */
class TownFactory extends Factory
{
    protected $model = Town::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'name' => $name,
            'slug' => str($name)->slug()->append('-bc')->toString(),
            'province' => 'British Columbia',
            'province_code' => 'BC',
            'latitude' => fake()->randomFloat(7, 54.0, 56.0),
            'longitude' => fake()->randomFloat(7, -130.0, -125.0),
            'radius_km' => 40,
            'map_zoom' => 11,
            'tagline' => fake()->sentence(),
            'intro' => fake()->paragraph(),
            'sort_order' => 0,
            'is_active' => true,
            'is_indexable' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    /**
     * A town pinned to an exact centre, so tests can place trails a known
     * distance away.
     */
    public function at(float $latitude, float $longitude, int $radiusKm = 40): static
    {
        return $this->state(fn () => [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius_km' => $radiusKm,
        ]);
    }
}
