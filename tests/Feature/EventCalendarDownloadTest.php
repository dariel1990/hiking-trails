<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCalendarDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_download_with_event_and_end_times_returns_valid_ics(): void
    {
        $event = Event::create([
            'title' => 'Fall Fair',
            'event_date' => '2026-07-18',
            'event_time' => '09:00:00',
            'end_date' => '2026-07-21',
            'end_time' => '12:00:00',
            'source_id' => 'test-event-1',
            'is_active' => true,
        ]);

        $response = $this->get(route('events.calendar', $event));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('DTSTART:20260718T090000', $response->getContent());
        $this->assertStringContainsString('DTEND:20260721T120000', $response->getContent());
    }

    public function test_calendar_download_without_times_uses_dates_only(): void
    {
        $event = Event::create([
            'title' => 'All Day Event',
            'event_date' => '2026-08-01',
            'source_id' => 'test-event-2',
            'is_active' => true,
        ]);

        $response = $this->get(route('events.calendar', $event));

        $response->assertOk();
        $this->assertStringContainsString('DTSTART:20260801T000000', $response->getContent());
    }
}
