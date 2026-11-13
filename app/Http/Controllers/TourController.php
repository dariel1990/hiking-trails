<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TourController extends Controller
{
    public function index(): View
    {
        $tours = Tour::active()
            ->tours()
            ->withCount('stops')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('tours.index', compact('tours'));
    }

    /**
     * Events share the tour engine but live under /local-events, so an old
     * /tours/{slug} link to an event is permanently redirected there.
     */
    public function show(Tour $tour): View|RedirectResponse
    {
        abort_unless($tour->is_active, 404);

        if ($tour->isEvent()) {
            return redirect()->route('local-events.show', $tour, 301);
        }

        return $this->showView($tour);
    }

    /**
     * Map-only version of a tour for embedding in an iframe on other sites.
     */
    public function embed(Tour $tour): Response|RedirectResponse
    {
        abort_unless($tour->is_active, 404);

        if ($tour->isEvent()) {
            return redirect()->route('local-events.embed', $tour, 301);
        }

        return $this->embedResponse($tour, route('tours.show', $tour));
    }

    /**
     * Friendly URL for the Christmas parade: the active event with the
     * configured slug (other events live at /local-events/{slug}).
     */
    public function christmasParade(): View
    {
        return $this->showView($this->currentChristmasParade());
    }

    public function christmasParadeEmbed(): Response
    {
        return $this->embedResponse($this->currentChristmasParade(), route('christmas-parade'));
    }

    private function currentChristmasParade(): Tour
    {
        return Tour::active()
            ->events()
            ->where('slug', config('app.christmas_parade_slug'))
            ->firstOrFail();
    }

    /**
     * Shared by tours and events: events link back to the Events page.
     */
    protected function showView(Tour $tour): View
    {
        $tour->load('stops.trail.features', 'stops.feature', 'stops.facility');

        $mapboxToken = config('services.mapbox.access_token');
        $backUrl = $tour->isEvent() ? route('local-events.index') : route('tours.index');
        $backLabel = $tour->isEvent() ? 'All Events' : 'All Tours';

        return view('tours.show', compact('tour', 'mapboxToken', 'backUrl', 'backLabel'));
    }

    protected function embedResponse(Tour $tour, string $fullPageUrl): Response
    {
        $tour->load('stops.trail.features', 'stops.feature', 'stops.facility');

        $mapboxToken = config('services.mapbox.access_token');
        $frameAncestors = trim("'self' ".config('app.embed_frame_ancestors'));

        return response()
            ->view('tours.embed', compact('tour', 'mapboxToken', 'fullPageUrl'))
            ->header('Content-Security-Policy', "frame-ancestors {$frameAncestors}");
    }
}
