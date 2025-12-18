<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityTypeController extends Controller
{
    /**
     * Display a listing of activity types
     */
    public function index(Request $request)
    {
        $query = ActivityType::withCount('trails');

        // Search filter
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // Season filter
        if ($request->season) {
            $query->where('season_applicable', $request->season);
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('is_active', $request->status);
        }

        $activityTypes = $query->orderBy('name')->paginate(15);

        return view('admin.activity-types.index', compact('activityTypes'));
    }

    /**
     * Show the form for creating a new activity type
     */
    public function create()
    {
        return view('admin.activity-types.create');
    }

    /**
     * Store a newly created activity type
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:activity_types,slug',
            'icon' => 'required|string|max:10',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
            'season_applicable' => 'required|in:summer,winter,both',
            'is_active' => 'boolean',
        ]);

        // Ensure slug is lowercase and hyphenated
        $validated['slug'] = Str::slug($validated['slug']);
        
        // Handle checkbox: if not present in request, set to false
        $validated['is_active'] = $request->has('is_active') ? true : false;

        ActivityType::create($validated);

        return redirect()->route('admin.activity-types.index')
            ->with('success', 'Activity type created successfully!');
    }

    /**
     * Show the form for editing the specified activity type
     */
    public function edit(ActivityType $activityType)
    {
        $activityType->loadCount('trails');
        return view('admin.activity-types.edit', compact('activityType'));
    }

    /**
     * Update the specified activity type
     */
    public function update(Request $request, ActivityType $activityType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:activity_types,slug,' . $activityType->id,
            'icon' => 'required|string|max:10',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
            'season_applicable' => 'required|in:summer,winter,both',
            'is_active' => 'boolean',
        ]);

        // Ensure slug is lowercase and hyphenated
        $validated['slug'] = Str::slug($validated['slug']);
        
        // Handle checkbox: if not present in request, set to false
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $activityType->update($validated);

        return redirect()->route('admin.activity-types.index')
            ->with('success', 'Activity type updated successfully!');
    }

    /**
     * Remove the specified activity type
     */
    public function destroy(ActivityType $activityType)
    {
        $trailCount = $activityType->trails()->count();

        if ($trailCount > 0) {
            return redirect()->route('admin.activity-types.index')
                ->with('error', "Cannot delete '{$activityType->name}'. It is currently used in {$trailCount} trail(s).");
        }

        $activityType->delete();

        return redirect()->route('admin.activity-types.index')
            ->with('success', 'Activity type deleted successfully!');
    }
}