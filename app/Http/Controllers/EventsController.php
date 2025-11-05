<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EventsController extends Controller
{
    /**
     * Display events page with calendar and list view
     */
    public function index(Request $request)
    {
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
        $upcomingEvents = (clone $query)->upcoming()->paginate(12);
        
        // Get events for calendar view (current month)
        $calendarEvents = (clone $query)
            ->whereYear('event_date', $year)
            ->whereMonth('event_date', $month)
            ->orderBy('event_date')
            ->orderBy('event_time')
            ->get();
        
        // Get all categories for filter
        $categories = Event::where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');
        
        // Calendar data
        $firstDayOfMonth = Carbon::create($year, $month, 1);
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
        $startDayOfWeek = $firstDayOfMonth->dayOfWeek;
        $daysInMonth = $firstDayOfMonth->daysInMonth;
        
        // Previous and next month
        $prevMonth = $firstDayOfMonth->copy()->subMonth();
        $nextMonth = $firstDayOfMonth->copy()->addMonth();
        
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
            'nextMonth'
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
        
        $filename = \Str::slug($event->title) . '.ics';
        
        return response($icsContent)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    /**
     * Generate ICS file content
     */
    protected function generateIcsFile(Event $event)
    {
        $startDateTime = $event->event_date;
        if ($event->event_time) {
            $startDateTime = Carbon::parse($event->event_date->format('Y-m-d') . ' ' . $event->event_time);
        }
        
        $endDateTime = $event->end_date ?? $event->event_date;
        if ($event->end_time) {
            $endDateTime = Carbon::parse($endDateTime->format('Y-m-d') . ' ' . $event->end_time);
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
        $ics .= "UID:" . md5($event->source_id) . "@smithersevents.com\r\n";
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
     * Escape string for ICS format
     */
    protected function escapeIcsString($string)
    {
        $string = str_replace(["\r\n", "\n", "\r"], "\\n", $string);
        $string = str_replace([",", ";"], ["\\,", "\\;"], $string);
        return $string;
    }

    /**
     * Get event details as JSON for modal
     */
    public function getEventDetails(Event $event)
    {
        $googleCalendarUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text=" . urlencode($event->title) . 
            "&dates=" . $event->event_date->format('Ymd') . "T" . ($event->event_time ? \Carbon\Carbon::parse($event->event_time)->format('His') : '000000') . 
            "/" . ($event->end_date ? $event->end_date->format('Ymd') : $event->event_date->format('Ymd')) . "T" . ($event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('His') : '235959') . 
            "&details=" . urlencode($event->description ?? '') . "&location=" . urlencode($event->location ?? '');
        
        $outlookCalendarUrl = "https://outlook.live.com/calendar/0/deeplink/compose?subject=" . urlencode($event->title) . 
            "&body=" . urlencode($event->description ?? '') . "&location=" . urlencode($event->location ?? '') . 
            "&startdt=" . $event->event_date->format('Y-m-d') . "T" . ($event->event_time ? \Carbon\Carbon::parse($event->event_time)->format('H:i:s') : '00:00:00') . 
            "&enddt=" . ($event->end_date ? $event->end_date->format('Y-m-d') : $event->event_date->format('Y-m-d')) . "T" . ($event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('H:i:s') : '23:59:59');
        
        $yahooCalendarUrl = "https://calendar.yahoo.com/?v=60&title=" . urlencode($event->title) . 
            "&st=" . $event->event_date->format('Ymd') . "T" . ($event->event_time ? \Carbon\Carbon::parse($event->event_time)->format('His') : '000000') . 
            "&et=" . ($event->end_date ? $event->end_date->format('Ymd') : $event->event_date->format('Ymd')) . "T" . ($event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('His') : '235959') . 
            "&desc=" . urlencode($event->description ?? '') . "&in_loc=" . urlencode($event->location ?? '');
        
        return response()->json([
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'formatted_date' => $event->formatted_date,
            'event_time' => $event->event_time,
            'formatted_time' => $event->formatted_time,
            'end_time' => $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('g:i A') : null,
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