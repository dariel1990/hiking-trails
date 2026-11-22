<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventDay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Multi-day and multi-time local events on the /events calendar: shown on every day
 * they run, each schedule day with its own hours, like the local-events page.
 */
class EventsCalendarMultiDayTest extends TestCase
{
    use RefreshDatabase;

    private function calendar(int $month, int $year = 2030): TestResponse
    {
        return $this->get(route('events.index', ['view' => 'calendar', 'month' => $month, 'year' => $year]))->assertOk();
    }

    /**
     * How many calendar days show the event (one chip per day it runs).
     */
    private function daysShown(TestResponse $response, Event $event): int
    {
        return substr_count($response->getContent(), 'openEventModal('.$event->id.')');
    }

    /**
     * @param  array<int, array{0: string, 1: ?string, 2: ?string}>  $days  [date, start, end]
     */
    private function scheduledEvent(array $days, array $attributes = []): Event
    {
        $summary = Event::summaryFromDays(array_map(fn ($d) => ['date' => $d[0], 'start_time' => $d[1], 'end_time' => $d[2]], $days));
        $event = Event::factory()->create(array_merge($summary, $attributes));

        foreach ($days as [$date, $start, $end]) {
            EventDay::factory()->create(['event_id' => $event->id, 'date' => $date, 'start_time' => $start, 'end_time' => $end]);
        }

        return $event;
    }

    public function test_a_date_range_event_shows_on_every_day_it_runs(): void
    {
        $event = Event::factory()->create([
            'title' => 'Winter Carnival',
            'event_date' => '2030-12-05',
            'end_date' => '2030-12-07',
            'event_time' => '10:00',
            'end_time' => '16:00',
        ]);

        $response = $this->calendar(12);

        $this->assertSame(3, $this->daysShown($response, $event));
        $response->assertSee('10 AM – 4 PM');
    }

    public function test_a_scheduled_event_shows_only_on_its_days_with_each_days_hours(): void
    {
        $event = $this->scheduledEvent([
            ['2030-12-05', '10:00', '16:00'],
            ['2030-12-12', '12:00', '20:30'],
            ['2030-12-19', null, null],
        ], ['title' => 'Christmas Market']);

        $this->assertTrue($event->occursOn(now()->setDate(2030, 12, 12)));
        $this->assertFalse($event->occursOn(now()->setDate(2030, 12, 6)));
        $this->assertSame('12 PM – 8:30 PM', $event->timeLabelOn(now()->setDate(2030, 12, 12)));
        $this->assertNull($event->timeLabelOn(now()->setDate(2030, 12, 19)));

        $response = $this->calendar(12);

        $this->assertSame(3, $this->daysShown($response, $event));
        $response->assertSee('10 AM – 4 PM')->assertSee('12 PM – 8:30 PM');
    }

    public function test_an_event_that_started_last_month_shows_on_its_remaining_days(): void
    {
        $event = Event::factory()->create([
            'title' => 'Light Festival',
            'event_date' => '2030-11-29',
            'end_date' => '2030-12-02',
            'event_time' => null,
        ]);

        $this->assertSame(2, $this->daysShown($this->calendar(12), $event));
        $this->assertSame(2, $this->daysShown($this->calendar(11), $event));
        $this->assertSame(0, $this->daysShown($this->calendar(1, 2031), $event));
    }

    public function test_inactive_events_are_not_on_the_calendar(): void
    {
        $event = Event::factory()->inactive()->create(['event_date' => '2030-12-05', 'end_date' => '2030-12-07']);

        $this->assertSame(0, $this->daysShown($this->calendar(12), $event));
    }

    public function test_event_details_include_the_date_range_and_each_days_hours(): void
    {
        $event = $this->scheduledEvent([
            ['2030-12-05', '10:00', '16:00'],
            ['2030-12-12', '12:00', '20:00'],
        ]);

        $this->getJson(route('events.details', $event))
            ->assertOk()
            ->assertJsonPath('date_range_label', 'Dec 5 & 12, 2030')
            ->assertJsonPath('day_times.0.short_date', 'Dec 5')
            ->assertJsonPath('day_times.0.time', '10 AM – 4 PM')
            ->assertJsonPath('day_times.1.time', '12 PM – 8 PM');
    }

    public function test_the_list_shows_the_date_range_and_one_line_per_day_when_hours_differ(): void
    {
        $this->scheduledEvent([
            [now()->addDays(3)->toDateString(), '10:00', '16:00'],
            [now()->addDays(10)->toDateString(), '12:00', '20:00'],
        ], ['title' => 'Holiday Market']);

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee('Holiday Market')
            ->assertSee(now()->addDays(3)->format('M j').' · 10 AM – 4 PM')
            ->assertSee(now()->addDays(10)->format('M j').' · 12 PM – 8 PM');
    }
}
