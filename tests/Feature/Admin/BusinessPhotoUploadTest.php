<?php

namespace Tests\Feature\Admin;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessPhotoUploadTest extends TestCase
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
            ->post(route('admin.businesses.photos.upload'), [
                'photo' => UploadedFile::fake()->image('storefront.jpg', 800, 600),
            ]);

        $response->assertOk()->assertJsonStructure(['path', 'thumbnail_path', 'url', 'thumbnail_url']);

        $path = $response->json('path');
        $thumbnailPath = $response->json('thumbnail_path');

        $this->assertStringStartsWith('businesses/tmp/photos/', $path);
        $this->assertStringStartsWith('businesses/tmp/photos/thumbs/', $thumbnailPath);
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertExists($thumbnailPath);
    }

    public function test_admin_can_delete_a_temporarily_uploaded_photo(): void
    {
        Storage::fake('public');

        $upload = $this->actingAs($this->admin())
            ->post(route('admin.businesses.photos.upload'), [
                'photo' => UploadedFile::fake()->image('storefront.jpg'),
            ]);

        $path = $upload->json('path');
        $thumbnailPath = $upload->json('thumbnail_path');

        $this->actingAs($this->admin())
            ->delete(route('admin.businesses.photos.upload.delete'), [
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
        Storage::disk('public')->put('business-icons/keep-me.webp', 'not-really-an-image');

        $this->actingAs($this->admin())
            ->delete(route('admin.businesses.photos.upload.delete'), [
                'path' => 'business-icons/keep-me.webp',
            ])
            ->assertStatus(422);

        Storage::disk('public')->assertExists('business-icons/keep-me.webp');
    }

    public function test_creating_a_business_moves_uploaded_photos_into_permanent_storage(): void
    {
        Storage::fake('public');

        $upload = $this->actingAs($this->admin())
            ->post(route('admin.businesses.photos.upload'), [
                'photo' => UploadedFile::fake()->image('storefront.jpg'),
            ]);

        $tempPath = $upload->json('path');
        $tempThumbnailPath = $upload->json('thumbnail_path');

        $response = $this->actingAs($this->admin())
            ->post(route('admin.businesses.store'), [
                'business_type' => 'cafe',
                'name' => 'Trailhead Coffee',
                'latitude' => 54.78,
                'longitude' => -127.17,
                'is_active' => '1',
                'uploaded_photos' => json_encode([
                    ['path' => $tempPath, 'thumbnail_path' => $tempThumbnailPath],
                ]),
            ]);

        $response->assertRedirect(route('admin.businesses.index'));

        $business = Business::where('name', 'Trailhead Coffee')->firstOrFail();
        $media = $business->media()->firstOrFail();

        $this->assertStringStartsWith("businesses/{$business->id}/photos/", $media->file_path);
        $this->assertStringStartsWith("businesses/{$business->id}/photos/thumbs/", $media->thumbnail_path);
        Storage::disk('public')->assertExists($media->file_path);
        Storage::disk('public')->assertExists($media->thumbnail_path);
        Storage::disk('public')->assertMissing($tempPath);
        Storage::disk('public')->assertMissing($tempThumbnailPath);
    }

    public function test_deleting_media_over_ajax_returns_json_instead_of_redirecting(): void
    {
        Storage::fake('public');
        $business = Business::create([
            'name' => 'Trailhead Coffee',
            'business_type' => 'cafe',
            'latitude' => 54.78,
            'longitude' => -127.17,
        ]);
        $media = $business->media()->create([
            'media_type' => 'photo',
            'file_path' => 'businesses/1/photos/photo.webp',
            'is_primary' => true,
        ]);

        $this->actingAs($this->admin())
            ->deleteJson(route('admin.businesses.media.delete', [$business, $media]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('business_media', ['id' => $media->id]);
    }

    public function test_setting_primary_media_over_ajax_returns_json_instead_of_redirecting(): void
    {
        Storage::fake('public');
        $business = Business::create([
            'name' => 'Trailhead Coffee',
            'business_type' => 'cafe',
            'latitude' => 54.78,
            'longitude' => -127.17,
        ]);
        $first = $business->media()->create([
            'media_type' => 'photo',
            'file_path' => 'businesses/1/photos/one.webp',
            'is_primary' => true,
        ]);
        $second = $business->media()->create([
            'media_type' => 'photo',
            'file_path' => 'businesses/1/photos/two.webp',
            'is_primary' => false,
        ]);

        $this->actingAs($this->admin())
            ->patchJson(route('admin.businesses.media.primary', [$business, $second]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertTrue($second->fresh()->is_primary);
    }
}
