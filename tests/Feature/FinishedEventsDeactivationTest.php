<?php

namespace Tests\Feature;

use App\Models\CarouselSlide;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\User;
use App\Services\EventImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Finished events and calendar slides are switched off (not deleted) after their last
 * day; multi-date ones stay on until their final date; admins can switch them back on.
 */
class FinishedEventsDeactivationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * "Now" as a local (Smithers) time.
     */
    private function travelToLocal(string $localDateTime): void
    {
        Carbon::setTestNow(Carbon::parse($localDateTime, config('app.local_timezone')));
    }

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeCalendarSlide(string $caption, array $attributes = []): CarouselSlide
    {
        return CarouselSlide::create(array_merge([
            'filename' => str($caption)->slug().'.webp',
            'caption' => $caption,
            'is_active' => true,
            'show_on_calendar' => true,
        ], $attributes));
    }

    // ── Local events ────────────────────────────────────────────────────────

    public function test_a_one_day_event_is_switched_off_the_day_after_not_deleted(): void
    {
        $this->travelToLocal('2030-12-06 12:00');
        $event = Event::factory()->create(['event_date' => '2030-12-06', 'end_date' => null]);

        $this->assertSame(0, Event::deactivateFinished());
        $this->assertTrue($event->fresh()->is_active);

        $this->travelToLocal('2030-12-07 00:05');
        $this->artisan('events:expire')->assertSuccessful();

        $event->refresh();
        $this->assertFalse($event->is_active);
        $this->assertNotNull($event->auto_deactivated_at);
    }

    public function test_a_multi_day_event_stays_on_until_its_last_day(): void
    {
        $event = Event::factory()->create(['event_date' => '2030-12-05', 'end_date' => '2030-12-07']);

        foreach (['2030-12-06 09:00', '2030-12-07 23:59'] as $now) {
            $this->travelToLocal($now);
            Event::deactivateFinished();
            $this->assertTrue($event->fresh()->is_active, "Should still be on at {$now}");
        }

        $this->travelToLocal('2030-12-08 00:01');
        Event::deactivateFinished();
        $this->assertFalse($event->fresh()->is_active);
    }

    public function test_a_scheduled_event_stays_on_until_its_final_schedule_day(): void
    {
        $event = Event::factory()->create(Event::summaryFromDays([
            ['date' => '2030-12-05', 'start_time' => '10:00', 'end_time' => '16:00'],
            ['date' => '2030-12-19', 'start_time' => '12:00', 'end_time' => '20:00'],
        ]));
        EventDay::factory()->create(['event_id' => $event->id, 'date' => '2030-12-05']);
        EventDay::factory()->create(['event_id' => $event->id, 'date' => '2030-12-19']);

        $this->travelToLocal('2030-12-12 12:00');
        Event::deactivateFinished();
        $this->assertTrue($event->fresh()->is_active);

        $this->travelToLocal('2030-12-20 08:00');
        Event::deactivateFinished();
        $this->assertFalse($event->fresh()->is_active);
    }

    public function test_the_list_keeps_a_multi_day_event_until_its_last_day(): void
    {
        $this->travelToLocal('2030-12-06 12:00');
        Event::factory()->create(['title' => 'Winter Carnival', 'event_date' => '2030-12-05', 'end_date' => '2030-12-07']);
        Event::factory()->create(['title' => 'Yesterday Market', 'event_date' => '2030-12-05', 'end_date' => null]);

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee('Winter Carnival')
            ->assertDontSee('Yesterday Market');
    }

    public function test_finished_events_are_hidden_from_the_calendar_after_being_switched_off(): void
    {
        $this->travelToLocal('2030-12-10 12:00');
        $event = Event::factory()->create(['event_date' => '2030-12-05', 'end_date' => '2030-12-07']);

        $response = $this->get(route('events.index', ['view' => 'calendar', 'month' => 12, 'year' => 2030]))->assertOk();

        $this->assertFalse($event->fresh()->is_active);
        $this->assertStringNotContainsString('openEventModal('.$event->id.')', $response->getContent());
        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_an_admin_can_switch_a_finished_event_back_on_and_it_stays_on(): void
    {
        $this->travelToLocal('2030-12-10 12:00');
        $event = Event::factory()->create(['event_date' => '2030-12-05', 'end_date' => '2030-12-07']);
        Event::deactivateFinished();

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.bulk-action'), ['action' => 'activate', 'ids' => [$event->id]])
            ->assertRedirect();

        Event::deactivateFinished();
        $this->assertTrue($event->fresh()->is_active);
        $this->assertSame(3, substr_count(
            $this->get(route('events.index', ['view' => 'calendar', 'month' => 12, 'year' => 2030]))->getContent(),
            'openEventModal('.$event->id.')',
        ));
    }

    public function test_moving_a_finished_event_to_new_dates_lets_it_finish_again(): void
    {
        $this->travelToLocal('2030-12-10 12:00');
        $event = Event::factory()->create(['event_date' => '2030-12-05']);
        Event::deactivateFinished();
        $this->assertNotNull($event->fresh()->auto_deactivated_at);

        $event->fresh()->update(['event_date' => '2030-12-20', 'is_active' => true]);
        $this->assertNull($event->fresh()->auto_deactivated_at);

        $this->travelToLocal('2030-12-21 08:00');
        Event::deactivateFinished();
        $this->assertFalse($event->fresh()->is_active);
    }

    public function test_the_admin_list_shows_finished_events_as_ended_and_inactive(): void
    {
        $this->travelToLocal('2030-12-10 12:00');
        Event::factory()->create(['title' => 'Winter Carnival', 'event_date' => '2030-12-05']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.local-events.index', ['when' => 'past']))
            ->assertOk()
            ->assertSee('Winter Carnival')
            ->assertSee('Ended · Inactive');
    }

    public function test_the_scraper_cleanup_respects_multi_day_events(): void
    {
        $this->travelToLocal('2030-12-06 12:00');
        $running = Event::factory()->imported()->create(['event_date' => '2030-12-05', 'end_date' => '2030-12-07']);
        $done = Event::factory()->imported()->create(['event_date' => '2030-12-04']);

        $this->assertSame(1, app(EventImportService::class)->deactivatePastEvents());
        $this->assertTrue($running->fresh()->is_active);
        $this->assertFalse($done->fresh()->is_active);
    }

    // ── Carousel slides on the calendar ─────────────────────────────────────

    public function test_a_calendar_slide_is_switched_off_in_slideshow_and_calendar_after_its_last_day(): void
    {
        $slide = $this->makeCalendarSlide('Truck Parade', ['calendar_date' => '2030-12-05', 'calendar_end_date' => '2030-12-07']);

        $this->travelToLocal('2030-12-07 20:00');
        CarouselSlide::deactivateExpired();
        $this->assertTrue($slide->fresh()->is_active);
        $this->assertContains($slide->id, CarouselSlide::currentlyActive()->pluck('id')->all());

        $this->travelToLocal('2030-12-08 00:05');
        $this->artisan('carousel:expire')->assertSuccessful();

        $slide->refresh();
        $this->assertFalse($slide->is_active);
        $this->assertNotNull($slide->calendar_ended_at);
        $this->assertNotContains($slide->id, CarouselSlide::currentlyActive()->pluck('id')->all());
        $this->assertCount(0, CarouselSlide::onCalendarInMonth(2030, 12)->get());
        $this->assertDatabaseHas('carousel_slides', ['id' => $slide->id]);
    }

    public function test_a_slide_out_of_the_slideshow_stays_on_the_calendar_until_its_event_is_over(): void
    {
        $slide = $this->makeCalendarSlide('Truck Parade', [
            'starts_at' => '2030-11-20',
            'ends_at' => '2030-12-01',
            'calendar_date' => '2030-12-06',
        ]);

        $this->travelToLocal('2030-12-03 12:00');
        CarouselSlide::deactivateExpired();

        $slide->refresh();
        $this->assertTrue($slide->is_active);
        $this->assertNotContains($slide->id, CarouselSlide::currentlyActive()->pluck('id')->all());
        $this->assertCount(1, CarouselSlide::onCalendarInMonth(2030, 12)->get());

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.carousel.index'))
            ->assertSee('Calendar only');
    }

    public function test_an_admin_can_switch_a_finished_calendar_slide_back_on_and_it_stays_on(): void
    {
        $slide = $this->makeCalendarSlide('Truck Parade', ['calendar_date' => '2030-12-06']);
        $this->travelToLocal('2030-12-10 12:00');
        CarouselSlide::deactivateExpired();

        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->get(route('admin.carousel.index', ['status' => 'inactive']))
            ->assertSee('Event ended Dec 6');

        $this->actingAs($admin)
            ->patch(route('admin.carousel.update', $slide), ['caption' => 'Truck Parade', 'sort_order' => 0, 'is_active' => '1'])
            ->assertSessionHasNoErrors();

        CarouselSlide::deactivateExpired();
        $this->assertTrue($slide->fresh()->is_active);
        $this->assertCount(1, CarouselSlide::onCalendarInMonth(2030, 12)->get());
    }

    public function test_new_future_calendar_dates_let_a_slide_finish_again(): void
    {
        $slide = $this->makeCalendarSlide('Truck Parade', ['calendar_date' => '2030-12-06']);
        $this->travelToLocal('2030-12-10 12:00');
        CarouselSlide::deactivateExpired();

        $slide->fresh()->update(['calendar_date' => '2030-12-20', 'is_active' => true]);
        $this->assertNull($slide->fresh()->calendar_ended_at);

        $this->travelToLocal('2030-12-21 08:00');
        CarouselSlide::deactivateExpired();
        $this->assertFalse($slide->fresh()->is_active);
    }
}
