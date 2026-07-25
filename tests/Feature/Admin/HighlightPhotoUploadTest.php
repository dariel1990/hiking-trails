<?php

namespace Tests\Feature\Admin;

use App\Models\Trail;
use App\Models\TrailFeature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HighlightPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeHighlight(): TrailFeature
    {
        $trail = Trail::create([
            'name' => 'Test Trail',
            'description' => 'Trail for highlight photo upload tests.',
            'difficulty_level' => 2.0,
            'distance_km' => 5.5,
            'elevation_gain_m' => 150,
            'estimated_time_hours' => 1.5,
            'trail_type' => 'loop',
            'start_coordinates' => [54.78, -127.17],
        ]);

        return TrailFeature::create([
            'trail_id' => $trail->id,
            'feature_type' => 'viewpoint',
            'name' => 'Test Viewpoint',
            'coordinates' => [54.78, -127.17],
        ]);
    }

    public function test_admin_can_upload_a_photo_directly_to_a_highlight(): void
    {
        Storage::fake('public');
        $highlight = $this->makeHighlight();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.highlights.media.upload', $highlight), [
                'photo' => UploadedFile::fake()->image('viewpoint.jpg', 800, 600),
            ]);

        $response->assertOk()->assertJson(['success' => true, 'is_primary' => true]);

        $highlight->refresh();
        $this->assertSame(1, $highlight->media()->count());

        $media = $highlight->photos()->first();
        Storage::disk('public')->assertExists($media->storage_path);
    }

    public function test_second_uploaded_photo_is_not_primary(): void
    {
        Storage::fake('public');
        $highlight = $this->makeHighlight();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.highlights.media.upload', $highlight), [
            'photo' => UploadedFile::fake()->image('one.jpg'),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.highlights.media.upload', $highlight), [
            'photo' => UploadedFile::fake()->image('two.jpg'),
        ]);

        $response->assertOk()->assertJson(['success' => true, 'is_primary' => false]);
        $this->assertSame(2, $highlight->media()->count());
    }

    public function test_upload_is_rejected_once_photo_limit_is_reached(): void
    {
        Storage::fake('public');
        $highlight = $this->makeHighlight();
        $admin = $this->admin();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($admin)->post(route('admin.highlights.media.upload', $highlight), [
                'photo' => UploadedFile::fake()->image("photo{$i}.jpg"),
            ])->assertOk();
        }

        $this->actingAs($admin)->post(route('admin.highlights.media.upload', $highlight), [
            'photo' => UploadedFile::fake()->image('overflow.jpg'),
        ])->assertStatus(422)->assertJson(['success' => false]);

        $this->assertSame(10, $highlight->media()->count());
    }
}
