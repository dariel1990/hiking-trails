<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_email_and_phone(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)
            ->put('/settings/account', [
                'email' => 'new@example.com',
                'phone' => '(250) 555-0123',
            ])
            ->assertRedirect(route('settings.account'));

        $user->refresh();

        $this->assertSame('new@example.com', $user->email);
        $this->assertSame('(250) 555-0123', $user->phone);
        $this->assertNull($user->email_verified_at);
    }

    public function test_changing_password_requires_correct_current_password(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);

        $this->actingAs($user)
            ->put('/settings/account', [
                'email' => 'me@example.com',
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_changing_password_fails_when_current_password_is_blank(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);

        $this->actingAs($user)
            ->put('/settings/account', [
                'email' => 'me@example.com',
                'current_password' => '',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_changing_password_fails_when_confirmation_does_not_match(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);

        $this->actingAs($user)
            ->put('/settings/account', [
                'email' => 'me@example.com',
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'does-not-match',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_failed_password_change_keeps_typed_current_password_but_not_new_password(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);

        $response = $this->actingAs($user)
            ->from('/settings/account')
            ->put('/settings/account', [
                'email' => 'me@example.com',
                'current_password' => 'wrong-password-i-typed',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertSessionHas('_old_input.current_password', 'wrong-password-i-typed');
        $oldInput = session('_old_input', []);
        $this->assertArrayNotHasKey('password', $oldInput);
        $this->assertArrayNotHasKey('password_confirmation', $oldInput);
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);

        $this->actingAs($user)
            ->put('/settings/account', [
                'email' => 'me@example.com',
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('settings.account'));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_deactivating_account_requires_correct_password(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);

        $this->actingAs($user)
            ->delete('/settings/account', ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password');

        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_deactivating_account_logs_the_user_out_and_blocks_future_login(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);

        $this->actingAs($user)
            ->delete('/settings/account', ['password' => 'password'])
            ->assertRedirect(route('home'));

        $user->refresh();
        $this->assertFalse($user->is_active);

        $this->post('/login', [
            'email' => 'me@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_google_only_account_can_be_deactivated_without_a_password(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com', 'password' => null, 'google_id' => '123456']);

        $this->actingAs($user)
            ->delete('/settings/account', [])
            ->assertRedirect(route('home'));

        $this->assertFalse($user->fresh()->is_active);
    }
}
