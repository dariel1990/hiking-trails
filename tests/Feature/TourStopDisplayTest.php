<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TourStop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourStopDisplayTest extends TestCase
{
    use RefreshDatabase;

    private function makeTourWithStop(array $stopOverrides = []): Tour
    {
        $tour = Tour::create([
            'title' => 'Heritage Tour',
            'tour_type' => 'scenic',
            'is_active' => true,
        ]);

        TourStop::create(array_merge([
            'tour_id' => $tour->id,
            'stop_order' => 1,
            'stop_label' => 'Central Park Building',
            'latitude' => 54.78,
            'longitude' => -127.17,
        ], $stopOverrides));

        return $tour;
    }

    public function test_long_stop_description_is_rendered_in_full_with_read_more_toggle(): void
    {
        $ending = 'This is the final sentence of a very long description.';
        $description = str_repeat('Built in 1925, it housed the Government Agent office. ', 30).$ending;

        $tour = $this->makeTourWithStop(['description' => $description]);

        $this->get(route('tours.show', $tour))
            ->assertOk()
            ->assertSee($ending)
            ->assertSee('class="stop-description ', false)
            ->assertSee('Read more');
    }

    public function test_stop_image_is_larger_and_opens_full_size_viewer(): void
    {
        $tour = $this->makeTourWithStop(['image' => 'tour-stops/central-park.jpg']);

        $this->get(route('tours.show', $tour))
            ->assertOk()
            ->assertSee('data-photos="'.e(json_encode([asset('storage/tour-stops/central-park.jpg')])).'"', false)
            ->assertSee('openStopImage(this)', false)
            ->assertSee('stop-image-viewer', false)
            ->assertSee('aspect-[16/9]', false);
    }

    public function test_stop_without_image_or_description_renders_without_viewer_trigger(): void
    {
        $tour = $this->makeTourWithStop();

        $this->get(route('tours.show', $tour))
            ->assertOk()
            ->assertSee('Central Park Building')
            ->assertDontSee('data-photos', false)
            ->assertDontSee('class="stop-description ', false);
    }

    public function test_roaming_stop_image_is_clickable(): void
    {
        $tour = $this->makeTourWithStop([
            'is_roaming' => true,
            'latitude' => null,
            'longitude' => null,
            'image' => 'tour-stops/roaming.jpg',
        ]);

        $this->get(route('tours.show', $tour))
            ->assertOk()
            ->assertSee('data-photos="'.e(json_encode([asset('storage/tour-stops/roaming.jpg')])).'"', false);
    }
}
