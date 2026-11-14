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
     * Events used to be tours of a special type. Links to those old tours are
     * permanently redirected to the event page with the same slug.
     */
    public function show(Tour $tour): View|RedirectResponse
    {
        abort_unless($tour->is_active, 404);

        if ($tour->isLegacyEvent()) {
            return redirect()->route('local-events.show', $tour->slug, 301);
        }

        $tour->load('stops.trail.features', 'stops.feature', 'stops.facility');

        return view('tours.show', [
            'tour' => $tour,
            'map' => $tour->toMapPayload(),
            'mapboxToken' => config('services.mapbox.access_token'),
        ]);
    }

    /**
     * Map-only version of a tour for embedding in an iframe on other sites.
     */
    public function embed(Tour $tour): Response|RedirectResponse
    {
        abort_unless($tour->is_active, 404);

        if ($tour->isLegacyEvent()) {
            return redirect()->route('local-events.embed', $tour->slug, 301);
        }

        $tour->load('stops.trail.features', 'stops.feature', 'stops.facility');

        return response()
            ->view('tours.embed', [
                'title' => $tour->title,
                'map' => $tour->toMapPayload(),
                'mapboxToken' => config('services.mapbox.access_token'),
                'fullPageUrl' => route('tours.show', $tour),
            ])
            ->header('Content-Security-Policy', self::embedFrameAncestorsPolicy());
    }

    /**
     * CSP header value that lets only our own sites frame an embed page.
     */
    public static function embedFrameAncestorsPolicy(): string
    {
        return 'frame-ancestors '.trim("'self' ".config('app.embed_frame_ancestors'));
    }
}
