<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\AppleIdTokenVerifier;
use Google\Client as GoogleClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_and_returns_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'Jane',
            'last_name' => 'Hiker',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'device_name' => 'Pixel 7 (XploreSmithers Android)',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']])
            ->assertJsonPath('user.email', 'jane@example.com');

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'jane@example.com']);

        $this->postJson('/api/auth/register', [
            'first_name' => 'Jane',
            'last_name' => 'Hiker',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_register_requires_password_confirmation(): void
    {
        $this->postJson('/api/auth/register', [
            'first_name' => 'Jane',
            'last_name' => 'Hiker',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'mismatch',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_login_returns_token_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'jane@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'device_name' => 'Pixel 7',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'jane@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401)->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_google_sign_in_creates_user_and_returns_token(): void
    {
        $this->fakeGoogleToken([
            'sub' => 'google-uid-123',
            'email' => 'jane@example.com',
            'email_verified' => true,
            'name' => 'Jane Hiker',
        ]);

        $response = $this->postJson('/api/auth/google', [
            'id_token' => 'valid-token',
            'device_name' => 'Pixel 7',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']])
            ->assertJsonPath('user.email', 'jane@example.com');
        $this->assertNotEmpty($response->json('token'));
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'google_id' => 'google-uid-123',
        ]);
    }

    public function test_google_sign_in_links_existing_user_without_duplicate(): void
    {
        User::factory()->create([
            'email' => 'jane@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->fakeGoogleToken([
            'sub' => 'google-uid-123',
            'email' => 'jane@example.com',
            'email_verified' => true,
            'name' => 'Jane Hiker',
        ]);

        $this->postJson('/api/auth/google', ['id_token' => 'valid-token'])->assertOk();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'google_id' => 'google-uid-123',
        ]);
    }

    public function test_google_sign_in_rejects_invalid_token(): void
    {
        $this->fakeGoogleToken(false);

        $this->postJson('/api/auth/google', ['id_token' => 'tampered'])
            ->assertStatus(401)
            ->assertJson(['message' => 'Invalid Google token']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_sign_in_rejects_unverified_email(): void
    {
        $this->fakeGoogleToken([
            'sub' => 'google-uid-123',
            'email' => 'jane@example.com',
            'email_verified' => false,
            'name' => 'Jane Hiker',
        ]);

        $this->postJson('/api/auth/google', ['id_token' => 'valid-token'])->assertStatus(401);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_sign_in_requires_id_token(): void
    {
        $this->postJson('/api/auth/google', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('id_token');
    }

    /**
     * Bind a fake Google client that returns the given verifyIdToken payload.
     *
     * @param  array<string, mixed>|false  $payload
     */
    private function fakeGoogleToken(array|false $payload): void
    {
        $this->mock(GoogleClient::class, function ($mock) use ($payload) {
            $mock->shouldReceive('setClientId')->once();
            $mock->shouldReceive('verifyIdToken')->once()->andReturn($payload);
        });
    }

    public function test_apple_sign_in_creates_user_and_returns_token(): void
    {
        $this->fakeAppleToken([
            'sub' => 'apple-uid-123',
            'email' => 'jane@example.com',
        ]);

        $response = $this->postJson('/api/auth/apple', [
            'id_token' => 'valid-token',
            'device_name' => 'iPhone 15',
            'name' => 'Jane Hiker',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']])
            ->assertJsonPath('user.email', 'jane@example.com')
            ->assertJsonPath('user.name', 'Jane Hiker');
        $this->assertNotEmpty($response->json('token'));
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'apple_id' => 'apple-uid-123',
        ]);
        $this->assertNotNull(User::first()->email_verified_at);
    }

    public function test_apple_repeat_sign_in_matches_by_apple_id_without_email(): void
    {
        User::factory()->create([
            'name' => 'Jane Hiker',
            'email' => 'jane@example.com',
            'apple_id' => 'apple-uid-123',
        ]);

        // Apple omits email/name after the first authorization.
        $this->fakeAppleToken(['sub' => 'apple-uid-123']);

        $this->postJson('/api/auth/apple', ['id_token' => 'valid-token'])
            ->assertOk()
            ->assertJsonPath('user.email', 'jane@example.com')
            ->assertJsonPath('user.name', 'Jane Hiker');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_apple_sign_in_links_existing_email_user_without_duplicate(): void
    {
        User::factory()->create([
            'name' => 'Jane Hiker',
            'email' => 'jane@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->fakeAppleToken([
            'sub' => 'apple-uid-123',
            'email' => 'jane@example.com',
        ]);

        $this->postJson('/api/auth/apple', ['id_token' => 'valid-token'])->assertOk();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'apple_id' => 'apple-uid-123',
            'name' => 'Jane Hiker',
        ]);
    }

    public function test_apple_sign_in_accepts_private_relay_email(): void
    {
        $this->fakeAppleToken([
            'sub' => 'apple-uid-123',
            'email' => 'abc123@privaterelay.appleid.com',
        ]);

        $this->postJson('/api/auth/apple', ['id_token' => 'valid-token'])->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'abc123@privaterelay.appleid.com',
            'apple_id' => 'apple-uid-123',
        ]);
    }

    public function test_apple_sign_in_rejects_invalid_token(): void
    {
        $this->fakeAppleToken(null);

        $this->postJson('/api/auth/apple', ['id_token' => 'tampered'])
            ->assertStatus(401)
            ->assertJson(['message' => 'Invalid Apple token']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_apple_sign_in_rejects_deactivated_user(): void
    {
        User::factory()->create([
            'email' => 'jane@example.com',
            'apple_id' => 'apple-uid-123',
            'is_active' => false,
        ]);

        $this->fakeAppleToken(['sub' => 'apple-uid-123']);

        $this->postJson('/api/auth/apple', ['id_token' => 'valid-token'])
            ->assertStatus(403)
            ->assertJson(['message' => 'This account has been deactivated']);
    }

    public function test_apple_sign_in_rejects_unknown_user_without_email(): void
    {
        $this->fakeAppleToken(['sub' => 'apple-uid-unknown']);

        $this->postJson('/api/auth/apple', ['id_token' => 'valid-token'])
            ->assertStatus(422);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_apple_sign_in_requires_id_token(): void
    {
        $this->postJson('/api/auth/apple', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('id_token');
    }

    /**
     * Bind a fake Apple verifier that returns the given claims (null = invalid).
     *
     * @param  array<string, mixed>|null  $claims
     */
    private function fakeAppleToken(?array $claims): void
    {
        $this->mock(AppleIdTokenVerifier::class, function ($mock) use ($claims) {
            $mock->shouldReceive('verify')->once()->andReturn($claims);
        });
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Jane Hiker']);
        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertExactJson([
                'id' => $user->id,
                'name' => 'Jane Hiker',
                'email' => $user->email,
                'avatar_url' => null,
            ]);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('android')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/logout')
            ->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
