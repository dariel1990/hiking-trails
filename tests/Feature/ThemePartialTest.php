<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemePartialTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_theme_overrides_are_emitted_at_defaults(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('theme-overrides', false)
            ->assertDontSee('--c-forest-600:', false);
    }

    public function test_changing_the_brand_color_emits_the_full_forest_ramp(): void
    {
        Setting::set('theme_brand_color', '#336699');

        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('theme-overrides', false);
        $response->assertSee('--c-forest-600:51 102 153', false);
        $response->assertSee('--c-forest-50:', false);
        $response->assertSee('--c-forest-950:', false);
        $response->assertDontSee('--c-accent-500:', false);
    }

    public function test_changing_the_body_font_emits_the_variable_and_bunny_link(): void
    {
        Setting::set('theme_body_font', 'Nunito');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('--font-body:', false)
            ->assertSee('fonts.bunny.net/css?family=nunito:', false);
    }

    public function test_js_contexts_receive_the_resolved_hex(): void
    {
        Setting::set('theme_brand_color', '#336699');

        $this->assertSame('#336699', theme_color('forest', 600));
        $this->assertSame('51, 102, 153', theme_color_rgb('forest', 600));
    }

    public function test_theme_color_returns_canonical_hex_at_defaults(): void
    {
        $this->assertSame('#2c5f5d', theme_color('forest', 600));
        $this->assertSame('#4a9b8e', theme_color('emerald', 400));
    }

    public function test_admin_pages_do_not_include_theme_overrides(): void
    {
        Setting::set('theme_brand_color', '#336699');

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertDontSee('theme-overrides', false);
    }

    public function test_theme_settings_validate_hex_and_font_choices(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $payload = [
            'group' => 'theme',
            'theme_brand_color' => '#2C5F5D',
            'theme_accent_color' => '#E87B35',
            'theme_action_color' => '#10b981',
            'theme_heading_font' => 'Playfair Display',
            'theme_subheading_font' => 'Oswald',
            'theme_body_font' => 'Inter',
        ];

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $payload)
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'theme']))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), array_merge($payload, ['theme_brand_color' => '#fff']))
            ->assertSessionHasErrors('theme_brand_color');

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), array_merge($payload, ['theme_brand_color' => '2C5F5D']))
            ->assertSessionHasErrors('theme_brand_color');

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), array_merge($payload, ['theme_body_font' => 'Comic Sans MS']))
            ->assertSessionHasErrors('theme_body_font');
    }
}
