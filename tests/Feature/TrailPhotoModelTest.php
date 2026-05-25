<?php

namespace Tests\Feature;

use App\Models\Trail;
use App\Models\TrailPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrailPhotoModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeTrail(array $overrides = []): Trail
    {
        return Trail::create(array_merge([
            'name' => 'Test Trail',
            'description' => 'A trail for tests.',
            'difficulty_level' => 2.0,
            'distance_km' => 5.5,
            'elevation_gain_m' => 150,
            'estimated_time_hours' => 1.5,
            'trail_type' => 'loop',
            'start_coordinates' => [54.78, -127.17],
        ], $overrides));
    }

    public function test_trail_has_many_photos(): void
    {
        $trail = $this->makeTrail();
        TrailPhoto::factory()->count(3)->for($trail)->create();

        $this->assertCount(3, $trail->refresh()->photos);
    }

    public function test_approved_photos_scope_returns_only_approved_newest_first(): void
    {
        $trail = $this->makeTrail();

        TrailPhoto::factory()->for($trail)->pending()->create();
        TrailPhoto::factory()->for($trail)->rejected()->create();
        $older = TrailPhoto::factory()->for($trail)->approved()->create(['created_at' => now()->subDay()]);
        $newer = TrailPhoto::factory()->for($trail)->approved()->create(['created_at' => now()]);

        $approved = $trail->refresh()->approvedPhotos;

        $this->assertCount(2, $approved);
        $this->assertSame($newer->id, $approved->first()->id);
        $this->assertSame($older->id, $approved->last()->id);
    }

    public function test_status_helpers_reflect_status(): void
    {
        $pending = TrailPhoto::factory()->for($this->makeTrail())->pending()->create();
        $approved = TrailPhoto::factory()->for($this->makeTrail())->approved()->create();
        $rejected = TrailPhoto::factory()->for($this->makeTrail())->rejected()->create();

        $this->assertTrue($pending->isPending());
        $this->assertFalse($pending->isApproved());

        $this->assertTrue($approved->isApproved());
        $this->assertNotNull($approved->reviewed_at);
        $this->assertNotNull($approved->reviewed_by);

        $this->assertTrue($rejected->isRejected());
    }

    public function test_email_and_ip_are_hidden_from_serialization(): void
    {
        $photo = TrailPhoto::factory()->for($this->makeTrail())->create([
            'email' => 'submitter@example.com',
            'submitter_ip' => '203.0.113.42',
        ]);

        $array = $photo->toArray();

        $this->assertArrayNotHasKey('email', $array);
        $this->assertArrayNotHasKey('submitter_ip', $array);
    }

    public function test_reviewer_relationship_returns_admin_user(): void
    {
        $admin = User::factory()->create();
        $photo = TrailPhoto::factory()
            ->for($this->makeTrail())
            ->approved()
            ->create(['reviewed_by' => $admin->id]);

        $this->assertTrue($photo->reviewer->is($admin));
    }

    public function test_deleting_photo_removes_stored_files(): void
    {
        Storage::fake('public');
        $trail = $this->makeTrail();

        Storage::disk('public')->put('trail-photos/x/full.webp', 'fake');
        Storage::disk('public')->put('trail-photos/x/thumbs/full.webp', 'fake');

        $photo = TrailPhoto::factory()->for($trail)->create([
            'image_path' => 'trail-photos/x/full.webp',
            'thumbnail_path' => 'trail-photos/x/thumbs/full.webp',
        ]);

        Storage::disk('public')->assertExists('trail-photos/x/full.webp');
        Storage::disk('public')->assertExists('trail-photos/x/thumbs/full.webp');

        $photo->delete();

        Storage::disk('public')->assertMissing('trail-photos/x/full.webp');
        Storage::disk('public')->assertMissing('trail-photos/x/thumbs/full.webp');
    }

    public function test_deleting_trail_cascades_to_photos(): void
    {
        $trail = $this->makeTrail();
        TrailPhoto::factory()->count(2)->for($trail)->create();

        $this->assertDatabaseCount('trail_photos', 2);

        $trail->delete();

        $this->assertDatabaseCount('trail_photos', 0);
    }

    public function test_image_url_accessor_uses_storage_disk(): void
    {
        $photo = TrailPhoto::factory()->for($this->makeTrail())->create([
            'image_path' => 'trail-photos/1/abc.webp',
            'thumbnail_path' => 'trail-photos/1/thumbs/abc.webp',
        ]);

        $this->assertStringContainsString('trail-photos/1/abc.webp', $photo->image_url);
        $this->assertStringContainsString('thumbs/abc.webp', $photo->thumbnail_url);
    }

    public function test_thumbnail_url_falls_back_to_image_url_when_missing(): void
    {
        $photo = TrailPhoto::factory()->for($this->makeTrail())->create([
            'image_path' => 'trail-photos/1/abc.webp',
            'thumbnail_path' => null,
        ]);

        $this->assertSame($photo->image_url, $photo->thumbnail_url);
    }
}
