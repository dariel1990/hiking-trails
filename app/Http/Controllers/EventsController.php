<?php

namespace App\Http\Controllers;

use App\Models\CarouselSlide;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EventsController extends Controller
{
    /**
     * Display events page with calendar and list view
     */
    public function index(Request $request)
    {
        // Switch off finished events and slides (also done nightly by events:expire and
        // carousel:expire); once a day here too, in case the server's scheduler is not running.
        if (Cache::add('events:expired-on:'.Event::localToday(), true, now()->addDay())) {
            Event::deactivateFinished();
            CarouselSlide::deactivateExpired();
        }

        // Get current month and year
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        // Get view type (calendar or list)
        $view = $request->get('view', 'list');

        // Get category filter
        $categoryFilter = $request->get('category');

        // Base query
        $query = Event::where('is_active', true);

        // Apply category filter if provided
        if ($categoryFilter) {
            $query->where('category', $categoryFilter);
        }

        // Get upcoming events for list view
        $upcomingEvents = (clone $query)->with('days')->upcoming()->paginate(setting('events_per_page'));

        // Events on the calendar this month: every day they run, including multi-day
        // events that started last month and schedule days (each with its own hours).
        $calendarEvents = (clone $query)
            ->with('days')
            ->overlappingMonth((int) $year, (int) $month)
            ->orderBy('event_date')
            ->orderBy('event_time')
            ->get();

        // Carousel slides added to the calendar (Admin → Carousel → Calendar).
        $calendarSlides = CarouselSlide::onCalendarInMonth((int) $year, (int) $month)
            ->when($categoryFilter, fn ($q) => $q->where('calendar_category', $categoryFilter))
            ->with('days')
            ->orderBy('calendar_date')
            ->orderBy('calendar_start_time')
            ->get();

        // Upcoming calendar slides lead the list view's first page; with a category
        // filter on, only slides in that category.
        $listSlides = $upcomingEvents->onFirstPage()
            ? CarouselSlide::upcomingOnCalendar()
                ->when($categoryFilter, fn ($q) => $q->where('calendar_category', $categoryFilter))
                ->with('days')
                ->get()
            : collect();

        // Details for the slide popup, from either view.
        $slideDetails = $calendarSlides->concat($listSlides)
            ->unique('id')
            ->mapWithKeys(fn (CarouselSlide $slide) => [$slide->id => $slide->toCalendarPayload()]);

        // Get all categories for filter
        // (events and calendar slides share categories)
        $categories = Event::where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->concat(CarouselSlide::where('is_active', true)
                ->where('show_on_calendar', true)
                ->whereNotNull('calendar_category')
                ->distinct()
                ->pluck('calendar_category'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Calendar data
        $firstDayOfMonth = Carbon::create($year, $month, 1);
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
        $startDayOfWeek = $firstDayOfMonth->dayOfWeek;
        $daysInMonth = $firstDayOfMonth->daysInMonth;

        // Previous and next month
        $prevMonth = $firstDayOfMonth->copy()->subMonth();
        $nextMonth = $firstDayOfMonth->copy()->addMonth();

        $slides = CarouselSlide::currentlyActive()->ordered()->get()
            ->map(fn ($s) => [
                'url' => $s->url,
                'name' => $s->caption,
            ]);

        return view('events.index', compact(
            'upcomingEvents',
            'calendarEvents',
            'categories',
            'categoryFilter',
            'view',
            'month',
            'year',
            'firstDayOfMonth',
            'lastDayOfMonth',
            'startDayOfWeek',
            'daysInMonth',
            'prevMonth',
            'nextMonth',
            'slides',
            'calendarSlides',
            'listSlides',
            'slideDetails'
        ));
    }

    /**
     * Display single event
     */
    public function show(Event $event)
    {
        // Get related events (same category)
        $relatedEvents = Event::where('is_active', true)
            ->where('category', $event->category)
            ->where('id', '!=', $event->id)
            ->upcoming()
            ->limit(3)
            ->get();

        return view('events.show', compact('event', 'relatedEvents'));
    }

    /**
     * Download .ics calendar file for event
     */
    public function downloadCalendar(Event $event)
    {
        $icsContent = $this->generateIcsFile($event);

        $filename = \Str::slug($event->title).'.ics';

        return response($icsContent)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    /**
     * Generate ICS file content
     */
    protected function generateIcsFile(Event $event)
    {
        if ($event->hasSchedule()) {
            return $this->generateScheduleIcsFile($event);
        }

        $startDateTime = $event->event_date;
        if ($event->event_time) {
            $startDateTime = Carbon::parse($event->event_date->format('Y-m-d').' '.$event->event_time->format('H:i:s'));
        }

        $endDateTime = $event->end_date ?? $event->event_date;
        if ($event->end_time) {
            $endDateTime = Carbon::parse($endDateTime->format('Y-m-d').' '.$event->end_time->format('H:i:s'));
        } elseif ($event->event_time) {
            // Default to 1 hour after start if no end time
            $endDateTime = $startDateTime->copy()->addHour();
        }

        // Format dates for ICS
        $dtstart = $startDateTime->format('Ymd\THis');
        $dtend = $endDateTime->format('Ymd\THis');
        $dtstamp = now()->format('Ymd\THis');

        // Clean strings for ICS format
        $title = $this->escapeIcsString($event->title);
        $description = $this->escapeIcsString($event->description ?? '');
        $location = $this->escapeIcsString($event->location ?? '');

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Smithers Events//EN\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= 'UID:'.md5($event->source_id)."@smithersevents.com\r\n";
        $ics .= "DTSTAMP:{$dtstamp}\r\n";
        $ics .= "DTSTART:{$dtstart}\r\n";
        $ics .= "DTEND:{$dtend}\r\n";
        $ics .= "SUMMARY:{$title}\r\n";
        $ics .= "DESCRIPTION:{$description}\r\n";
        $ics .= "LOCATION:{$location}\r\n";
        if ($event->external_url) {
            $ics .= "URL:{$event->external_url}\r\n";
        }
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }

    /**
     * One calendar entry per schedule day, each with that day's hours. A day without
     * times is an all-day entry; without an end time it lasts an hour, like single events.
     */
    protected function generateScheduleIcsFile(Event $event): string
    {
        $dtstamp = now()->format('Ymd\THis');
        $title = $this->escapeIcsString($event->title);
        $location = $this->escapeIcsString($event->location ?? '');
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Smithers Events//EN\r\n";

        foreach ($event->days as $day) {
            $date = $day->date->format('Y-m-d');
            $description = $this->escapeIcsString(trim(($day->note ? $day->note."\n\n" : '').($event->description ?? '')));

            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:event-{$event->id}-{$day->date->format('Ymd')}@{$host}\r\n";
            $ics .= "DTSTAMP:{$dtstamp}\r\n";

            if ($day->start_time) {
                $start = Carbon::parse($date.' '.$day->start_time->format('H:i:s'));
                $end = $day->end_time
                    ? Carbon::parse($date.' '.$day->end_time->format('H:i:s'))
                    : $start->copy()->addHour();
                if ($end->lte($start)) {
                    $end->addDay();
                }
                $ics .= 'DTSTART:'.$start->format('Ymd\THis')."\r\n";
                $ics .= 'DTEND:'.$end->format('Ymd\THis')."\r\n";
            } else {
                $ics .= 'DTSTART;VALUE=DATE:'.$day->date->format('Ymd')."\r\n";
                $ics .= 'DTEND;VALUE=DATE:'.$day->date->copy()->addDay()->format('Ymd')."\r\n";
            }

            $ics .= "SUMMARY:{$title}\r\n";
            $ics .= "DESCRIPTION:{$description}\r\n";
            $ics .= "LOCATION:{$location}\r\n";
            if ($event->external_url) {
                $ics .= "URL:{$event->external_url}\r\n";
            }
            $ics .= "END:VEVENT\r\n";
        }

        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }

    /**
     * Escape string for ICS format
     */
    protected function escapeIcsString($string)
    {
        $string = str_replace(["\r\n", "\n", "\r"], '\\n', $string);
        $string = str_replace([',', ';'], ['\\,', '\\;'], $string);

        return $string;
    }

    /**
     * Get event details as JSON for modal
     */
    public function getEventDetails(Event $event)
    {
        $event->loadMissing('days');

        $googleCalendarUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text='.urlencode($event->title).
            '&dates='.$event->event_date->format('Ymd').'T'.($event->event_time ? Carbon::parse($event->event_time)->format('His') : '000000').
            '/'.($event->end_date ? $event->end_date->format('Ymd') : $event->event_date->format('Ymd')).'T'.($event->end_time ? Carbon::parse($event->end_time)->format('His') : '235959').
            '&details='.urlencode($event->description ?? '').'&location='.urlencode($event->location ?? '');

        $outlookCalendarUrl = 'https://outlook.live.com/calendar/0/deeplink/compose?subject='.urlencode($event->title).
            '&body='.urlencode($event->description ?? '').'&location='.urlencode($event->location ?? '').
            '&startdt='.$event->event_date->format('Y-m-d').'T'.($event->event_time ? Carbon::parse($event->event_time)->format('H:i:s') : '00:00:00').
            '&enddt='.($event->end_date ? $event->end_date->format('Y-m-d') : $event->event_date->format('Y-m-d')).'T'.($event->end_time ? Carbon::parse($event->end_time)->format('H:i:s') : '23:59:59');

        $yahooCalendarUrl = 'https://calendar.yahoo.com/?v=60&title='.urlencode($event->title).
            '&st='.$event->event_date->format('Ymd').'T'.($event->event_time ? Carbon::parse($event->event_time)->format('His') : '000000').
            '&et='.($event->end_date ? $event->end_date->format('Ymd') : $event->event_date->format('Ymd')).'T'.($event->end_time ? Carbon::parse($event->end_time)->format('His') : '235959').
            '&desc='.urlencode($event->description ?? '').'&in_loc='.urlencode($event->location ?? '');

        return response()->json([
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'formatted_date' => $event->formatted_date,
            'date_range_label' => $event->date_range_label,
            'time_range_label' => $event->time_range_label,
            'day_times' => $event->day_times->all(),
            'event_time' => $event->event_time,
            'formatted_time' => $event->formatted_time,
            'end_time' => $event->end_time ? Carbon::parse($event->end_time)->format('g:i A') : null,
            'location' => $event->location,
            'organizer' => $event->organizer,
            'category' => $event->category,
            'external_url' => $event->external_url,
            'google_calendar_url' => $googleCalendarUrl,
            'outlook_calendar_url' => $outlookCalendarUrl,
            'yahoo_calendar_url' => $yahooCalendarUrl,
        ]);
    }
}
