<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TownRequest;
use App\Models\Town;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminTownController extends Controller
{
    public function index(): View
    {
        $towns = Town::ordered()
            ->withCount([
                'trails',
                'businesses',
                'facilities',
                'tours',
            ])
            ->get();

        return view('admin.towns.index', compact('towns'));
    }

    public function create(): View
    {
        $town = new Town([
            'province' => 'British Columbia',
            'province_code' => 'BC',
            'radius_km' => 40,
            'map_zoom' => 11,
            'latitude' => setting('map_default_lat'),
            'longitude' => setting('map_default_lng'),
            'is_active' => true,
        ]);

        return view('admin.towns.create', compact('town'));
    }

    public function store(TownRequest $request): RedirectResponse
    {
        $town = Town::create($this->attributes($request));

        if ($request->hasFile('hero_image')) {
            $town->update(['hero_image' => $this->storeHeroImage($request, $town)]);
        }

        return redirect()->route('admin.towns.index')
            ->with('success', "{$town->name} created. Run `php artisan towns:assign` to attach nearby trails.");
    }

    public function edit(Town $town): View
    {
        return view('admin.towns.edit', compact('town'));
    }

    public function update(TownRequest $request, Town $town): RedirectResponse
    {
        $town->update($this->attributes($request));

        if ($request->hasFile('hero_image')) {
            $this->deleteHeroImage($town);
            $town->update(['hero_image' => $this->storeHeroImage($request, $town)]);
        }

        return redirect()->route('admin.towns.index')->with('success', "{$town->name} updated.");
    }

    public function destroy(Town $town): RedirectResponse
    {
        $this->deleteHeroImage($town);
        $name = $town->name;
        $town->delete();

        return redirect()->route('admin.towns.index')
            ->with('success', "{$name} deleted. Its trails and businesses were kept and are now unassigned.");
    }

    public function toggleActive(Town $town): RedirectResponse
    {
        $town->update(['is_active' => ! $town->is_active]);

        return back()->with('success', "{$town->name} is now ".($town->is_active ? 'published' : 'hidden').'.');
    }

    public function destroyHeroImage(Town $town): RedirectResponse
    {
        $this->deleteHeroImage($town);
        $town->update(['hero_image' => null]);

        return back()->with('success', 'Hero image removed.');
    }

    /**
     * Normalise the submitted fields, translating the three-way indexing
     * choice back into the nullable boolean the column stores.
     *
     * @return array<string, mixed>
     */
    private function attributes(TownRequest $request): array
    {
        $attributes = $request->safe()->except(['hero_image', 'is_indexable']);

        $attributes['is_active'] = $request->boolean('is_active');
        $attributes['sort_order'] = (int) $request->input('sort_order', 0);
        $attributes['is_indexable'] = match ($request->input('is_indexable', 'auto')) {
            'index' => true,
            'noindex' => false,
            default => null,
        };

        return $attributes;
    }

    private function storeHeroImage(Request $request, Town $town): string
    {
        return $request->file('hero_image')->store("towns/{$town->id}", 'public');
    }

    private function deleteHeroImage(Town $town): void
    {
        if ($town->hero_image) {
            Storage::disk('public')->delete($town->hero_image);
        }
    }
}
