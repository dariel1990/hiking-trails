<?php

namespace App\Http\Controllers;

use App\Services\TrailService;
use Illuminate\Http\Request;

class TrailController extends Controller
{
    public function __construct(
        protected TrailService $trailService
    ) {}

    /**
     * Show homepage with featured trails
     */
    public function home()
    {
        $data = $this->trailService->getFeaturedTrails();

        return view('home', $data);
    }

    /**
     * Display trail listing page
     */
    public function index(Request $request)
    {
        if ($this->trailService->isMobile()) {
            return $this->trailService->fetchProductionPage('trails?'.$request->getQueryString());
        }

        $data = $this->trailService->getFilteredTrails($request);

        return view('trails.index', $data);
    }

    /**
     * Show trail detail page
     */
    public function show($id)
    {
        if ($this->trailService->isMobile()) {
            return $this->trailService->fetchProductionPage("trails/{$id}");
        }

        $data = $this->trailService->getTrailDetail($id);

        return view('trails.show', $data);
    }

    /**
     * Show map page
     */
    public function map()
    {
        $activities = $this->trailService->getActiveActivities();

        return view('map', compact('activities'));
    }

    /**
     * API endpoint for trail data
     */
    public function apiIndex(Request $request)
    {
        $trails = $this->trailService->getTrailsForMap($request);

        return response()->json($trails);
    }

    /**
     * API endpoint for single trail
     */
    public function apiShow($id)
    {
        $data = $this->trailService->getTrailForApi($id);

        return response()->json($data);
    }
}
