<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AvatarSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_a_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/settings/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('me.jpg', 400, 400),
            ])
            ->assertRedirect(route('settings.profile'));

        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
        $this->assertNotNull($user->avatar_url);
    }

    public function test_uploading_a_new_photo_deletes_the_old_one(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/settings/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('first.jpg'),
        ]);
        $firstPath = $user->refresh()->avatar;

        $this->actingAs($user)->post('/settings/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('second.jpg'),
        ]);
        $secondPath = $user->refresh()->avatar;

        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);
    }

    public function test_avatar_upload_rejects_non_image_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/settings/profile/avatar', [
                'avatar' => UploadedFile::fake()->create('not-an-image.pdf', 100),
            ])
            ->assertSessionHasErrors('avatar');

        $this->assertNull($user->fresh()->avatar);
    }

    public function test_user_can_remove_their_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/settings/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('me.jpg'),
        ]);
        $path = $user->refresh()->avatar;

        $this->actingAs($user)
            ->delete('/settings/profile/avatar')
            ->assertRedirect(route('settings.profile'));

        $this->assertNull($user->fresh()->avatar);
        Storage::disk('public')->assertMissing($path);
    }
}
