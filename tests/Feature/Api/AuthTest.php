<?php

namespace Tests\Feature\Api;

use App\Models\User;
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
            'name' => 'Jane Hiker',
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
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_register_requires_password_confirmation(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Jane',
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
