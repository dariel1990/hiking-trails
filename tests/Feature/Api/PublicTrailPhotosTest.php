<?php

namespace Tests\Feature\Api;

use App\Models\Trail;
use App\Models\TrailPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicTrailPhotosTest extends TestCase
{
    use RefreshDatabase;

    private function makeTrail(array $overrides = []): Trail
    {
        return Trail::create(array_merge([
            'name' => 'Test Trail',
            'description' => 'Trail for public api tests.',
            'difficulty_level' => 2.0,
            'distance_km' => 5.5,
            'elevation_gain_m' => 150,
            'estimated_time_hours' => 1.5,
            'trail_type' => 'loop',
            'start_coordinates' => [54.78, -127.17],
        ], $overrides));
    }

    private function fakePhotoFile(string $path): void
    {
        Storage::disk('public')->put($path, 'fake');
    }

    public function test_index_returns_only_approved_photos(): void
    {
        Storage::fake('public');
        $trail = $this->makeTrail();

        $approved = TrailPhoto::factory()->for($trail)->approved()->create([
            'image_path' => 'trail-photos/1/a.webp',
            'thumbnail_path' => 'trail-photos/1/thumbs/a.webp',
        ]);
        $this->fakePhotoFile($approved->image_path);

        TrailPhoto::factory()->for($trail)->pending()->create([
            'image_path' => 'trail-photos/1/b.webp',
        ]);
        TrailPhoto::factory()->for($trail)->rejected()->create([
            'image_path' => null,
        ]);

        $response = $this->getJson('/api/trail-photos?trail_id='.$trail->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $approved->id)
            ->assertJsonPath('data.0.caption', $approved->caption);
    }

    public function test_index_filters_to_a_single_trail(): void
    {
        Storage::fake('public');
        $a = $this->makeTrail(['name' => 'Trail A']);
        $b = $this->makeTrail(['name' => 'Trail B']);

        $photoA = TrailPhoto::factory()->for($a)->approved()->create(['image_path' => 'trail-photos/'.$a->id.'/a.webp']);
        $photoB = TrailPhoto::factory()->for($b)->approved()->create(['image_path' => 'trail-photos/'.$b->id.'/b.webp']);
        $this->fakePhotoFile($photoA->image_path);
        $this->fakePhotoFile($photoB->image_path);

        $response = $this->getJson('/api/trail-photos?trail_id='.$a->id);

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame($photoA->id, $response->json('data.0.id'));
    }

    public function test_response_hides_email_and_ip(): void
    {
        Storage::fake('public');
        $trail = $this->makeTrail();
        $photo = TrailPhoto::factory()->for($trail)->approved()->create([
            'image_path' => 'trail-photos/1/a.webp',
            'email' => 'private@example.com',
            'submitter_ip' => '203.0.113.1',
        ]);
        $this->fakePhotoFile($photo->image_path);

        $response = $this->getJson('/api/trail-photos?trail_id='.$trail->id);

        $response->assertOk();
        $json = $response->json('data.0');
        $this->assertArrayNotHasKey('email', $json);
        $this->assertArrayNotHasKey('submitter_ip', $json);
    }

    public function test_anonymous_submission_name_falls_back_to_anonymous(): void
    {
        Storage::fake('public');
        $trail = $this->makeTrail();
        $photo = TrailPhoto::factory()->for($trail)->approved()->create([
            'image_path' => 'trail-photos/1/a.webp',
            'name' => null,
        ]);
        $this->fakePhotoFile($photo->image_path);

        $this->getJson('/api/trail-photos?trail_id='.$trail->id)
            ->assertJsonPath('data.0.submitter_name', 'Anonymous');
    }

    public function test_rows_with_missing_files_are_filtered_out(): void
    {
        Storage::fake('public');
        $trail = $this->makeTrail();

        $present = TrailPhoto::factory()->for($trail)->approved()->create([
            'image_path' => 'trail-photos/1/present.webp',
        ]);
        $this->fakePhotoFile($present->image_path);

        TrailPhoto::factory()->for($trail)->approved()->create([
            'image_path' => 'trail-photos/1/missing.webp',
        ]);

        $response = $this->getJson('/api/trail-photos?trail_id='.$trail->id);

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame($present->id, $response->json('data.0.id'));
    }

    public function test_returns_newest_first(): void
    {
        Storage::fake('public');
        $trail = $this->makeTrail();

        $older = TrailPhoto::factory()->for($trail)->approved()->create([
            'image_path' => 'trail-photos/1/older.webp',
            'created_at' => now()->subDay(),
        ]);
        $newer = TrailPhoto::factory()->for($trail)->approved()->create([
            'image_path' => 'trail-photos/1/newer.webp',
            'created_at' => now(),
        ]);
        $this->fakePhotoFile($older->image_path);
        $this->fakePhotoFile($newer->image_path);

        $response = $this->getJson('/api/trail-photos?trail_id='.$trail->id);

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertSame([$newer->id, $older->id], $ids);
    }
}
