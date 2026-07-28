<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityType;
use App\Models\Trail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrailPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_upload_a_photo_to_temporary_storage(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())
            ->post(route('admin.trails.photos.upload'), [
                'photo' => UploadedFile::fake()->image('lakeshore.jpg', 800, 600),
            ]);

        $response->assertOk()->assertJsonStructure(['path', 'thumbnail_path', 'url', 'thumbnail_url']);

        $path = $response->json('path');
        $thumbnailPath = $response->json('thumbnail_path');

        $this->assertStringStartsWith('trails/tmp/photos/', $path);
        $this->assertStringStartsWith('trails/tmp/photos/thumbs/', $thumbnailPath);
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertExists($thumbnailPath);
    }

    public function test_admin_can_delete_a_temporarily_uploaded_photo(): void
    {
        Storage::fake('public');

        $upload = $this->actingAs($this->admin())
            ->post(route('admin.trails.photos.upload'), [
                'photo' => UploadedFile::fake()->image('lakeshore.jpg'),
            ]);

        $path = $upload->json('path');
        $thumbnailPath = $upload->json('thumbnail_path');

        $this->actingAs($this->admin())
            ->delete(route('admin.trails.photos.upload.delete'), [
                'path' => $path,
                'thumbnail_path' => $thumbnailPath,
            ])
            ->assertOk()
            ->assertJson(['deleted' => true]);

        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertMissing($thumbnailPath);
    }

    public function test_deleting_a_photo_outside_the_temp_directory_is_rejected(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('trail-photos/keep-me.webp', 'not-really-an-image');

        $this->actingAs($this->admin())
            ->delete(route('admin.trails.photos.upload.delete'), [
                'path' => 'trail-photos/keep-me.webp',
            ])
            ->assertStatus(422);

        Storage::disk('public')->assertExists('trail-photos/keep-me.webp');
    }

    public function test_creating_a_trail_moves_uploaded_photos_into_permanent_storage(): void
    {
        Storage::fake('public');

        $activity = ActivityType::create(['name' => 'Fishing', 'slug' => 'fishing', 'icon' => '🎣', 'color' => '#3b82f6', 'is_active' => true]);

        $upload = $this->actingAs($this->admin())
            ->post(route('admin.trails.photos.upload'), [
                'photo' => UploadedFile::fake()->image('lakeshore.jpg'),
            ]);

        $tempPath = $upload->json('path');
        $tempThumbnailPath = $upload->json('thumbnail_path');

        $response = $this->actingAs($this->admin())
            ->post(route('admin.trails.store'), [
                'name' => 'Sunset Lake',
                'location_type' => 'fishing_lake',
                'status' => 'active',
                'point_latitude' => 54.78,
                'point_longitude' => -127.17,
                'activity_id' => $activity->id,
                'uploaded_photos' => json_encode([
                    ['path' => $tempPath, 'thumbnail_path' => $tempThumbnailPath],
                ]),
                'featured_photo_index' => 0,
            ]);

        $response->assertRedirect();

        $trail = Trail::where('name', 'Sunset Lake')->firstOrFail();
        $media = $trail->media()->firstOrFail();

        $this->assertStringStartsWith('trail-photos/', $media->storage_path);
        $this->assertStringStartsWith('trail-photos/thumbs/', $media->thumbnail_path);
        $this->assertTrue((bool) $media->is_featured);
        Storage::disk('public')->assertExists($media->storage_path);
        Storage::disk('public')->assertExists($media->thumbnail_path);
        Storage::disk('public')->assertMissing($tempPath);
        Storage::disk('public')->assertMissing($tempThumbnailPath);
    }
}
