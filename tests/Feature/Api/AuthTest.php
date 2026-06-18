<?php

namespace Tests\Feature\Api;

use App\Models\User;
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
