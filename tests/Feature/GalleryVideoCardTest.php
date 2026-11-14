<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMedia;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * In the Photos & Videos gallery, the first video is the big card.
 */
class GalleryVideoCardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function layouts(): array
    {
        return [
            'premium' => ['premium'],
            'booking' => ['booking'],
            'minimal' => ['minimal'],
            'editorial' => ['editorial'],
            'corporate' => ['corporate'],
        ];
    }

    private function eventWithPhotosAndVideos(): Event
    {
        $event = Event::factory()->create(['video_url' => null]);
        EventMedia::factory()->for($event)->create(['caption' => 'Trucks lined up', 'sort_order' => 0]);
        EventMedia::factory()->for($event)->create(['caption' => 'Crowds at Bovill Square', 'sort_order' => 1]);
        EventMedia::factory()->for($event)->youtube('mvczV0VFUK4')->create(['caption' => 'Highlights from last year', 'sort_order' => 2]);
        EventMedia::factory()->for($event)->youtube('UAg9ijcBJWY')->create(['caption' => 'Driving the route', 'sort_order' => 3]);

        return $event;
    }

    #[DataProvider('layouts')]
    public function test_the_first_video_is_the_big_gallery_card_in_every_layout(string $layout): void
    {
        Setting::set('event_page_layout', $layout);
        $event = $this->eventWithPhotosAndVideos();

        $html = $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('2 photos · 2 videos')
            ->assertSeeInOrder([
                'aria-label="Play video: Highlights from last year"',
                'aria-label="View photo: Trucks lined up"',
                'aria-label="View photo: Crowds at Bovill Square"',
                'aria-label="Play video: Driving the route"',
            ], false)
            ->getContent();

        $gallery = substr($html, strpos($html, 'id="gallery"'));
        preg_match('~<button[^>]*>~s', substr($gallery, strpos($gallery, '<button')), $firstCard);

        $this->assertStringContainsString('col-span-2 row-span-2', $firstCard[0]);
        $this->assertStringContainsString('mvczV0VFUK4/maxresdefault.jpg', $gallery);
        $this->assertStringContainsString('data-fallback="https://img.youtube.com/vi/mvczV0VFUK4/hqdefault.jpg"', $gallery);
        $this->assertStringContainsString('UAg9ijcBJWY/hqdefault.jpg', $gallery);
        $this->assertSame(1, substr_count($gallery, '</svg>
                        Video
                    </span>'));
    }

    public function test_the_viewer_follows_the_same_order(): void
    {
        $event = $this->eventWithPhotosAndVideos();

        $html = $this->get(route('local-events.show', $event->slug))->assertOk()->getContent();

        $viewerItems = Str::between($html, 'eventLightbox(', 'id="gallery-heading"');
        $positions = array_map(
            fn (string $caption) => strpos($viewerItems, $caption),
            ['Highlights from last year', 'Trucks lined up', 'Crowds at Bovill Square', 'Driving the route'],
        );

        $this->assertNotContains(false, $positions);
        $this->assertSame($positions, collect($positions)->sort()->values()->all());
    }

    public function test_a_photo_only_gallery_keeps_its_layout(): void
    {
        Setting::set('event_page_layout', 'booking');
        $event = Event::factory()->create();
        EventMedia::factory()->for($event)->count(3)->create();

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('aspect-[4/3]', false)
            ->assertDontSee('row-span-2', false)
            ->assertDontSee('maxresdefault.jpg', false);
    }

    public function test_the_events_own_video_stays_where_it_was(): void
    {
        $event = Event::factory()->create(['video_url' => 'https://youtu.be/dQw4w9WgXcQ']);

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('xsVeOpen(', false)
            ->assertDontSee('id="gallery"', false);
    }
}
