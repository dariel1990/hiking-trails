<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrailNetwork;
use App\Models\NetworkFacility;
use Illuminate\Http\Request;

class NetworkFacilityController extends Controller
{
    /**
     * Display facilities for a trail network
     */
    public function index(TrailNetwork $trailNetwork)
    {
        $facilities = $trailNetwork->facilities()->orderBy('facility_type')->get();
        
        return view('admin.trail-networks.facilities.index', compact('trailNetwork', 'facilities'));
    }

    /**
     * Show the form for creating a new facility
     */
    public function create(TrailNetwork $trailNetwork)
    {
        return view('admin.trail-networks.facilities.create', compact('trailNetwork'));
    }

    /**
     * Store a newly created facility
     */
    public function store(Request $request, TrailNetwork $trailNetwork)
    {
        $validated = $request->validate([
            'facility_type' => 'required|in:parking,toilets,emergency_kit,lodge,viewpoint,info,picnic,water,shelter',
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);

        $validated['trail_network_id'] = $trailNetwork->id;
        $validated['is_active'] = $request->has('is_active');

        NetworkFacility::create($validated);

        return redirect()->route('admin.trail-networks.facilities.index', $trailNetwork)
            ->with('success', 'Facility added successfully.');
    }

    /**
     * Show the form for editing a facility
     */
    public function edit(TrailNetwork $trailNetwork, NetworkFacility $facility)
    {
        // Ensure facility belongs to this network
        if ($facility->trail_network_id !== $trailNetwork->id) {
            abort(404);
        }

        return view('admin.trail-networks.facilities.edit', compact('trailNetwork', 'facility'));
    }

    /**
     * Update the specified facility
     */
    public function update(Request $request, TrailNetwork $trailNetwork, NetworkFacility $facility)
    {
        // Ensure facility belongs to this network
        if ($facility->trail_network_id !== $trailNetwork->id) {
            abort(404);
        }

        $validated = $request->validate([
            'facility_type' => 'required|in:parking,toilets,emergency_kit,lodge,viewpoint,info,picnic,water,shelter',
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $facility->update($validated);

        return redirect()->route('admin.trail-networks.facilities.index', $trailNetwork)
            ->with('success', 'Facility updated successfully.');
    }

    /**
     * Remove the specified facility
     */
    public function destroy(TrailNetwork $trailNetwork, NetworkFacility $facility)
    {
        // Ensure facility belongs to this network
        if ($facility->trail_network_id !== $trailNetwork->id) {
            abort(404);
        }

        $facility->delete();

        return redirect()->route('admin.trail-networks.facilities.index', $trailNetwork)
            ->with('success', 'Facility deleted successfully.');
    }
}