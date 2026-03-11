<?php

namespace App\Http\Controllers;

use App\Services\TrailNetworkService;

class TrailNetworkController extends Controller
{
    public function __construct(
        protected TrailNetworkService $trailNetworkService
    ) {}

    /**
     * Display a listing of all trail networks
     */
    public function index()
    {
        if ($this->trailNetworkService->isMobile()) {
            return $this->trailNetworkService->fetchProductionPage('trail-networks');
        }

        $networks = $this->trailNetworkService->getNetworks();

        return view('trail-networks.index', compact('networks'));
    }

    /**
     * Display the specified trail network with its map
     */
    public function show($slug)
    {
        if ($this->trailNetworkService->isMobile()) {
            return $this->trailNetworkService->fetchProductionPage("trail-networks/{$slug}");
        }

        $network = $this->trailNetworkService->getNetworkDetail($slug);

        return view('trail-networks.show', compact('network'));
    }

    public function trailHighlights()
    {
        $highlights = $this->trailNetworkService->getHighlights();

        return response()->json($highlights);
    }
}
