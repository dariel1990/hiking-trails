<?php

namespace Tests\Feature;

use App\Models\Trail;
use App\Models\TrailNetwork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrailNetworkIconTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

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
            'icon' => '🥾',
            'latitude' => 54.78,
            'longitude' => -127.17,
        ], $overrides);
    }

    public function test_admin_can_upload_network_icon(): void
    {
        $response = $this->actingAs($this->makeAdmin())->postJson(route('admin.trail-networks.icons.upload'), [
            'icon' => UploadedFile::fake()->image('marker.png', 512, 512),
        ]);

        $response->assertOk()->assertJsonStructure(['path', 'url']);
        $this->assertStringStartsWith('trail-network-icons/', $response->json('path'));
        Storage::disk('public')->assertExists($response->json('path'));
    }

    public function test_icon_upload_rejects_non_images(): void
    {
        $this->actingAs($this->makeAdmin())->postJson(route('admin.trail-networks.icons.upload'), [
            'icon' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
        ])->assertUnprocessable();
    }

    public function test_admin_can_list_uploaded_network_icons(): void
    {
        Storage::disk('public')->put('trail-network-icons/a.webp', 'x');
        Storage::disk('public')->put('trail-network-icons/readme.txt', 'x');

        $this->actingAs($this->makeAdmin())->getJson(route('admin.trail-networks.icons'))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.path', 'trail-network-icons/a.webp');
    }

    public function test_non_admin_cannot_use_icon_endpoints(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->getJson(route('admin.trail-networks.icons'))->assertUnauthorized();
        $this->actingAs($user)->postJson(route('admin.trail-networks.icons.upload'), [
            'icon' => UploadedFile::fake()->image('marker.png'),
        ])->assertUnauthorized();
    }

    public function test_admin_can_create_and_update_network_with_icon_image(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('admin.trail-networks.store'), $this->networkPayload([
            'icon_image' => 'trail-network-icons/first.webp',
        ]))->assertRedirect(route('admin.trail-networks.index'));

        $network = TrailNetwork::where('network_name', 'Bluff Trail Network')->firstOrFail();
        $this->assertSame('trail-network-icons/first.webp', $network->icon_image);
        $this->assertSame('🥾', $network->icon);

        $this->actingAs($admin)->put(route('admin.trail-networks.update', $network), $this->networkPayload([
            'icon_image' => '',
        ]))->assertRedirect(route('admin.trail-networks.index'));

        $this->assertNull($network->fresh()->icon_image);
    }

    public function test_icon_image_outside_network_icon_folder_is_rejected(): void
    {
        $this->actingAs($this->makeAdmin())->post(route('admin.trail-networks.store'), $this->networkPayload([
            'icon_image' => 'trail-icons/../../.env',
        ]))->assertStatus(422);

        $this->assertDatabaseMissing('trail_networks', ['network_name' => 'Bluff Trail Network']);
    }

    public function test_deleting_icon_in_use_requires_force_and_clears_networks(): void
    {
        $admin = $this->makeAdmin();
        Storage::disk('public')->put('trail-network-icons/used.webp', 'x');
        $network = $this->makeNetwork(['icon_image' => 'trail-network-icons/used.webp']);

        $this->actingAs($admin)->deleteJson(route('admin.trail-networks.icons.delete'), [
            'path' => 'trail-network-icons/used.webp',
        ])->assertOk()->assertJson(['deleted' => false, 'in_use' => 1]);

        Storage::disk('public')->assertExists('trail-network-icons/used.webp');

        $this->actingAs($admin)->deleteJson(route('admin.trail-networks.icons.delete'), [
            'path' => 'trail-network-icons/used.webp',
            'force' => true,
        ])->assertOk()->assertJson(['deleted' => true]);

        Storage::disk('public')->assertMissing('trail-network-icons/used.webp');
        $this->assertNull($network->fresh()->icon_image);
    }

    public function test_deleting_icon_outside_network_icon_folder_is_rejected(): void
    {
        $this->actingAs($this->makeAdmin())->deleteJson(route('admin.trail-networks.icons.delete'), [
            'path' => 'trail-icons/other.webp',
        ])->assertStatus(422);
    }

    public function test_map_api_exposes_network_icon_image_url(): void
    {
        $network = $this->makeNetwork(['icon_image' => 'trail-network-icons/map.webp']);
        Trail::create([
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

        $this->getJson('/api/trail-networks')
            ->assertOk()
            ->assertJsonPath('0.icon_image_url', asset('storage/trail-network-icons/map.webp'));
    }

    public function test_create_and_edit_pages_render_icon_picker(): void
    {
        $admin = $this->makeAdmin();
        $network = $this->makeNetwork(['icon_image' => 'trail-network-icons/edit.webp']);

        $this->actingAs($admin)->get(route('admin.trail-networks.create'))
            ->assertOk()
            ->assertSee('networkIconGallery', false);

        $this->actingAs($admin)->get(route('admin.trail-networks.edit', $network))
            ->assertOk()
            ->assertSee("iconPicker('', null, 'trail-network-icons\\/edit.webp')", false);
    }
}
