<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalEventSectionTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeEvent(array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'title' => 'Christmas in the Valley',
            'tour_type' => Tour::EVENT_TYPE,
            'is_active' => true,
        ], $overrides));
    }

    private function makeTour(array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'title' => 'Houston Waterfalls Tour',
            'tour_type' => 'waterfalls',
            'is_active' => true,
        ], $overrides));
    }

    public function test_events_page_lists_only_active_events(): void
    {
        $this->makeEvent();
        $this->makeEvent(['title' => 'Hidden Draft Event', 'is_active' => false]);
        $this->makeTour();

        $this->get(route('local-events.index'))
            ->assertOk()
            ->assertSee('Christmas in the Valley')
            ->assertSee(route('local-events.show', 'christmas-in-the-valley'), false)
            ->assertDontSee('Hidden Draft Event')
            ->assertDontSee('Houston Waterfalls Tour');
    }

    public function test_tours_page_no_longer_lists_events(): void
    {
        $this->makeEvent();
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
            ->assertSee(route('local-events.index'), false)
            ->assertSee('Events');
    }

    public function test_event_page_links_back_to_all_events(): void
    {
        $event = $this->makeEvent();

        $this->get(route('local-events.show', $event))
            ->assertOk()
            ->assertSee('All Events')
            ->assertSee(route('local-events.index'), false);
    }

    public function test_old_tour_links_to_an_event_redirect_to_the_events_page(): void
    {
        $event = $this->makeEvent();

        $this->get(route('tours.show', $event))->assertRedirect(route('local-events.show', $event));
        $this->get(route('tours.embed', $event))->assertRedirect(route('local-events.embed', $event));
    }

    public function test_events_url_does_not_show_regular_tours(): void
    {
        $tour = $this->makeTour();

        $this->get(route('local-events.show', $tour))->assertNotFound();
        $this->get(route('local-events.embed', $tour))->assertNotFound();
    }

    public function test_scraped_calendar_events_page_is_untouched(): void
    {
        $this->get(route('events.index'))->assertOk();
    }

    public function test_admin_events_list_shows_only_events(): void
    {
        $this->makeEvent();
        $this->makeTour();

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.local-events.index'))
            ->assertOk()
            ->assertSee('Christmas in the Valley')
            ->assertSee('Add Event')
            ->assertDontSee('Houston Waterfalls Tour');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.tours.index'))
            ->assertOk()
            ->assertSee('Houston Waterfalls Tour')
            ->assertDontSee('Christmas in the Valley');
    }

    public function test_admin_sidebar_links_to_events(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.tours.index'))
            ->assertSee(route('admin.local-events.index'), false);
    }

    public function test_admin_event_form_only_offers_the_event_type(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.local-events.create'))
            ->assertOk()
            ->assertSee('Event Title')
            ->assertSee('value="'.Tour::EVENT_TYPE.'" selected', false)
            ->assertDontSee('value="waterfalls"', false);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.tours.create'))
            ->assertOk()
            ->assertSee('value="waterfalls"', false)
            ->assertDontSee('value="'.Tour::EVENT_TYPE.'"', false);
    }

    public function test_admin_can_create_event_in_events_section(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), ['title' => 'Winter Carnival', 'tour_type' => Tour::EVENT_TYPE])
            ->assertRedirect(route('admin.local-events.index'));

        $this->assertTrue(Tour::where('title', 'Winter Carnival')->firstOrFail()->isEvent());
    }

    public function test_type_must_belong_to_the_section(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.tours.store'), ['title' => 'Sneaky Event', 'tour_type' => Tour::EVENT_TYPE])
            ->assertSessionHasErrors('tour_type');

        $this->actingAs($admin)
            ->post(route('admin.local-events.store'), ['title' => 'Sneaky Tour', 'tour_type' => 'scenic'])
            ->assertSessionHasErrors('tour_type');

        $this->assertDatabaseCount('tours', 0);
    }

    public function test_editing_from_the_wrong_section_redirects(): void
    {
        $admin = $this->makeAdmin();
        $event = $this->makeEvent();
        $tour = $this->makeTour();

        $this->actingAs($admin)->get(route('admin.tours.edit', $event))
            ->assertRedirect(route('admin.local-events.edit', $event));
        $this->actingAs($admin)->get(route('admin.local-events.edit', $tour))
            ->assertRedirect(route('admin.tours.edit', $tour));
    }

    public function test_cannot_update_or_delete_from_the_wrong_section(): void
    {
        $admin = $this->makeAdmin();
        $event = $this->makeEvent();

        $this->actingAs($admin)
            ->put(route('admin.tours.update', $event), ['title' => 'Changed', 'tour_type' => Tour::EVENT_TYPE])
            ->assertNotFound();
        $this->actingAs($admin)
            ->delete(route('admin.tours.destroy', $event))
            ->assertNotFound();

        $this->assertSame('Christmas in the Valley', $event->fresh()->title);
    }

    public function test_bulk_actions_only_touch_their_own_section(): void
    {
        $event = $this->makeEvent();
        $tour = $this->makeTour();

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.bulk-action'), ['action' => 'deactivate', 'ids' => [$event->id, $tour->id]])
            ->assertRedirect(route('admin.local-events.index'));

        $this->assertFalse($event->fresh()->is_active);
        $this->assertTrue($tour->fresh()->is_active);
    }

    public function test_admin_can_delete_event_with_an_apostrophe_in_its_title(): void
    {
        $event = $this->makeEvent(['title' => "Santa's Workshop"]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.local-events.index'))
            ->assertOk()
            ->assertSee("confirmDelete(event, 'Santa\\u0027s Workshop')", false);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.local-events.destroy', $event))
            ->assertRedirect(route('admin.local-events.index'));

        $this->assertModelMissing($event);
    }

    public function test_sitemap_lists_events_under_their_own_urls(): void
    {
        $event = $this->makeEvent();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('local-events.index'), false)
            ->assertSee(route('local-events.show', $event), false)
            ->assertDontSee(route('tours.show', $event), false);
    }
}
