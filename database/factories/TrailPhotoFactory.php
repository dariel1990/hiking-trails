<?php

namespace Database\Factories;

use App\Models\Trail;
use App\Models\TrailPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrailPhoto>
 */
class TrailPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $trailId = Trail::query()->inRandomOrder()->value('id');
        $uuid = (string) Str::uuid();

        return [
            'trail_id' => $trailId ?? Trail::factory(),
            'image_path' => "trail-photos/{$trailId}/{$uuid}.webp",
            'thumbnail_path' => "trail-photos/{$trailId}/thumbs/{$uuid}.webp",
            'caption' => fake()->optional()->sentence(),
            'name' => fake()->optional()->name(),
            'email' => fake()->safeEmail(),
            'submitter_ip' => fake()->ipv4(),
            'status' => TrailPhoto::STATUS_PENDING,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => TrailPhoto::STATUS_PENDING,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => TrailPhoto::STATUS_APPROVED,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => TrailPhoto::STATUS_REJECTED,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }
}
