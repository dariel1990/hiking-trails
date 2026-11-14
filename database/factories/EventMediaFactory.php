<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventMedia>
 */
class EventMediaFactory extends Factory
{
    /**
     * A gallery photo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'media_type' => EventMedia::TYPE_PHOTO,
            'file_path' => 'events/1/gallery/'.fake()->uuid().'.webp',
            'thumbnail_path' => null,
            'caption' => fake()->sentence(4),
            'sort_order' => 0,
        ];
    }

    public function youtube(string $id = 'mvczV0VFUK4'): static
    {
        return $this->state(fn () => [
            'media_type' => EventMedia::TYPE_VIDEO,
            'file_path' => null,
            'url' => 'https://www.youtube.com/watch?v='.$id,
            'video_provider' => 'youtube',
        ]);
    }
}
