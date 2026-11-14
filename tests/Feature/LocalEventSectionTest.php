<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventStop;
use App\Models\Tour;
use App\Models\Town;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Events are their own module (events table, /admin/local-events), separate
 * from tours, and feed /local-events, town pages and the /events calendar.
 */
class LocalEventSectionTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeTour(array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'title' => 'Houston Waterfalls Tour',
            'tour_type' => 'waterfalls',
            'is_active' => true,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Winter Carnival',
            'event_date' => now()->addDays(5)->toDateString(),
            'event_time' => '18:30',
            'end_time' => '21:00',
            'venue' => 'Bovill Square',
            'category' => 'Community',
            'external_url' => 'https://www.facebook.com/events/123',
            'theme' => Event::THEME_STANDARD,
            'is_active' => '1',
        ], $overrides);
    }

    public function test_events_page_lists_current_active_events_sorted_by_date(): void
    {
        Event::factory()->create(['title' => 'Later Event', 'event_date' => now()->addDays(20)->toDateString()]);
        Event::factory()->create(['title' => 'Sooner Event', 'event_date' => now()->addDays(2)->toDateString()]);
        Event::factory()->create(['title' => 'Ongoing Fair', 'event_date' => now()->subDays(2)->toDateString(), 'end_date' => now()->addDay()->toDateString()]);
        Event::factory()->past()->create(['title' => 'Finished Event']);
        Event::factory()->inactive()->create(['title' => 'Hidden Draft Event']);
        $this->makeTour();

        $this->get(route('local-events.index'))
            ->assertOk()
            ->assertSeeInOrder(['Ongoing Fair', 'Sooner Event', 'Later Event'])
            ->assertSee(route('local-events.show', Event::where('title', 'Sooner Event')->value('slug')), false)
            ->assertDontSee('Finished Event')
            ->assertDontSee('Hidden Draft Event')
            ->assertDontSee('Houston Waterfalls Tour');
    }

    public function test_featured_events_are_listed_first(): void
    {
        Event::factory()->create(['title' => 'Regular Soon', 'event_date' => now()->addDay()->toDateString()]);
        Event::factory()->create(['title' => 'Featured Later', 'event_date' => now()->addDays(9)->toDateString(), 'is_featured' => true]);

        $this->get(route('local-events.index'))->assertSeeInOrder(['Featured Later', 'Regular Soon']);
    }

    public function test_month_filter_lists_months_with_counts_and_filters_events(): void
    {
        $this->travelTo(now()->setDate(2030, 11, 10));
        Event::factory()->create(['title' => 'November Market', 'event_date' => '2030-11-20']);
        Event::factory()->create(['title' => 'Christmas Parade', 'event_date' => '2030-12-05']);
        Event::factory()->create(['title' => 'Winter Lights', 'event_date' => '2030-11-28', 'end_date' => '2031-01-03']);
        Event::factory()->create(['title' => 'Ski Swap', 'event_date' => '2031-01-15']);

        $this->get(route('local-events.index'))
            ->assertOk()
            ->assertSee('Browse by month')
            ->assertSee('aria-label="Previous months"', false)
            ->assertSee('aria-label="Next months"', false)
            ->assertSeeInOrder(['November', 'December', 'January 2031'])
            ->assertSee(route('local-events.index', ['month' => '2030-12']), false)
            ->assertSee('November Market')
            ->assertSee('Ski Swap');

        $this->get(route('local-events.index', ['month' => '2030-12']))
            ->assertOk()
            ->assertSee('aria-current="page"', false)
            ->assertSee('Christmas Parade')
            ->assertSee('Winter Lights')
            ->assertDontSee('November Market')
            ->assertDontSee('Ski Swap')
            ->assertSee('Clear filter');

        $this->get(route('local-events.index', ['month' => '2031-01']))
            ->assertSee('Winter Lights')
            ->assertSee('Ski Swap')
            ->assertDontSee('Christmas Parade');
    }

    public function test_ongoing_event_counts_from_the_current_month(): void
    {
        $this->travelTo(now()->setDate(2030, 11, 10));
        Event::factory()->create(['title' => 'Long Exhibit', 'event_date' => '2030-09-01', 'end_date' => '2030-12-15']);
        Event::factory()->create(['title' => 'December Show', 'event_date' => '2030-12-10']);

        $this->get(route('local-events.index'))
            ->assertSee(route('local-events.index', ['month' => '2030-11']), false)
            ->assertDontSee(route('local-events.index', ['month' => '2030-09']), false);

        $this->get(route('local-events.index', ['month' => '2030-11']))
            ->assertSee('Long Exhibit')
            ->assertDontSee('December Show');
    }

    public function test_unknown_month_shows_all_events_and_single_month_hides_the_filter(): void
    {
        $this->travelTo(now()->setDate(2030, 11, 10));
        Event::factory()->create(['title' => 'Only Event', 'event_date' => '2030-11-20']);

        $this->get(route('local-events.index', ['month' => 'garbage']))
            ->assertOk()
            ->assertSee('Only Event')
            ->assertDontSee('Browse by month');

        $this->get(route('local-events.index', ['month' => '2031-05']))
            ->assertOk()
            ->assertSee('Only Event');
    }

    public function test_tours_page_does_not_list_events(): void
    {
        Event::factory()->create(['title' => 'Christmas in the Valley']);
        $this->makeTour();

        $this->get(route('tours.index'))
            ->assertOk()
            ->assertSee('Houston Waterfalls Tour')
            ->assertDontSee('Christmas in the Valley');
    }

    public function test_navbar_links_to_events(): void
    {
        $this->get(route('tours.index'))
            ->assertOk()
            ->assertSee(route('local-events.index'), false);
    }

    public function test_event_page_shows_details_more_info_and_calendar_link(): void
    {
        $event = Event::factory()->create([
            'title' => 'Winter Carnival',
            'event_date' => '2030-12-05',
            'event_time' => '18:30',
            'end_time' => '21:00',
            'venue' => 'Bovill Square',
            'organizer' => 'Smithers Chamber',
            'external_url' => 'https://www.facebook.com/events/123',
        ]);

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('All Events')
            ->assertSee('Thursday, Dec 5, 2030')
            ->assertSee('6:30 PM – 9:00 PM')
            ->assertSee('Bovill Square')
            ->assertSee('Smithers Chamber')
            ->assertSee('More info')
            ->assertSee('https://www.facebook.com/events/123', false)
            ->assertSee(route('events.calendar', $event->id), false);
    }

    public function test_event_page_is_a_landing_page_in_the_site_brand(): void
    {
        $event = Event::factory()->create([
            'title' => 'Winter Carnival',
            'organizer' => 'Smithers Chamber',
            'is_featured' => true,
        ]);

        $response = $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('★ Featured')
            ->assertSee('Hosted by')
            ->assertSee('What to expect')
            ->assertSee('Save the date')
            ->assertSee('bg-forest-900', false)
            ->assertSee('bg-sand-100', false)
            ->assertSee('event-eyebrow', false)
            ->assertSee('font-display', false)
            ->assertSee('data-share-open="event-share"', false);

        $this->assertStringNotContainsString('gradient', Str::between($response->getContent(), 'class="event-page', '<footer'));
    }

    public function test_the_activity_list_beside_the_map_uses_the_slim_scrollbar(): void
    {
        $event = Event::factory()->create();
        EventStop::factory()->for($event)->count(3)->create();

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('class="event-scroll ', false)
            ->assertSee('.event-scroll::-webkit-scrollbar { width: 4px; }', false);
    }

    public function test_ended_event_says_so_and_drops_the_save_the_date_band(): void
    {
        $event = Event::factory()->create(['event_date' => now()->subWeek()->toDateString()]);

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('This event has ended')
            ->assertDontSee('Save the date');
    }

    public function test_add_to_calendar_is_the_main_action_without_a_more_info_link(): void
    {
        $event = Event::factory()->create(['external_url' => null, 'venue' => null, 'location' => null]);

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertDontSee('More info')
            ->assertSee(route('events.calendar', $event->id), false);
    }

    public function test_event_without_a_cover_image_shows_its_icon_instead(): void
    {
        $event = Event::factory()->create(['cover_image' => null, 'image_url' => null, 'icon' => '🎿']);

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertDontSee('og:image', false)
            ->assertSee('🎿');
    }

    public function test_event_page_hides_the_map_when_there_is_nothing_to_show(): void
    {
        $event = Event::factory()->create();

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertDontSee('id="tour-map"', false)
            ->assertDontSee('mapbox-gl.js', false);
    }

    public function test_event_page_shows_the_map_when_it_has_a_route(): void
    {
        $event = Event::factory()->withRoute()->create();

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('id="tour-map"', false)
            ->assertSee('1.2 km');
    }

    public function test_inactive_or_unknown_event_returns_404(): void
    {
        $event = Event::factory()->inactive()->create();

        $this->get(route('local-events.show', $event->slug))->assertNotFound();
        $this->get(route('local-events.embed', $event->slug))->assertNotFound();
        $this->get('/local-events/does-not-exist')->assertNotFound();
    }

    public function test_events_are_looked_up_by_slug_on_event_pages_and_by_id_on_the_calendar(): void
    {
        $event = Event::factory()->create(['title' => 'Light Up Night', 'event_time' => '19:00']);

        $this->get('/local-events/light-up-night')->assertOk()->assertSee('Light Up Night');
        $this->get('/local-events/'.$event->id)->assertNotFound();
        $this->get(route('events.details', $event->id))->assertOk()->assertJsonFragment(['title' => 'Light Up Night']);
        $this->get(route('events.calendar', $event->id))->assertOk();
    }

    public function test_manually_added_event_appears_on_the_events_calendar(): void
    {
        Event::factory()->create(['title' => 'Light Up Night', 'event_date' => now()->addDays(3)->toDateString()]);

        $this->get(route('events.index'))->assertOk()->assertSee('Light Up Night');
    }

    public function test_old_links_to_event_tours_redirect_to_the_event_page(): void
    {
        $legacy = Tour::create(['title' => 'Christmas in the Valley', 'tour_type' => Tour::LEGACY_EVENT_TYPE, 'is_active' => true]);

        $this->get(route('tours.show', $legacy))->assertRedirect(route('local-events.show', 'christmas-in-the-valley'));
        $this->get(route('tours.embed', $legacy))->assertRedirect(route('local-events.embed', 'christmas-in-the-valley'));
        $this->get(route('tours.index'))->assertDontSee('Christmas in the Valley');
    }

    public function test_admin_events_list_shows_events_not_tours(): void
    {
        Event::factory()->create(['title' => 'Christmas in the Valley']);
        Event::factory()->imported()->create(['title' => 'Imported Concert']);
        $this->makeTour();

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.local-events.index'))
            ->assertOk()
            ->assertSee('Christmas in the Valley')
            ->assertSee('Imported Concert')
            ->assertSee('Imported')
            ->assertSee('Add Event')
            ->assertDontSee('Houston Waterfalls Tour');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.tours.index'))
            ->assertOk()
            ->assertSee('Houston Waterfalls Tour')
            ->assertDontSee('Christmas in the Valley');
    }

    public function test_admin_events_list_filters_past_and_source(): void
    {
        Event::factory()->create(['title' => 'Upcoming Manual']);
        Event::factory()->imported()->create(['title' => 'Upcoming Imported']);
        Event::factory()->past()->create(['title' => 'Old Market']);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('admin.local-events.index'))
            ->assertSee('Upcoming Manual')->assertDontSee('Old Market');
        $this->actingAs($admin)->get(route('admin.local-events.index', ['when' => 'past']))
            ->assertSee('Old Market')->assertDontSee('Upcoming Manual');
        $this->actingAs($admin)->get(route('admin.local-events.index', ['source' => 'manual']))
            ->assertSee('Upcoming Manual')->assertDontSee('Upcoming Imported');
        $this->actingAs($admin)->get(route('admin.local-events.index', ['q' => 'Imported']))
            ->assertSee('Upcoming Imported')->assertDontSee('Upcoming Manual');
    }

    public function test_admin_sidebar_links_to_events(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.tours.index'))
            ->assertSee(route('admin.local-events.index'), false);
    }

    public function test_admin_event_form_loads(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.local-events.create'))
            ->assertOk()
            ->assertSee('Event Title')
            ->assertSee('Start date')
            ->assertSee('Pin activity on map')
            ->assertSee('christmas-truck-parade')
            ->assertDontSee('name="tour_type"', false);
    }

    public function test_tour_form_no_longer_offers_the_christmas_type(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.tours.create'))
            ->assertOk()
            ->assertSee('value="waterfalls"', false)
            ->assertDontSee('value="christmas"', false);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.tours.store'), ['title' => 'Sneaky Event', 'tour_type' => 'christmas'])
            ->assertSessionHasErrors('tour_type');

        $this->assertDatabaseCount('tours', 0);
    }

    public function test_admin_can_create_an_event(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->eventPayload())
            ->assertRedirect(route('admin.local-events.index'))
            ->assertSessionHasNoErrors();

        $event = Event::where('title', 'Winter Carnival')->firstOrFail();
        $this->assertSame('winter-carnival', $event->slug);
        $this->assertNull($event->source_id);
        $this->assertTrue($event->isManual());
        $this->assertSame('18:30', $event->event_time->format('H:i'));
        $this->assertSame('Bovill Square', $event->location, 'location falls back to the venue for the calendar');
        $this->assertTrue($event->is_active);
    }

    public function test_slug_is_kept_unique_and_can_be_set(): void
    {
        $admin = $this->makeAdmin();
        Event::factory()->create(['title' => 'Winter Carnival']);

        $this->actingAs($admin)->post(route('admin.local-events.store'), $this->eventPayload());
        $this->assertTrue(Event::where('slug', 'winter-carnival-2')->exists());

        $this->actingAs($admin)->post(route('admin.local-events.store'), $this->eventPayload(['title' => 'Parade', 'slug' => 'Christmas Truck Parade']));
        $this->assertTrue(Event::where('slug', 'christmas-truck-parade')->exists());

        $this->actingAs($admin)
            ->post(route('admin.local-events.store'), $this->eventPayload(['title' => 'Copy', 'slug' => 'winter-carnival']))
            ->assertSessionHasErrors('slug');
    }

    public function test_event_requires_a_start_date_and_a_valid_end_date(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.local-events.store'), $this->eventPayload(['event_date' => '']))
            ->assertSessionHasErrors('event_date');

        $this->actingAs($admin)
            ->post(route('admin.local-events.store'), $this->eventPayload(['event_date' => '2030-12-05', 'end_date' => '2030-12-01']))
            ->assertSessionHasErrors('end_date');

        $this->actingAs($admin)
            ->post(route('admin.local-events.store'), $this->eventPayload(['external_url' => 'not a link', 'theme' => 'easter']))
            ->assertSessionHasErrors(['external_url', 'theme']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_admin_event_map_defaults_to_satellite_with_a_layer_toggle(): void
    {
        $event = Event::factory()->create();

        $this->actingAs($this->makeAdmin())->get(route('admin.local-events.edit', $event))
            ->assertOk()
            ->assertSee('data-default-layer="satellite"', false)
            ->assertSee("tourRouteEditor.setLayer('standard')", false)
            ->assertSee("tourRouteEditor.setLayer('satellite')", false)
            ->assertSee('mapbox://styles/mapbox/satellite-streets-v12', false);
    }

    public function test_admin_can_edit_an_imported_event(): void
    {
        $event = Event::factory()->imported()->create(['title' => 'Imported Concert']);
        $sourceId = $event->source_id;

        $this->actingAs($this->makeAdmin())->get(route('admin.local-events.edit', $event))
            ->assertOk()
            ->assertSee('imported from SmithersEvents.com');

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.local-events.update', $event), $this->eventPayload(['title' => 'Imported Concert (moved)']))
            ->assertRedirect(route('admin.local-events.index'));

        $event->refresh();
        $this->assertSame('Imported Concert (moved)', $event->title);
        $this->assertSame($sourceId, $event->source_id);
    }

    public function test_bulk_actions_update_and_delete_each_event(): void
    {
        [$first, $second, $third] = Event::factory()->count(3)->create();

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.bulk-action'), ['action' => 'deactivate', 'ids' => [$first->id, $second->id]])
            ->assertRedirect(route('admin.local-events.index'));

        $this->assertFalse($first->fresh()->is_active);
        $this->assertFalse($second->fresh()->is_active);
        $this->assertTrue($third->fresh()->is_active);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.bulk-action'), ['action' => 'delete', 'ids' => [$first->id]]);
        $this->assertModelMissing($first);
    }

    public function test_admin_can_delete_event_with_an_apostrophe_in_its_title(): void
    {
        $event = Event::factory()->create(['title' => "Santa's Workshop"]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.local-events.index'))
            ->assertOk()
            ->assertSee("confirmDelete(event, 'Santa\\u0027s Workshop')", false);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.local-events.destroy', $event))
            ->assertRedirect(route('admin.local-events.index'));

        $this->assertModelMissing($event);
    }

    public function test_non_admins_cannot_manage_events(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->post(route('admin.local-events.store'), $this->eventPayload())
            ->assertRedirect();

        $this->assertDatabaseCount('events', 0);
    }

    public function test_sitemap_lists_events_and_tours_under_their_own_urls(): void
    {
        $event = Event::factory()->create();
        Event::factory()->past()->create(['title' => 'Finished Event']);
        $tour = $this->makeTour();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('local-events.index'), false)
            ->assertSee(route('local-events.show', $event->slug), false)
            ->assertDontSee('finished-event', false)
            ->assertSee(route('tours.show', $tour), false);
    }

    public function test_town_page_lists_events_linked_to_the_town_and_links_to_their_pages(): void
    {
        $town = Town::factory()->create(['name' => 'Telkwa', 'is_active' => true]);
        $linked = Event::factory()->create(['title' => 'Telkwa Barbecue', 'town_id' => $town->id, 'location' => 'Riverside Park', 'venue' => null]);
        Event::factory()->create(['title' => 'Other Town Event', 'location' => 'Houston, BC', 'venue' => null]);

        $this->get(route('towns.show', $town))
            ->assertOk()
            ->assertSee('Telkwa Barbecue')
            ->assertSee(route('local-events.show', $linked->slug), false)
            ->assertDontSee('Other Town Event');
    }
}
