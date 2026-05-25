<?php

namespace Tests\Feature\Admin;

use App\Models\Trail;
use App\Models\TrailPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrailPhotoModerationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function standardUser(): User
    {
        return User::factory()->create(['is_admin' => false]);
    }

    private function makeTrail(): Trail
    {
        return Trail::create([
            'name' => 'Test Trail',
            'description' => 'Trail for moderation tests.',
            'difficulty_level' => 2.0,
            'distance_km' => 5.5,
            'elevation_gain_m' => 150,
            'estimated_time_hours' => 1.5,
            'trail_type' => 'loop',
            'start_coordinates' => [54.78, -127.17],
        ]);
    }

    public function test_guest_cannot_view_moderation_index(): void
    {
        $this->get(route('admin.trail-photos.index'))
            ->assertRedirect('/login');
    }

    public function test_non_admin_user_cannot_view_moderation_index(): void
    {
        $this->actingAs($this->standardUser())
            ->get(route('admin.trail-photos.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_sees_pending_photos_by_default(): void
    {
        $trail = $this->makeTrail();
        TrailPhoto::factory()->for($trail)->pending()->create(['caption' => 'Pending caption']);
        TrailPhoto::factory()->for($trail)->approved()->create(['caption' => 'Approved caption']);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.trail-photos.index'));

        $response->assertOk();
        $response->assertSee('Pending caption');
        $response->assertDontSee('Approved caption');
    }

    public function test_admin_can_filter_by_status(): void
    {
        $trail = $this->makeTrail();
        TrailPhoto::factory()->for($trail)->pending()->create(['caption' => 'Pending only']);
        TrailPhoto::factory()->for($trail)->approved()->create(['caption' => 'Approved only']);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.trail-photos.index', ['status' => 'approved']));

        $response->assertOk();
        $response->assertSee('Approved only');
        $response->assertDontSee('Pending only');
    }

    public function test_admin_can_approve_a_photo(): void
    {
        $admin = $this->admin();
        $trail = $this->makeTrail();
        $photo = TrailPhoto::factory()->for($trail)->pending()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.trail-photos.index'))
            ->patch(route('admin.trail-photos.update', $photo), ['status' => 'approved']);

        $response->assertRedirect(route('admin.trail-photos.index'));

        $photo->refresh();
        $this->assertSame(TrailPhoto::STATUS_APPROVED, $photo->status);
        $this->assertSame($admin->id, $photo->reviewed_by);
        $this->assertNotNull($photo->reviewed_at);
    }

    public function test_rejecting_a_photo_deletes_files_but_keeps_row(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $trail = $this->makeTrail();

        Storage::disk('public')->put('trail-photos/1/full.webp', 'fake');
        Storage::disk('public')->put('trail-photos/1/thumbs/full.webp', 'fake');

        $photo = TrailPhoto::factory()->for($trail)->pending()->create([
            'image_path' => 'trail-photos/1/full.webp',
            'thumbnail_path' => 'trail-photos/1/thumbs/full.webp',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.trail-photos.update', $photo), ['status' => 'rejected'])
            ->assertRedirect();

        $photo->refresh();
        $this->assertSame(TrailPhoto::STATUS_REJECTED, $photo->status);
        $this->assertNull($photo->image_path);
        $this->assertNull($photo->thumbnail_path);
        $this->assertSame($admin->id, $photo->reviewed_by);

        Storage::disk('public')->assertMissing('trail-photos/1/full.webp');
        Storage::disk('public')->assertMissing('trail-photos/1/thumbs/full.webp');

        $this->assertDatabaseHas('trail_photos', ['id' => $photo->id]);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $admin = $this->admin();
        $trail = $this->makeTrail();
        $photo = TrailPhoto::factory()->for($trail)->pending()->create();

        $this->actingAs($admin)
            ->patch(route('admin.trail-photos.update', $photo), ['status' => 'hijacked'])
            ->assertStatus(302)
            ->assertSessionHasErrors('status');

        $this->assertSame(TrailPhoto::STATUS_PENDING, $photo->refresh()->status);
    }

    public function test_admin_can_permanently_delete_a_photo(): void
    {
        $trail = $this->makeTrail();
        $photo = TrailPhoto::factory()->for($trail)->approved()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.trail-photos.destroy', $photo))
            ->assertRedirect();

        $this->assertDatabaseMissing('trail_photos', ['id' => $photo->id]);
    }

    public function test_bulk_approve_updates_multiple_photos(): void
    {
        $admin = $this->admin();
        $trail = $this->makeTrail();
        $photos = TrailPhoto::factory()->count(3)->for($trail)->pending()->create();

        $this->actingAs($admin)
            ->post(route('admin.trail-photos.bulk'), [
                'photo_ids' => $photos->pluck('id')->all(),
                'status' => 'approved',
            ])
            ->assertRedirect();

        foreach ($photos as $photo) {
            $this->assertSame(TrailPhoto::STATUS_APPROVED, $photo->refresh()->status);
            $this->assertSame($admin->id, $photo->reviewed_by);
        }
    }

    public function test_non_admin_cannot_approve(): void
    {
        $trail = $this->makeTrail();
        $photo = TrailPhoto::factory()->for($trail)->pending()->create();

        $this->actingAs($this->standardUser())
            ->patch(route('admin.trail-photos.update', $photo), ['status' => 'approved'])
            ->assertRedirect(route('admin.login'));

        $this->assertSame(TrailPhoto::STATUS_PENDING, $photo->refresh()->status);
    }
}
