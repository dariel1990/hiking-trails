<?php

namespace Tests\Feature\Api;

use App\Models\Trail;
use App\Models\TrailPhoto;
use App\Models\User;
use App\Notifications\NewTrailPhotoSubmitted;
use App\Services\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class TrailPhotoSubmissionTest extends TestCase
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

    private function passRecaptcha(): void
    {
        $this->mock(RecaptchaService::class, function (MockInterface $mock) {
            $mock->shouldReceive('verify')->andReturn(true);
        });
    }

    private function failRecaptcha(): void
    {
        $this->mock(RecaptchaService::class, function (MockInterface $mock) {
            $mock->shouldReceive('verify')->andReturn(false);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Trail $trail, array $overrides = []): array
    {
        return array_merge([
            'trail_id' => $trail->id,
            'image' => UploadedFile::fake()->image('photo.jpg', 1200, 900),
            'caption' => 'A great view.',
            'name' => 'Sam Hiker',
            'email' => 'sam@example.com',
            'g-recaptcha-response' => 'test-token',
            'website' => '',
        ], $overrides);
    }

    public function test_valid_submission_stores_photo_and_notifies_admins(): void
    {
        Storage::fake('public');
        Notification::fake();
        $this->passRecaptcha();

        $trail = $this->makeTrail();
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['is_admin' => false]);

        $response = $this->postJson('/api/trail-photos', $this->payload($trail));

        $response->assertCreated()
            ->assertJsonStructure(['message', 'id']);

        $this->assertDatabaseHas('trail_photos', [
            'trail_id' => $trail->id,
            'email' => 'sam@example.com',
            'name' => 'Sam Hiker',
            'caption' => 'A great view.',
            'status' => TrailPhoto::STATUS_PENDING,
        ]);

        $photo = TrailPhoto::first();
        Storage::disk('public')->assertExists($photo->image_path);
        Storage::disk('public')->assertExists($photo->thumbnail_path);

        Notification::assertSentTo($admin, NewTrailPhotoSubmitted::class);
    }

    public function test_image_is_required(): void
    {
        Storage::fake('public');
        $this->passRecaptcha();
        $trail = $this->makeTrail();

        $this->postJson('/api/trail-photos', $this->payload($trail, ['image' => null]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('image');
    }

    public function test_email_is_required(): void
    {
        Storage::fake('public');
        $this->passRecaptcha();
        $trail = $this->makeTrail();

        $this->postJson('/api/trail-photos', $this->payload($trail, ['email' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_unknown_trail_is_rejected(): void
    {
        Storage::fake('public');
        $this->passRecaptcha();
        $trail = $this->makeTrail();

        $this->postJson('/api/trail-photos', $this->payload($trail, ['trail_id' => 99999]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('trail_id');
    }

    public function test_honeypot_field_blocks_submission(): void
    {
        Storage::fake('public');
        $this->passRecaptcha();
        $trail = $this->makeTrail();

        $this->postJson('/api/trail-photos', $this->payload($trail, ['website' => 'spam-bot.example']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('website');

        $this->assertDatabaseCount('trail_photos', 0);
    }

    public function test_svg_uploads_are_rejected(): void
    {
        Storage::fake('public');
        $this->passRecaptcha();
        $trail = $this->makeTrail();

        $svg = UploadedFile::fake()->createWithContent('evil.svg', '<svg></svg>');

        $this->postJson('/api/trail-photos', $this->payload($trail, ['image' => $svg]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('image');
    }

    public function test_failed_recaptcha_blocks_submission(): void
    {
        Storage::fake('public');
        $this->failRecaptcha();
        $trail = $this->makeTrail();

        $this->postJson('/api/trail-photos', $this->payload($trail))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Spam check failed. Please refresh the page and try again.');

        $this->assertDatabaseCount('trail_photos', 0);
    }

    public function test_email_quota_blocks_after_daily_limit(): void
    {
        Storage::fake('public');
        Notification::fake();
        $this->passRecaptcha();

        $trail = $this->makeTrail();
        User::factory()->create(['is_admin' => true]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/trail-photos', $this->payload($trail, [
                'image' => UploadedFile::fake()->image("photo-{$i}.jpg", 1200, 900),
            ]))->assertCreated();
        }

        $this->postJson('/api/trail-photos', $this->payload($trail, [
            'image' => UploadedFile::fake()->image('photo-6.jpg', 1200, 900),
        ]))->assertStatus(429);

        $this->assertDatabaseCount('trail_photos', 5);
    }

    public function test_stored_image_is_re_encoded_as_webp(): void
    {
        Storage::fake('public');
        Notification::fake();
        $this->passRecaptcha();
        $trail = $this->makeTrail();

        $this->postJson('/api/trail-photos', $this->payload($trail))->assertCreated();

        $photo = TrailPhoto::first();
        $this->assertStringEndsWith('.webp', $photo->image_path);
        $this->assertStringEndsWith('.webp', $photo->thumbnail_path);
        $this->assertStringContainsString('thumbs/', $photo->thumbnail_path);
    }
}
