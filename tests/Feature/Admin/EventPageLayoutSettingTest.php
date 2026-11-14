<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\EventMedia;
use App\Models\EventStop;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The public event page layout is chosen in Settings → Events; admins can
 * preview any layout with ?layout= before making it live.
 */
class EventPageLayoutSettingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeEvent(): Event
    {
        $event = Event::factory()->create([
            'title' => 'Winter Carnival',
            'event_date' => now()->addWeek()->toDateString(),
            'event_time' => '18:30',
            'venue' => 'Bovill Square',
            'organizer' => 'Smithers Chamber',
            'external_url' => 'https://example.com/carnival',
        ]);
        EventStop::factory()->for($event)->create(['stop_label' => 'Hot Chocolate Tent']);
        EventStop::factory()->for($event)->roaming()->create(['stop_label' => 'Carollers', 'stop_order' => 1]);
        EventMedia::factory()->for($event)->create(['caption' => 'Lights on Main']);

        return $event;
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function layouts(): array
    {
        return [
            'premium' => ['premium'],
            'booking' => ['booking'],
            'minimal' => ['minimal'],
            'editorial' => ['editorial'],
            'corporate' => ['corporate'],
        ];
    }

    public function test_every_layout_in_the_settings_has_a_view_and_is_tested(): void
    {
        $layouts = array_keys(config('settings.definitions.event_page_layout.options'));

        $this->assertSame($layouts, array_keys(self::layouts()));
        foreach ($layouts as $layout) {
            $this->assertTrue(view()->exists('local-events.layouts.'.$layout), "Missing view for the {$layout} layout.");
        }
    }

    public function test_premium_is_the_default_layout(): void
    {
        $event = $this->makeEvent();

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('data-layout="premium"', false);
    }

    #[DataProvider('layouts')]
    public function test_every_layout_shows_the_whole_event(string $layout): void
    {
        Setting::set('event_page_layout', $layout);
        $event = $this->makeEvent();

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('data-layout="'.$layout.'"', false)
            ->assertSee('Winter Carnival')
            ->assertSee('6:30 PM')
            ->assertSee('Bovill Square')
            ->assertSee('Smithers Chamber')
            ->assertSee('All Events')
            ->assertSee('More info')
            ->assertSee('https://example.com/carnival', false)
            ->assertSee(route('events.calendar', $event->id), false)
            ->assertSee('data-share-open="event-share"', false)
            ->assertSee('id="tour-map"', false)
            ->assertSee('stop-card-0', false)
            ->assertSee('Hot Chocolate Tent')
            ->assertSee('Carollers')
            ->assertSee('eventLightbox(', false)
            ->assertSee('Lights on Main')
            ->assertDontSee('aria-label="Event page layouts"', false);
    }

    public function test_host_initials_skip_small_words(): void
    {
        Setting::set('event_page_layout', 'booking');
        $event = Event::factory()->create(['organizer' => 'Town of Smithers']);

        $this->get(route('local-events.show', $event->slug))->assertSee('>TS</span>', false);
    }

    public function test_the_christmas_parade_page_uses_the_chosen_layout(): void
    {
        Setting::set('event_page_layout', 'editorial');
        Event::factory()->christmas()->create(['slug' => config('app.christmas_parade_slug')]);

        $this->get(route('christmas-parade'))->assertOk()->assertSee('data-layout="editorial"', false);
    }

    public function test_the_settings_page_lists_the_layouts_with_previews_of_the_next_event(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->admin())
            ->get(route('admin.settings.edit', ['tab' => 'events']))
            ->assertOk()
            ->assertSee('Event page layout')
            ->assertSeeInOrder(['Premium', 'Booking', 'Minimal', 'Editorial', 'Corporate'])
            ->assertSee('value="premium"', false)
            ->assertSee('Live')
            ->assertSee(route('local-events.show', $event->slug).'?layout=editorial', false);
    }

    public function test_the_settings_page_works_without_any_upcoming_event(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.settings.edit', ['tab' => 'events']))
            ->assertOk()
            ->assertSee('Event page layout')
            ->assertDontSee('?layout=', false);
    }

    public function test_admin_can_choose_a_layout(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), ['group' => 'events', 'event_page_layout' => 'booking', 'event_accent_color' => '#2C5F5D', 'event_christmas_accent_color' => '#B3202A'])
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'events']))
            ->assertSessionHasNoErrors();

        $this->assertSame('booking', setting('event_page_layout'));
        $this->get(route('local-events.show', $event->slug))->assertSee('data-layout="booking"', false);
    }

    public function test_unknown_layouts_are_rejected(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), ['group' => 'events', 'event_page_layout' => 'fancy'])
            ->assertSessionHasErrors('event_page_layout');

        $this->assertSame('premium', setting('event_page_layout'));
    }

    public function test_a_bad_stored_layout_falls_back_to_premium(): void
    {
        Setting::set('event_page_layout', 'removed-layout');
        $event = $this->makeEvent();

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('data-layout="premium"', false);
    }

    public function test_only_admins_can_preview_other_layouts(): void
    {
        $event = $this->makeEvent();
        $previewUrl = route('local-events.show', ['event' => $event->slug, 'layout' => 'corporate']);

        $this->get($previewUrl)
            ->assertOk()
            ->assertSee('data-layout="premium"', false)
            ->assertDontSee('aria-label="Event page layouts"', false);

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get($previewUrl)
            ->assertSee('data-layout="premium"', false)
            ->assertDontSee('aria-label="Event page layouts"', false);

        $this->actingAs($this->admin())
            ->get($previewUrl)
            ->assertOk()
            ->assertSee('data-layout="corporate"', false)
            ->assertSee('aria-label="Event page layouts"', false)
            ->assertSee('Use this layout')
            ->assertSee('name="event_page_layout" value="corporate"', false);
    }

    public function test_use_this_layout_keeps_the_other_event_settings(): void
    {
        Setting::set('event_accent_color', '#1D4ED8');
        $event = $this->makeEvent();

        $html = $this->actingAs($this->admin())
            ->get(route('local-events.show', ['event' => $event->slug, 'layout' => 'minimal']))
            ->assertSee('name="event_accent_color" value="#1D4ED8"', false)
            ->getContent();

        preg_match('~<form method="POST" action="'.preg_quote(route('admin.settings.update'), '~').'">(.*?)</form>~s', $html, $form);
        preg_match_all('~name="([^"]+)" value="([^"]*)"~', $form[1], $fields, PREG_SET_ORDER);
        $payload = collect($fields)->mapWithKeys(fn (array $field) => [$field[1] => $field[2]])->all();

        $this->put(route('admin.settings.update'), $payload)->assertSessionHasNoErrors();

        $this->assertSame('minimal', setting('event_page_layout'));
        $this->assertSame('#1D4ED8', setting('event_accent_color'));
    }

    public function test_previewing_the_live_layout_offers_no_switch(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->admin())
            ->get(route('local-events.show', ['event' => $event->slug, 'layout' => 'premium']))
            ->assertOk()
            ->assertSee('aria-label="Event page layouts"', false)
            ->assertDontSee('Use this layout');
    }

    public function test_an_unknown_preview_layout_shows_the_live_one(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->admin())
            ->get(route('local-events.show', ['event' => $event->slug, 'layout' => 'nope']))
            ->assertOk()
            ->assertSee('data-layout="premium"', false);
    }
}
