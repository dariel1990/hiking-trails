<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * Display all facilities
     */
    public function index()
    {
        $facilities = Facility::orderBy('facility_type')->orderBy('name')->get();
        
        return view('admin.facilities.index', compact('facilities'));
    }

    /**
     * Show the form for creating a new facility
     */
    public function create()
    {
        return view('admin.facilities.create');
    }

    /**
     * Store a newly created facility
     */
    public function store(Request $request)
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

        $validated['is_active'] = $request->has('is_active');

        Facility::create($validated);

        return redirect()->route('admin.facilities.index')
            ->with('success', 'Facility added successfully.');
    }

    /**
     * Show the form for editing a facility
     */
    public function edit(Facility $facility)
    {
        return view('admin.facilities.edit', compact('facility'));
    }

    /**
     * Update the specified facility
     */
    public function update(Request $request, Facility $facility)
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

        $validated['is_active'] = $request->has('is_active');

        $facility->update($validated);

        return redirect()->route('admin.facilities.index')
            ->with('success', 'Facility updated successfully.');
    }

    /**
     * Remove the specified facility
     */
    public function destroy(Facility $facility)
    {
        $facility->delete();

        return redirect()->route('admin.facilities.index')
            ->with('success', 'Facility deleted successfully.');
    }
}