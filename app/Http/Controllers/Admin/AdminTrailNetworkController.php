<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrailNetwork;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminTrailNetworkController extends Controller
{
    /**
     * Display a listing of trail networks
     */
    public function index()
    {
        $networks = TrailNetwork::withCount('trails')->orderBy('network_name')->get();
        
        return view('admin.trail-networks.index', compact('networks'));
    }

    /**
     * Show the form for creating a new trail network
     */
    public function create()
    {
        return view('admin.trail-networks.create');
    }

    /**
     * Store a newly created trail network
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'network_name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:trail_networks,slug',
            'type' => 'required|in:nordic_skiing,downhill_skiing,hiking,mountain_biking',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
            'website_url' => 'nullable|url|max:500',
            'is_always_visible' => 'boolean',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['network_name']);
        }

        // Ensure is_always_visible has a value
        $validated['is_always_visible'] = $request->has('is_always_visible');

        TrailNetwork::create($validated);

        return redirect()->route('admin.trail-networks.index')
            ->with('success', 'Trail network created successfully.');
    }

    /**
     * Display the specified trail network
     */
    public function show(TrailNetwork $trailNetwork)
    {
        $trailNetwork->load('trails');
        
        return view('admin.trail-networks.show', compact('trailNetwork'));
    }

    /**
     * Show the form for editing the specified trail network
     */
    public function edit(TrailNetwork $trailNetwork)
    {
        return view('admin.trail-networks.edit', compact('trailNetwork'));
    }

    /**
     * Update the specified trail network
     */
    public function update(Request $request, TrailNetwork $trailNetwork)
    {
        $validated = $request->validate([
            'network_name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:trail_networks,slug,' . $trailNetwork->id,
            'type' => 'required|in:nordic_skiing,downhill_skiing,hiking,mountain_biking',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
            'website_url' => 'nullable|url|max:500',
            'is_always_visible' => 'boolean',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['network_name']);
        }

        // Ensure is_always_visible has a value
        $validated['is_always_visible'] = $request->has('is_always_visible');

        $trailNetwork->update($validated);

        return redirect()->route('admin.trail-networks.index')
            ->with('success', 'Trail network updated successfully.');
    }

    /**
     * Remove the specified trail network
     */
    public function destroy(TrailNetwork $trailNetwork)
    {
        // Check if network has trails
        if ($trailNetwork->trails()->count() > 0) {
            return redirect()->route('admin.trail-networks.index')
                ->with('error', 'Cannot delete trail network with existing trails. Please reassign or delete the trails first.');
        }

        $trailNetwork->delete();

        return redirect()->route('admin.trail-networks.index')
            ->with('success', 'Trail network deleted successfully.');
    }
}