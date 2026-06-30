<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\View\View;

class TourController extends Controller
{
    public function index(): View
    {
        $tours = Tour::active()
            ->withCount('stops')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('tours.index', compact('tours'));
    }

    public function show(Tour $tour): View
    {
        if (! $tour->is_active) {
            abort(404);
        }

        $tour->load('stops.trail');

        $mapboxToken = config('services.mapbox.access_token');

        return view('tours.show', compact('tour', 'mapboxToken'));
    }
}
