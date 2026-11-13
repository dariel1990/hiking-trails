<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Public "Events" pages: map-based events (e.g. Christmas in the Valley).
 * They are stored as tours with the event type and reuse the tour page,
 * but are listed and linked separately from regular tours.
 *
 * Not to be confused with EventsController (/events), which lists the
 * calendar events scraped from SmithersEvents.com.
 */
class LocalEventController extends TourController
{
    public function index(): View
    {
        $events = Tour::active()
            ->events()
            ->withCount('stops')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('local-events.index', compact('events'));
    }

    public function show(Tour $tour): View
    {
        abort_unless($tour->is_active && $tour->isEvent(), 404);

        return $this->showView($tour);
    }

    public function embed(Tour $tour): Response
    {
        abort_unless($tour->is_active && $tour->isEvent(), 404);

        return $this->embedResponse($tour, route('local-events.show', $tour));
    }
}
