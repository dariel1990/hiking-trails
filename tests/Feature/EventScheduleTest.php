<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Events with a per-day schedule: any dates, each with its own hours.
 */
class EventScheduleTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function schedulePayload(array $days, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Christmas Market',
            'theme' => Event::THEME_STANDARD,
            'is_active' => '1',
            'schedule_mode' => 'days',
            'days' => $days,
        ], $overrides);
    }

    // ── Saving ───────────────────────────────────────────────────────────────

    public function test_admin_can_save_days_and_the_summary_columns_follow_them(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->schedulePayload([
                ['date' => '2030-12-19', 'start_time' => '12:00', 'end_time' => '20:00', 'note' => 'Late night'],
                ['date' => '2030-12-05', 'start_time' => '10:00', 'end_time' => '16:00', 'note' => ''],
                ['date' => '2030-12-12', 'start_time' => '', 'end_time' => '', 'note' => ''],
            ], [
                // Stale single-mode values are ignored in days mode.
                'event_date' => '2031-01-01',
                'end_date' => 'not a date',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.local-events.index'));

        $event = Event::where('title', 'Christmas Market')->firstOrFail();
        $this->assertSame('2030-12-05', $event->event_date->toDateString());
        $this->assertSame('2030-12-19', $event->end_date->toDateString());
        $this->assertSame('10:00', $event->event_time->format('H:i'));
        $this->assertSame('16:00', $event->end_time->format('H:i'));

        $days = $event->days;
        $this->assertSame(['2030-12-05', '2030-12-12', '2030-12-19'], $days->map(fn ($d) => $d->date->toDateString())->all());
        $this->assertNull($days[1]->start_time);
        $this->assertNull($days[0]->note);
        $this->assertSame('Late night', $days[2]->note);
    }

    public function test_a_single_day_schedule_has_no_end_date(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->schedulePayload([
                ['date' => '2030-12-05', 'start_time' => '18:00', 'end_time' => '21:00'],
            ]))
            ->assertSessionHasNoErrors();

        $event = Event::firstOrFail();
        $this->assertNull($event->end_date);
        $this->assertCount(1, $event->days);
    }

    public function test_switching_back_to_same_hours_removes_the_days(): void
    {
        $event = Event::factory()->withDays([
            ['date' => '2030-12-05', 'start_time' => '10:00', 'end_time' => '16:00'],
            ['date' => '2030-12-06', 'start_time' => '12:00', 'end_time' => '20:00'],
        ])->create();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.local-events.update', $event), [
                'title' => $event->title,
                'theme' => Event::THEME_STANDARD,
                'schedule_mode' => 'single',
                'event_date' => '2030-12-05',
                'end_date' => '',
                'event_time' => '09:00',
                'end_time' => '',
                'days' => [['date' => '2030-12-09']],
            ])
            ->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertCount(0, $event->days);
        $this->assertNull($event->end_date);
        $this->assertSame('09:00', $event->event_time->format('H:i'));
    }

    public function test_events_saved_without_a_mode_keep_working_as_before(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), [
                'title' => 'Plain Event',
                'theme' => Event::THEME_STANDARD,
                'event_date' => '2030-12-05',
                'event_time' => '18:30',
            ])
            ->assertSessionHasNoErrors();

        $event = Event::firstOrFail();
        $this->assertFalse($event->hasSchedule());
        $this->assertSame('6:30 PM', $event->time_range_label);
    }

    // ── Validation ───────────────────────────────────────────────────────────

    public function test_days_mode_needs_at_least_one_day(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->schedulePayload([]))
            ->assertSessionHasErrors(['days' => 'Add at least one day to the schedule.']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_each_day_needs_a_unique_date_and_valid_times(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.local-events.store'), $this->schedulePayload([
                ['date' => '2030-12-05'],
                ['date' => '2030-12-05'],
            ]))
            ->assertSessionHasErrors(['days.0.date' => 'The same date is listed twice in the schedule.']);

        $this->actingAs($admin)
            ->post(route('admin.local-events.store'), $this->schedulePayload([
                ['date' => '', 'start_time' => '25:99'],
            ]))
            ->assertSessionHasErrors(['days.0.date', 'days.0.start_time']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_a_schedule_can_have_at_most_sixty_days(): void
    {
        $days = collect(range(0, 60))->map(fn ($i) => ['date' => now()->addDays($i + 1)->toDateString()])->all();

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->schedulePayload($days))
            ->assertSessionHasErrors(['days' => 'A schedule can have at most 60 days.']);
    }

    // ── Labels ───────────────────────────────────────────────────────────────

    public function test_dates_with_gaps_are_listed_and_consecutive_days_are_a_range(): void
    {
        $gaps = Event::factory()->withDays([
            ['date' => '2030-12-05'], ['date' => '2030-12-12'], ['date' => '2030-12-19'],
        ])->create();
        $this->assertSame('Dec 5, 12 & 19, 2030', $gaps->date_range_label);

        $acrossMonths = Event::factory()->withDays([
            ['date' => '2030-11-28'], ['date' => '2030-12-05'],
        ])->create();
        $this->assertSame('Nov 28 & Dec 5, 2030', $acrossMonths->date_range_label);

        $consecutive = Event::factory()->withDays([
            ['date' => '2030-12-05'], ['date' => '2030-12-06'], ['date' => '2030-12-07'],
        ])->create();
        $this->assertSame('Dec 5 – Dec 7, 2030', $consecutive->date_range_label);
    }

    public function test_time_label_lists_each_days_hours_only_when_they_differ(): void
    {
        $varies = Event::factory()->withDays([
            ['date' => '2030-12-05', 'start_time' => '10:00', 'end_time' => '16:00'],
            ['date' => '2030-12-06', 'start_time' => '12:00', 'end_time' => '20:00'],
            ['date' => '2030-12-07'],
        ])->create();
        $this->assertSame('Dec 5: 10 AM – 4 PM, Dec 6: 12 PM – 8 PM, Dec 7: All day', $varies->time_range_label);

        $same = Event::factory()->withDays([
            ['date' => '2030-12-05', 'start_time' => '10:00', 'end_time' => '16:00'],
            ['date' => '2030-12-06', 'start_time' => '10:00', 'end_time' => '16:00'],
        ])->create();
        $this->assertSame('10:00 AM – 4:00 PM', $same->time_range_label);
    }

    // ── Public pages ─────────────────────────────────────────────────────────

    public function test_event_page_shows_each_days_hours_in_the_time_line(): void
    {
        $first = now()->addDays(3);
        $event = Event::factory()->withDays([
            ['date' => $first->toDateString(), 'start_time' => '19:00', 'end_time' => '21:00'],
            ['date' => $first->copy()->addDay()->toDateString(), 'start_time' => '14:00', 'end_time' => '16:00'],
        ])->create();

        $second = $first->copy()->addDay();

        foreach (array_keys(config('settings.definitions.event_page_layout.options')) as $layout) {
            Setting::set('event_page_layout', $layout);

            $this->get(route('local-events.show', $event->slug))
                ->assertOk()
                ->assertSee('aria-label="Hours by day"', false)
                ->assertSeeInOrder([
                    $first->format('D').', '.$first->format('M j'), '7 PM – 9 PM',
                    $second->format('D').', '.$second->format('M j'), '2 PM – 4 PM',
                ])
                ->assertDontSee('Times vary by day');
        }
    }

    public function test_event_cards_show_one_tag_per_day(): void
    {
        $first = now()->addDays(3);
        Event::factory()->withDays([
            ['date' => $first->toDateString(), 'start_time' => '19:00', 'end_time' => '21:00'],
            ['date' => $first->copy()->addDay()->toDateString(), 'start_time' => '14:00', 'end_time' => '16:00'],
        ])->create();

        $this->get(route('local-events.index'))
            ->assertOk()
            ->assertSeeInOrder([$first->format('D').' '.$first->format('M j'), '7 PM – 9 PM']);
    }

    public function test_calendar_download_has_one_entry_per_day(): void
    {
        $event = Event::factory()->withDays([
            ['date' => '2030-12-05', 'start_time' => '10:00', 'end_time' => '16:00'],
            ['date' => '2030-12-06', 'start_time' => '22:00', 'end_time' => '01:00'],
            ['date' => '2030-12-12'],
        ])->create();

        $ics = $this->get(route('events.calendar', $event->id))->assertOk()->getContent();

        $this->assertSame(3, substr_count($ics, 'BEGIN:VEVENT'));
        $this->assertStringContainsString("DTSTART:20301205T100000\r\nDTEND:20301205T160000", $ics);
        $this->assertStringContainsString("DTSTART:20301206T220000\r\nDTEND:20301207T010000", $ics, 'Hours past midnight end the next day.');
        $this->assertStringContainsString("DTSTART;VALUE=DATE:20301212\r\nDTEND;VALUE=DATE:20301213", $ics);
        $this->assertStringContainsString("UID:event-{$event->id}-20301205@", $ics);
    }

    public function test_events_api_adds_the_schedule_and_keeps_its_labels(): void
    {
        $date = now()->addDays(3);
        Event::factory()->withRoute()->withDays([
            ['date' => $date->toDateString(), 'start_time' => '10:00', 'end_time' => '16:00', 'note' => 'Opening day'],
            ['date' => $date->copy()->addDay()->toDateString(), 'start_time' => '12:00', 'end_time' => '20:00'],
        ])->create();
        Event::factory()->withRoute()->create(['title' => 'Plain']);

        $response = $this->getJson('/api/events')->assertOk();

        $scheduled = collect($response->json())->firstWhere('title', '!==', 'Plain');
        $this->assertSame(
            $date->format('M j').': 10 AM – 4 PM, '.$date->copy()->addDay()->format('M j').': 12 PM – 8 PM',
            $scheduled['time_label'],
        );
        $this->assertNotEmpty($scheduled['date_label']);
        $this->assertSame([
            'date' => $date->toDateString(),
            'date_label' => $date->format('D, M j'),
            'time_label' => '10:00 AM – 4:00 PM',
            'note' => 'Opening day',
        ], $scheduled['schedule'][0]);

        $this->assertSame([], collect($response->json())->firstWhere('title', 'Plain')['schedule']);
    }

    // ── Admin form ───────────────────────────────────────────────────────────

    public function test_edit_page_opens_in_days_mode_with_the_days_filled_in(): void
    {
        $event = Event::factory()->withDays([
            ['date' => '2030-12-05', 'start_time' => '10:00', 'end_time' => '16:00', 'note' => 'Opening day'],
        ])->create();

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.local-events.edit', $event))
            ->assertOk()
            ->assertSee('Different dates or hours')
            ->assertSee('eventSchedule(', false)
            ->assertSee('name="schedule_mode" :value="mode" value="days"', false)
            ->assertSee('2030-12-05', false)
            ->assertSee('Opening day', false);
    }
}
