<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Button & icon colour for event pages, set in Settings → Events
 * (one for standard events, one for Christmas-themed events).
 */
class EventAccentColorSettingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * @return array<string, string>
     */
    private function eventsPayload(array $overrides = []): array
    {
        return array_merge([
            'group' => 'events',
            'event_page_layout' => 'premium',
            'event_accent_color' => '#2C5F5D',
            'event_christmas_accent_color' => '#B3202A',
        ], $overrides);
    }

    public function test_events_use_the_brand_green_and_red_by_default(): void
    {
        $standard = Event::factory()->create();
        $christmas = Event::factory()->christmas()->create();

        $this->get(route('local-events.show', $standard->slug))->assertOk()->assertSee('--tour-primary: #2c5f5d;', false);
        $this->get(route('local-events.show', $christmas->slug))->assertOk()->assertSee('--tour-primary: #b3202a;', false);
    }

    public function test_the_settings_page_has_colour_pickers(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.settings.edit', ['tab' => 'events']))
            ->assertOk()
            ->assertSee('Button &amp; icon colour', false)
            ->assertSee('Christmas events button &amp; icon colour', false)
            ->assertSee('type="color"', false)
            ->assertSee('name="event_accent_color"', false)
            ->assertSee('name="event_christmas_accent_color"', false);
    }

    public function test_admin_can_choose_the_colours_and_event_pages_use_them(): void
    {
        $standard = Event::factory()->create();
        $christmas = Event::factory()->christmas()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $this->eventsPayload([
                'event_accent_color' => '#1D4ED8',
                'event_christmas_accent_color' => '#166534',
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'events']))
            ->assertSessionHasNoErrors();

        $this->get(route('local-events.show', $standard->slug))
            ->assertSee('--tour-primary: #1d4ed8;', false)
            ->assertSee('--event-on-accent: #ffffff;', false);
        $this->get(route('local-events.show', $christmas->slug))
            ->assertSee('--tour-primary: #166534;', false);
    }

    public function test_a_light_colour_gets_dark_button_text(): void
    {
        Setting::set('event_accent_color', '#F5D90A');
        $event = Event::factory()->create();

        $this->get(route('local-events.show', $event->slug))
            ->assertSee('--tour-primary: #f5d90a;', false)
            ->assertSee('--event-on-accent: #111827;', false);
    }

    public function test_invalid_colours_are_rejected(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $this->eventsPayload([
                'event_accent_color' => 'blue',
                'event_christmas_accent_color' => '#12345',
            ]))
            ->assertSessionHasErrors(['event_accent_color', 'event_christmas_accent_color']);

        $this->assertSame('#2C5F5D', setting('event_accent_color'));
    }

    public function test_a_bad_stored_colour_falls_back_to_the_default(): void
    {
        Setting::set('event_accent_color', 'not-a-colour');
        $event = Event::factory()->create();

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('--tour-primary: #2c5f5d;', false);
    }
}
