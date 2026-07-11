<?php

namespace Tests\Feature;

use App\Models\TrailNetwork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrailNetworkVideoTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeNetwork(array $overrides = []): TrailNetwork
    {
        return TrailNetwork::create(array_merge([
            'network_name' => 'Test Network',
            'type' => 'hiking',
            'season' => 'both',
            'latitude' => 54.78,
            'longitude' => -127.17,
            'is_active' => true,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function networkPayload(array $overrides = []): array
    {
        return array_merge([
            'network_name' => 'Bluff Trail Network',
            'type' => 'hiking',
            'season' => 'both',
            'latitude' => 54.78,
            'longitude' => -127.17,
        ], $overrides);
    }

    public function test_admin_can_create_network_with_video_url(): void
    {
        $response = $this->actingAs($this->makeAdmin())->post(route('admin.trail-networks.store'), $this->networkPayload([
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]));

        $response->assertRedirect(route('admin.trail-networks.index'));
        $this->assertDatabaseHas('trail_networks', [
            'network_name' => 'Bluff Trail Network',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }

    public function test_admin_can_update_and_clear_network_video_url(): void
    {
        $admin = $this->makeAdmin();
        $network = $this->makeNetwork(['video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);

        $this->actingAs($admin)
            ->put(route('admin.trail-networks.update', $network), $this->networkPayload([
                'network_name' => $network->network_name,
                'video_url' => 'https://vimeo.com/76979871',
            ]))
            ->assertRedirect(route('admin.trail-networks.index'));

        $this->assertSame('https://vimeo.com/76979871', $network->refresh()->video_url);

        $this->actingAs($admin)
            ->put(route('admin.trail-networks.update', $network), $this->networkPayload([
                'network_name' => $network->network_name,
                'video_url' => '',
            ]))
            ->assertRedirect(route('admin.trail-networks.index'));

        $this->assertNull($network->refresh()->video_url);
    }

    public function test_non_youtube_or_vimeo_video_url_is_rejected(): void
    {
        $response = $this->actingAs($this->makeAdmin())->post(route('admin.trail-networks.store'), $this->networkPayload([
            'video_url' => 'https://example.com/video',
        ]));

        $response->assertSessionHasErrors('video_url');
        $this->assertDatabaseCount('trail_networks', 0);
    }

    public function test_public_network_page_shows_video_share_modal_and_og_meta(): void
    {
        $network = $this->makeNetwork(['video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);

        $response = $this->get(route('trail-networks.show', $network->slug));

        $response->assertOk();
        $response->assertSee('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', false);
        $response->assertSee('data-share-open="network-share"', false);
        $response->assertSee('og:title', false);
    }

    public function test_public_network_page_hides_video_block_when_no_video(): void
    {
        $network = $this->makeNetwork();

        $response = $this->get(route('trail-networks.show', $network->slug));

        $response->assertOk();
        $response->assertDontSee('id="network-video"', false);
    }
}
