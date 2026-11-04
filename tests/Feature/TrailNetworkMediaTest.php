<?php

namespace Tests\Feature;

use App\Models\Trail;
use App\Models\TrailFeature;
use App\Models\TrailMedia;
use App\Models\TrailNetwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrailNetworkMediaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: TrailNetwork, 1: Trail}
     */
    private function makeNetworkTrail(): array
    {
        $network = TrailNetwork::create([
            'network_name' => 'Test Network',
            'type' => 'hiking',
            'season' => 'both',
            'latitude' => 54.78,
            'longitude' => -127.17,
            'is_active' => true,
        ]);

        $trail = Trail::create([
            'trail_network_id' => $network->id,
            'name' => 'Network Trail',
            'description' => 'A trail inside a network.',
            'location_type' => 'trail',
            'geometry_type' => 'linestring',
            'status' => 'active',
            'difficulty_level' => 2,
            'distance_km' => 5,
            'elevation_gain_m' => 100,
            'estimated_time_hours' => 2,
            'trail_type' => 'loop',
            'start_coordinates' => [54.78, -127.17],
            'route_coordinates' => [[54.78, -127.17], [54.79, -127.18]],
        ]);

        return [$network, $trail];
    }

    private function addPhoto(Trail $trail, string $path, bool $featured = false, int $sortOrder = 0): TrailMedia
    {
        return TrailMedia::create([
            'trail_id' => $trail->id,
            'media_type' => 'photo',
            'filename' => basename($path),
            'original_name' => basename($path),
            'storage_path' => $path,
            'is_featured' => $featured,
            'sort_order' => $sortOrder,
        ]);
    }

    private function addYoutubeVideo(Trail $trail, string $url): TrailMedia
    {
        return TrailMedia::create([
            'trail_id' => $trail->id,
            'media_type' => 'video_url',
            'filename' => '',
            'original_name' => '',
            'storage_path' => '',
            'video_url' => $url,
            'video_provider' => 'youtube',
        ]);
    }

    private function showNetworkTrail(TrailNetwork $network): Trail
    {
        $response = $this->get(route('trail-networks.show', $network->slug));
        $response->assertOk();

        return $response->viewData('network')->trails->first();
    }

    public function test_featured_photo_is_hero_and_gallery_includes_photos_and_videos(): void
    {
        [$network, $trail] = $this->makeNetworkTrail();
        $this->addPhoto($trail, 'trail-photos/first.jpg', false, 0);
        $this->addPhoto($trail, 'trail-photos/featured.jpg', true, 1);
        $video = $this->addYoutubeVideo($trail, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $shown = $this->showNetworkTrail($network);

        $this->assertSame(Storage::url('trail-photos/featured.jpg'), $shown->preview_photo);
        $this->assertEqualsCanonicalizing(
            [Storage::url('trail-photos/first.jpg'), Storage::url('trail-photos/featured.jpg')],
            array_column($shown->photos, 'url')
        );
        $this->assertCount(1, $shown->videos);
        $this->assertSame($video->id, $shown->videos[0]['id']);
        $this->assertSame('video_url', $shown->videos[0]['media_type']);
        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $shown->videos[0]['url']);
        $this->assertStringContainsString('dQw4w9WgXcQ', $shown->videos[0]['thumbnail_url']);
    }

    public function test_first_photo_is_hero_when_none_is_featured(): void
    {
        [$network, $trail] = $this->makeNetworkTrail();
        $this->addPhoto($trail, 'trail-photos/a.jpg', false, 0);
        $this->addPhoto($trail, 'trail-photos/b.jpg', false, 1);

        $shown = $this->showNetworkTrail($network);

        $this->assertSame(Storage::url('trail-photos/a.jpg'), $shown->preview_photo);
    }

    public function test_highlight_media_is_excluded_from_trail_gallery(): void
    {
        [$network, $trail] = $this->makeNetworkTrail();
        $this->addPhoto($trail, 'trail-photos/general.jpg');
        $highlightPhoto = $this->addPhoto($trail, 'trail-photos/highlight.jpg');
        $highlightVideo = $this->addYoutubeVideo($trail, 'https://www.youtube.com/watch?v=avphbNbJ8NY');

        $feature = TrailFeature::create([
            'trail_id' => $trail->id,
            'feature_type' => 'viewpoint',
            'name' => 'Lookout',
            'coordinates' => [54.78, -127.17],
        ]);
        $feature->media()->attach([$highlightPhoto->id, $highlightVideo->id]);

        $shown = $this->showNetworkTrail($network);

        $this->assertSame([Storage::url('trail-photos/general.jpg')], array_column($shown->photos, 'url'));
        $this->assertSame([], $shown->videos);
    }

    public function test_trail_without_media_has_empty_gallery(): void
    {
        [$network] = $this->makeNetworkTrail();

        $shown = $this->showNetworkTrail($network);

        $this->assertNull($shown->preview_photo);
        $this->assertSame([], $shown->photos);
        $this->assertSame([], $shown->videos);
    }

    public function test_network_page_renders_media_carousel_modal(): void
    {
        [$network] = $this->makeNetworkTrail();

        $this->get(route('trail-networks.show', $network->slug))
            ->assertOk()
            ->assertSee('id="facility-media-modal"', false)
            ->assertSee('id="nmc-hero-grid"', false);
    }
}
