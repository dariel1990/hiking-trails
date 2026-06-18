<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/settings/profile')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_profile_settings(): void
    {
        $user = User::factory()->create(['name' => 'Thomas Camus']);

        $this->actingAs($user)
            ->get('/settings/profile')
            ->assertOk()
            ->assertSee('Thomas')
            ->assertSee('Camus');
    }

    public function test_user_can_update_first_and_last_name_and_bio(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $this->actingAs($user)
            ->put('/settings/profile', [
                'first_name' => 'New',
                'last_name' => 'Name',
                'bio' => 'I love hiking.',
            ])
            ->assertRedirect(route('settings.profile'));

        $user->refresh();

        $this->assertSame('New', $user->first_name);
        $this->assertSame('Name', $user->last_name);
        $this->assertSame('New Name', $user->name);
        $this->assertSame('I love hiking.', $user->bio);
    }

    public function test_first_and_last_name_are_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/settings/profile', ['first_name' => '', 'last_name' => ''])
            ->assertSessionHasErrors(['first_name', 'last_name']);
    }
}
