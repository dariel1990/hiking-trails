<?php

namespace Tests\Feature\Admin;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacilityPhotoUploadTest extends TestCase
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
            ->post(route('admin.facilities.photos.upload'), [
                'photo' => UploadedFile::fake()->image('trailhead.jpg', 800, 600),
            ]);

        $response->assertOk()->assertJsonStructure(['path', 'thumbnail_path', 'url', 'thumbnail_url']);

        $path = $response->json('path');
        $thumbnailPath = $response->json('thumbnail_path');

        $this->assertStringStartsWith('facilities/tmp/photos/', $path);
        $this->assertStringStartsWith('facilities/tmp/photos/thumbs/', $thumbnailPath);
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertExists($thumbnailPath);
    }

    public function test_admin_can_delete_a_temporarily_uploaded_photo(): void
    {
        Storage::fake('public');

        $upload = $this->actingAs($this->admin())
            ->post(route('admin.facilities.photos.upload'), [
                'photo' => UploadedFile::fake()->image('trailhead.jpg'),
            ]);

        $path = $upload->json('path');
        $thumbnailPath = $upload->json('thumbnail_path');

        $this->actingAs($this->admin())
            ->delete(route('admin.facilities.photos.upload.delete'), [
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
        Storage::disk('public')->put('facility-icons/keep-me.webp', 'not-really-an-image');

        $this->actingAs($this->admin())
            ->delete(route('admin.facilities.photos.upload.delete'), [
                'path' => 'facility-icons/keep-me.webp',
            ])
            ->assertStatus(422);

        Storage::disk('public')->assertExists('facility-icons/keep-me.webp');
    }

    public function test_creating_a_facility_moves_uploaded_photos_into_permanent_storage(): void
    {
        Storage::fake('public');

        $upload = $this->actingAs($this->admin())
            ->post(route('admin.facilities.photos.upload'), [
                'photo' => UploadedFile::fake()->image('trailhead.jpg'),
            ]);

        $tempPath = $upload->json('path');
        $tempThumbnailPath = $upload->json('thumbnail_path');

        $response = $this->actingAs($this->admin())
            ->post(route('admin.facilities.store'), [
                'facility_type' => 'parking',
                'name' => 'Trailhead Parking',
                'latitude' => 54.78,
                'longitude' => -127.17,
                'is_active' => '1',
                'uploaded_photos' => json_encode([
                    ['path' => $tempPath, 'thumbnail_path' => $tempThumbnailPath],
                ]),
            ]);

        $response->assertRedirect(route('admin.facilities.index'));

        $facility = Facility::where('name', 'Trailhead Parking')->firstOrFail();
        $media = $facility->media()->firstOrFail();

        $this->assertStringStartsWith("facilities/{$facility->id}/photos/", $media->file_path);
        $this->assertStringStartsWith("facilities/{$facility->id}/photos/thumbs/", $media->thumbnail_path);
        Storage::disk('public')->assertExists($media->file_path);
        Storage::disk('public')->assertExists($media->thumbnail_path);
        Storage::disk('public')->assertMissing($tempPath);
        Storage::disk('public')->assertMissing($tempThumbnailPath);
    }
}
