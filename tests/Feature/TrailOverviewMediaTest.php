<?php

namespace Tests\Feature;

use App\Models\ActivityType;
use App\Models\Trail;
use App\Models\TrailMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TrailOverviewMediaTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER_UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 Safari/605.1.15';

    private function makeTrail(array $attributes = []): Trail
    {
        return Trail::create(array_merge([
            'name' => 'Ganokwa Falls',
            'description' => 'Unique overview description text.',
            'location_type' => 'trail',
            'geometry_type' => 'linestring',
            'status' => 'active',
            'difficulty_level' => 3,
            'distance_km' => 2.9,
            'elevation_gain_m' => 154,
            'estimated_time_hours' => 1.5,
            'trail_type' => 'out-and-back',
            'start_coordinates' => [54.8, -126.9],
        ], $attributes));
    }

    private function addPhoto(Trail $trail, string $filename, int $sortOrder, bool $featured = false): TrailMedia
    {
        return TrailMedia::create([
            'trail_id' => $trail->id,
            'media_type' => 'photo',
            'filename' => $filename,
            'original_name' => $filename,
            'storage_path' => 'trail-photos/'.$filename,
            'is_featured' => $featured,
            'sort_order' => $sortOrder,
        ]);
    }

    private function addYoutubeVideo(Trail $trail, int $sortOrder): TrailMedia
    {
        return TrailMedia::create([
            'trail_id' => $trail->id,
            'media_type' => 'video_url',
            'filename' => 'video',
            'original_name' => 'video',
            'storage_path' => '',
            'video_url' => 'https://www.youtube.com/watch?v=abc123XYZ00',
            'video_provider' => 'youtube',
            'sort_order' => $sortOrder,
        ]);
    }

    private function viewTrail(Trail $trail): TestResponse
    {
        return $this->withHeader('User-Agent', self::BROWSER_UA)->get('/trails/'.$trail->slug);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function fishingActivity(): ActivityType
    {
        return ActivityType::create(['name' => 'Fishing', 'slug' => 'fishing', 'icon' => '🎣', 'color' => '#3b82f6', 'is_active' => true]);
    }

    public function test_new_trails_default_to_showing_media_at_the_top(): void
    {
        $trail = $this->makeTrail();

        $this->assertSame('top', $trail->fresh()->overview_media_position);
    }

    public function test_media_is_shown_above_the_description_by_default(): void
    {
        $trail = $this->makeTrail();
        $this->addPhoto($trail, 'first.jpg', 0);

        $this->viewTrail($trail)
            ->assertOk()
            ->assertSeeInOrder(['data-overview-media', 'first.jpg', 'About This Trail', 'Unique overview description text.'], false);
    }

    public function test_the_featured_photo_and_first_video_sit_side_by_side_and_keep_their_carousel_index(): void
    {
        $trail = $this->makeTrail();
        $this->addPhoto($trail, 'first.jpg', 0);
        $this->addYoutubeVideo($trail, 1);
        $this->addPhoto($trail, 'featured.jpg', 2, featured: true);

        $this->viewTrail($trail)
            ->assertOk()
            ->assertSeeInOrder([
                'data-overview-media',
                'md:grid-cols-2',
                "openMediaCarousel('trail-general', 2)",
                'featured.jpg',
                "openMediaCarousel('trail-general', 1)",
                'img.youtube.com/vi/abc123XYZ00',
                "openMediaCarousel('trail-general', 0)",
                'first.jpg',
                'About This Trail',
            ], false)
            ->assertSee('View all 3', false);
    }

    public function test_the_featured_photo_spans_full_width_when_there_is_no_video(): void
    {
        $trail = $this->makeTrail();
        $this->addPhoto($trail, 'featured.jpg', 0, featured: true);
        $this->addPhoto($trail, 'second.jpg', 1);

        $response = $this->viewTrail($trail)->assertOk();

        $block = str($response->getContent())->after('data-overview-media')->before('About This Trail');
        $this->assertStringNotContainsString('md:grid-cols-2', $block);
        $this->assertStringContainsString('second.jpg', $block);
    }

    public function test_only_five_items_are_shown_with_a_count_of_the_rest(): void
    {
        $trail = $this->makeTrail();
        foreach (range(0, 6) as $i) {
            $this->addPhoto($trail, "photo-{$i}.jpg", $i, featured: $i === 0);
        }

        $response = $this->viewTrail($trail)->assertOk()->assertSee('View all 7', false)->assertSee('+2', false);

        $block = str($response->getContent())->after('data-overview-media')->before('About This Trail');
        $this->assertStringContainsString('photo-4.jpg', $block);
        $this->assertStringNotContainsString('photo-5.jpg', $block);
    }

    public function test_new_trails_default_to_the_gallery_layout(): void
    {
        $this->assertSame('gallery', $this->makeTrail()->fresh()->overview_media_layout);
    }

    public function test_featured_only_layout_shows_just_the_featured_image(): void
    {
        $trail = $this->makeTrail(['overview_media_layout' => 'featured_only']);
        $this->addPhoto($trail, 'first.jpg', 0);
        $this->addYoutubeVideo($trail, 1);
        $this->addPhoto($trail, 'featured.jpg', 2, featured: true);

        $response = $this->viewTrail($trail)->assertOk()->assertSee('View all 3', false);

        $block = str($response->getContent())->after('data-overview-media')->before('About This Trail');
        $this->assertStringContainsString('featured.jpg', $block);
        $this->assertStringNotContainsString('first.jpg', $block);
        $this->assertStringNotContainsString('img.youtube.com', $block);
        $this->assertStringNotContainsString('md:grid-cols-2', $block);
    }

    public function test_featured_video_layout_shows_the_featured_image_and_video_without_thumbnails(): void
    {
        $trail = $this->makeTrail(['overview_media_layout' => 'featured_video']);
        $this->addPhoto($trail, 'first.jpg', 0);
        $this->addYoutubeVideo($trail, 1);
        $this->addPhoto($trail, 'featured.jpg', 2, featured: true);

        $response = $this->viewTrail($trail)->assertOk()->assertSee('View all 3', false);

        $block = str($response->getContent())->after('data-overview-media')->before('About This Trail');
        $this->assertStringContainsString('md:grid-cols-2', $block);
        $this->assertStringContainsString('featured.jpg', $block);
        $this->assertStringContainsString('img.youtube.com/vi/abc123XYZ00', $block);
        $this->assertStringNotContainsString('first.jpg', $block);
    }

    public function test_admin_can_change_the_layout_and_rejects_invalid_values(): void
    {
        $trail = $this->makeTrail([
            'name' => 'Sunset Lake',
            'location_type' => 'fishing_lake',
            'geometry_type' => 'point',
        ]);
        $payload = [
            'name' => 'Sunset Lake',
            'location_type' => 'fishing_lake',
            'status' => 'active',
            'point_latitude' => 54.78,
            'point_longitude' => -127.17,
            'activity_id' => $this->fishingActivity()->id,
        ];
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.trails.update', $trail), $payload + ['overview_media_layout' => 'featured_only'])
            ->assertSessionHasNoErrors();

        $this->assertSame('featured_only', $trail->fresh()->overview_media_layout);

        $this->actingAs($admin)
            ->put(route('admin.trails.update', $trail), $payload + ['overview_media_layout' => 'carousel'])
            ->assertSessionHasErrors('overview_media_layout');
    }

    public function test_media_can_be_placed_below_the_description(): void
    {
        $trail = $this->makeTrail(['overview_media_position' => 'below_description']);
        $this->addPhoto($trail, 'first.jpg', 0);

        $this->viewTrail($trail)
            ->assertOk()
            ->assertSeeInOrder(['About This Trail', 'Unique overview description text.', 'data-overview-media', 'first.jpg'], false);
    }

    public function test_media_can_be_hidden_from_the_overview(): void
    {
        $trail = $this->makeTrail(['overview_media_position' => 'hidden']);
        $this->addPhoto($trail, 'first.jpg', 0);

        $this->viewTrail($trail)
            ->assertOk()
            ->assertDontSee('data-overview-media', false)
            ->assertSee('first.jpg', false);
    }

    public function test_nothing_is_shown_when_the_trail_has_no_media(): void
    {
        $trail = $this->makeTrail();

        $this->viewTrail($trail)
            ->assertOk()
            ->assertDontSee('data-overview-media', false);
    }

    public function test_admin_can_choose_the_position_when_creating(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.trails.store'), [
                'name' => 'Sunset Lake',
                'location_type' => 'fishing_lake',
                'status' => 'active',
                'point_latitude' => 54.78,
                'point_longitude' => -127.17,
                'activity_id' => $this->fishingActivity()->id,
                'overview_media_position' => 'below_description',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('below_description', Trail::where('name', 'Sunset Lake')->value('overview_media_position'));
    }

    public function test_admin_can_change_the_position_when_updating(): void
    {
        $trail = $this->makeTrail([
            'name' => 'Sunset Lake',
            'location_type' => 'fishing_lake',
            'geometry_type' => 'point',
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.trails.update', $trail), [
                'name' => 'Sunset Lake',
                'location_type' => 'fishing_lake',
                'status' => 'active',
                'point_latitude' => 54.78,
                'point_longitude' => -127.17,
                'activity_id' => $this->fishingActivity()->id,
                'overview_media_position' => 'hidden',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('hidden', $trail->fresh()->overview_media_position);
    }

    public function test_an_invalid_position_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.trails.store'), [
                'name' => 'Sunset Lake',
                'location_type' => 'fishing_lake',
                'status' => 'active',
                'point_latitude' => 54.78,
                'point_longitude' => -127.17,
                'activity_id' => $this->fishingActivity()->id,
                'overview_media_position' => 'sidebar',
            ])
            ->assertSessionHasErrors('overview_media_position');

        $this->assertDatabaseMissing('trails', ['name' => 'Sunset Lake']);
    }
}
