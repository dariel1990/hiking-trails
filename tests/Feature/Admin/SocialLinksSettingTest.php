<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinksSettingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * @param  array<int, array<string, string>>  $links
     * @return array<string, mixed>
     */
    private function contactPayload(array $links): array
    {
        $payload = ['group' => 'contact', 'social_links' => json_encode($links)];

        foreach (config('settings.definitions') as $key => $definition) {
            if ($definition['group'] === 'contact' && $key !== 'social_links') {
                $payload[$key] = $definition['default'];
            }
        }

        return $payload;
    }

    public function test_the_settings_page_renders_the_repeater_with_current_links(): void
    {
        Setting::set('social_links', [
            ['icon' => 'pinterest', 'label' => 'Pinterest', 'url' => 'https://pinterest.com/xplore'],
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.settings.edit', ['tab' => 'contact']))
            ->assertOk()
            ->assertSee('Add social link')
            ->assertSee('pinterest.com', false)
            ->assertSee('X (Twitter)');
    }

    public function test_admin_can_save_a_list_of_social_links(): void
    {
        $links = [
            ['icon' => 'strava', 'label' => 'Strava', 'url' => 'https://strava.com/clubs/smithers'],
            ['icon' => 'linkedin', 'label' => 'LinkedIn', 'url' => 'https://linkedin.com/company/xplore'],
        ];

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $this->contactPayload($links))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'contact']))
            ->assertSessionHasNoErrors();

        $this->assertSame($links, Setting::get('social_links'));
    }

    public function test_saved_links_render_in_the_footer_in_order(): void
    {
        Setting::set('social_links', [
            ['icon' => 'strava', 'label' => 'Our Strava club', 'url' => 'https://strava.com/clubs/smithers'],
            ['icon' => 'whatsapp', 'label' => 'WhatsApp us', 'url' => 'https://wa.me/15550000000'],
        ]);

        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('https://strava.com/clubs/smithers', false);
        $response->assertSee('aria-label="WhatsApp us"', false);
        $response->assertSee(config('social-icons.strava.path'), false);

        $body = $response->getContent();
        $this->assertLessThan(
            strpos($body, 'https://wa.me/15550000000'),
            strpos($body, 'https://strava.com/clubs/smithers'),
            'Links should render in the configured order.'
        );
    }

    public function test_footer_social_section_is_hidden_when_no_links_are_configured(): void
    {
        Setting::set('social_links', []);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Follow our adventures');
    }

    public function test_an_unknown_icon_is_rejected(): void
    {
        $payload = $this->contactPayload([
            ['icon' => 'myspace', 'label' => 'MySpace', 'url' => 'https://myspace.com/xplore'],
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $payload)
            ->assertSessionHasErrors('social_links');
    }

    public function test_a_link_without_a_valid_url_is_rejected(): void
    {
        $payload = $this->contactPayload([
            ['icon' => 'facebook', 'label' => 'Facebook', 'url' => 'not-a-url'],
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $payload)
            ->assertSessionHasErrors('social_links');
    }

    public function test_a_link_without_a_label_is_rejected(): void
    {
        $payload = $this->contactPayload([
            ['icon' => 'facebook', 'label' => '   ', 'url' => 'https://facebook.com/xplore'],
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $payload)
            ->assertSessionHasErrors('social_links');
    }

    public function test_more_than_the_maximum_number_of_links_is_rejected(): void
    {
        $links = array_fill(0, 13, ['icon' => 'link', 'label' => 'Site', 'url' => 'https://example.com']);

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $this->contactPayload($links))
            ->assertSessionHasErrors('social_links');
    }

    public function test_every_icon_in_the_library_is_renderable(): void
    {
        foreach (config('social-icons') as $key => $icon) {
            $this->assertArrayHasKey('label', $icon, "Icon [{$key}] is missing a label.");
            $this->assertNotEmpty($icon['path'] ?? null, "Icon [{$key}] is missing a path.");
            $this->assertMatchesRegularExpression('/^[Mm]/', $icon['path'], "Icon [{$key}] path must start with a moveto command.");
        }
    }

    public function test_non_admins_cannot_update_settings(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->put(route('admin.settings.update'), $this->contactPayload([]))
            ->assertRedirect(route('admin.login'));
    }
}
