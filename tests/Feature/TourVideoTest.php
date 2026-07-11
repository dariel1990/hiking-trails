<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourVideoTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeTour(array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'title' => 'Test Tour',
            'tour_type' => 'scenic',
            'is_active' => true,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function tourPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Waterfall Wander',
            'tour_type' => 'waterfalls',
        ], $overrides);
    }

    public function test_admin_can_create_tour_with_video_url(): void
    {
        $response = $this->actingAs($this->makeAdmin())->post(route('admin.tours.store'), $this->tourPayload([
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]));

        $response->assertRedirect(route('admin.tours.index'));
        $this->assertDatabaseHas('tours', [
            'title' => 'Waterfall Wander',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }

    public function test_admin_can_update_and_clear_tour_video_url(): void
    {
        $admin = $this->makeAdmin();
        $tour = $this->makeTour(['video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);

        $this->actingAs($admin)
            ->put(route('admin.tours.update', $tour), $this->tourPayload([
                'title' => $tour->title,
                'tour_type' => $tour->tour_type,
                'video_url' => 'https://vimeo.com/76979871',
            ]))
            ->assertRedirect(route('admin.tours.index'));

        $this->assertSame('https://vimeo.com/76979871', $tour->refresh()->video_url);

        $this->actingAs($admin)
            ->put(route('admin.tours.update', $tour), $this->tourPayload([
                'title' => $tour->title,
                'tour_type' => $tour->tour_type,
                'video_url' => '',
            ]))
            ->assertRedirect(route('admin.tours.index'));

        $this->assertNull($tour->refresh()->video_url);
    }

    public function test_non_youtube_or_vimeo_video_url_is_rejected(): void
    {
        $response = $this->actingAs($this->makeAdmin())->post(route('admin.tours.store'), $this->tourPayload([
            'video_url' => 'https://example.com/video',
        ]));

        $response->assertSessionHasErrors('video_url');
        $this->assertDatabaseCount('tours', 0);
    }

    public function test_video_embed_accessors_for_supported_url_shapes(): void
    {
        $watch = $this->makeTour(['video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);
        $this->assertSame('youtube', $watch->video_provider);
        $this->assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $watch->video_embed_url);
        $this->assertSame('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $watch->video_thumbnail_url);

        $short = $this->makeTour(['video_url' => 'https://youtu.be/dQw4w9WgXcQ']);
        $this->assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $short->video_embed_url);

        $shorts = $this->makeTour(['video_url' => 'https://www.youtube.com/shorts/dQw4w9WgXcQ']);
        $this->assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $shorts->video_embed_url);

        $vimeo = $this->makeTour(['video_url' => 'https://vimeo.com/76979871']);
        $this->assertSame('vimeo', $vimeo->video_provider);
        $this->assertSame('https://player.vimeo.com/video/76979871', $vimeo->video_embed_url);
        $this->assertSame('https://vumbnail.com/76979871.jpg', $vimeo->video_thumbnail_url);

        $none = $this->makeTour();
        $this->assertNull($none->video_provider);
        $this->assertNull($none->video_embed_url);
        $this->assertNull($none->video_thumbnail_url);
    }

    public function test_public_tour_page_shows_video_share_modal_and_og_meta(): void
    {
        $tour = $this->makeTour(['video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);

        $response = $this->get(route('tours.show', $tour));

        $response->assertOk();
        $response->assertSee('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', false);
        $response->assertSee('data-share-open="tour-share"', false);
        $response->assertSee('og:title', false);
    }

    public function test_public_tour_page_hides_video_block_when_no_video(): void
    {
        $tour = $this->makeTour();

        $response = $this->get(route('tours.show', $tour));

        $response->assertOk();
        $response->assertDontSee('id="tour-video"', false);
    }
}
