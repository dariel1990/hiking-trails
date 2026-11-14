<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\View\View;

/**
 * Public event pages (/local-events): the list of upcoming events and each
 * event's page with its map of routes and activities.
 *
 * Not to be confused with EventsController (/events), the calendar page that
 * is iframed on xploresmithers.com. Both read the same events table.
 */
class LocalEventController extends Controller
{
    public const DEFAULT_LAYOUT = 'premium';

    /**
     * Upcoming events, optionally narrowed to one month with ?month=YYYY-MM.
     * A multi-day event is listed under every month it runs in.
     */
    public function index(Request $request): View
    {
        $allEvents = Event::current()
            ->withCount('stops')
            ->orderByDesc('is_featured')
            ->orderBy('event_date')
            ->orderBy('event_time')
            ->orderBy('title')
            ->get();

        $months = $this->monthsWithEvents($allEvents);
        $selectedMonth = $months->has((string) $request->query('month')) ? (string) $request->query('month') : null;

        $events = $selectedMonth
            ? $allEvents->filter(fn (Event $event) => $this->runsInMonth($event, $selectedMonth))->values()
            : $allEvents;

        return view('local-events.index', [
            'events' => $events,
            'totalCount' => $allEvents->count(),
            'months' => $months,
            'selectedMonth' => $selectedMonth,
        ]);
    }

    public function show(Event $event): View
    {
        abort_unless($event->is_active, 404);

        return $this->showView($event);
    }

    public function embed(Event $event): Response
    {
        abort_unless($event->is_active, 404);

        return $this->embedResponse($event, route('local-events.show', $event->slug));
    }

    /**
     * Friendly URL for the Christmas parade: the active event with the
     * configured slug.
     */
    public function christmasParade(): View
    {
        return $this->showView($this->currentChristmasParade());
    }

    public function christmasParadeEmbed(): Response
    {
        return $this->embedResponse($this->currentChristmasParade(), route('christmas-parade'));
    }

    private function currentChristmasParade(): Event
    {
        return Event::query()
            ->where('is_active', true)
            ->where('slug', config('app.christmas_parade_slug'))
            ->firstOrFail();
    }

    private function showView(Event $event): View
    {
        $event->load('stops', 'media');
        $layout = $this->pageLayout();

        return view('local-events.layouts.'.$layout, [
            'event' => $event,
            'map' => $event->toMapPayload(),
            'mapboxToken' => config('services.mapbox.access_token'),
            'pageLayout' => $layout,
            'isLayoutPreview' => $layout !== $this->chosenLayout(),
        ]);
    }

    /**
     * The layout to render: the one chosen in Settings → Events, or another
     * one an admin is previewing with ?layout=.
     */
    private function pageLayout(): string
    {
        $preview = request()->query('layout');

        if (is_string($preview) && array_key_exists($preview, self::layouts()) && request()->user()?->is_admin) {
            return $preview;
        }

        return $this->chosenLayout();
    }

    private function chosenLayout(): string
    {
        $chosen = setting('event_page_layout');

        return is_string($chosen) && array_key_exists($chosen, self::layouts()) ? $chosen : self::DEFAULT_LAYOUT;
    }

    /**
     * Event page layouts, keyed by name (see config/settings.php).
     *
     * @return array<string, array{label: string, description: string}>
     */
    public static function layouts(): array
    {
        return config('settings.definitions.event_page_layout.options', []);
    }

    private function embedResponse(Event $event, string $fullPageUrl): Response
    {
        $event->load('stops');

        return response()
            ->view('tours.embed', [
                'title' => $event->title,
                'map' => $event->toMapPayload(),
                'mapboxToken' => config('services.mapbox.access_token'),
                'fullPageUrl' => $fullPageUrl,
            ])
            ->header('Content-Security-Policy', TourController::embedFrameAncestorsPolicy());
    }

    /**
     * Months (from this month on) that have at least one event, in order.
     *
     * @param  Collection<int, Event>  $events
     * @return SupportCollection<string, array{label: string, count: int}> Keyed by YYYY-MM
     */
    private function monthsWithEvents(Collection $events): SupportCollection
    {
        $thisMonth = Carbon::today()->startOfMonth();
        $months = [];

        foreach ($events as $event) {
            $month = $event->event_date->copy()->startOfMonth()->max($thisMonth)->copy();
            $lastMonth = ($event->end_date ?? $event->event_date)->copy()->startOfMonth();

            while ($month->lte($lastMonth)) {
                $key = $month->format('Y-m');
                $months[$key] ??= [
                    'label' => $month->year === $thisMonth->year ? $month->format('F') : $month->format('F Y'),
                    'count' => 0,
                ];
                $months[$key]['count']++;
                $month->addMonth();
            }
        }

        ksort($months);

        return collect($months);
    }

    private function runsInMonth(Event $event, string $month): bool
    {
        $start = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfDay();
        $end = $start->copy()->endOfMonth();

        return $event->event_date->lte($end) && ($event->end_date ?? $event->event_date)->gte($start);
    }
}
