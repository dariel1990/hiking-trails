<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\TrailNetwork;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $validated = $request->validate($this->networkRules());

        if (empty($validated['slug'])) {
            $validated['slug'] = $this->uniqueSlug($validated['network_name']);
        }

        $validated['is_always_visible'] = $request->has('is_always_visible');
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('trail-networks', 'public');
        }

        $sponsorsPayload = $this->validatedSponsors($request);

        $trailNetwork = TrailNetwork::create($validated);

        $this->syncSponsors($trailNetwork, $sponsorsPayload, $request);

        return redirect()->route('admin.trail-networks.index')
            ->with('success', 'Trail network created successfully.');
    }

    /**
     * Display the specified trail network
     */
    public function show(TrailNetwork $trailNetwork)
    {
        $trailNetwork->load(['trails', 'sponsors']);

        $facilities = Facility::where('is_active', true)
            ->where('trail_network_id', $trailNetwork->id)
            ->orderBy('facility_type')
            ->orderBy('name')
            ->get();

        return view('admin.trail-networks.show', compact('trailNetwork', 'facilities'));
    }

    /**
     * Show the form for editing the specified trail network
     */
    public function edit(TrailNetwork $trailNetwork)
    {
        $trailNetwork->load('sponsors');

        return view('admin.trail-networks.edit', compact('trailNetwork'));
    }

    /**
     * Update the specified trail network
     */
    public function update(Request $request, TrailNetwork $trailNetwork)
    {
        $validated = $request->validate($this->networkRules($trailNetwork->id));

        if (empty($validated['slug'])) {
            $validated['slug'] = $this->uniqueSlug($validated['network_name'], $trailNetwork->id);
        }

        $validated['is_always_visible'] = $request->has('is_always_visible');
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($trailNetwork->image) {
                Storage::disk('public')->delete($trailNetwork->image);
            }
            $validated['image'] = $request->file('image')->store('trail-networks', 'public');
        } elseif ($request->boolean('remove_image') && $trailNetwork->image) {
            Storage::disk('public')->delete($trailNetwork->image);
            $validated['image'] = null;
        } else {
            unset($validated['image']);
        }

        $sponsorsPayload = $this->validatedSponsors($request);

        $trailNetwork->update($validated);

        $this->syncSponsors($trailNetwork, $sponsorsPayload, $request);

        return redirect()->route('admin.trail-networks.index')
            ->with('success', 'Trail network updated successfully.');
    }

    /**
     * Toggle the active status of the specified trail network
     */
    public function toggleActive(TrailNetwork $trailNetwork): JsonResponse
    {
        $trailNetwork->update(['is_active' => ! $trailNetwork->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $trailNetwork->is_active,
        ]);
    }

    /**
     * Remove the specified trail network
     */
    public function destroy(TrailNetwork $trailNetwork)
    {
        if ($trailNetwork->trails()->count() > 0) {
            return redirect()->route('admin.trail-networks.index')
                ->with('error', 'Cannot delete trail network with existing trails. Please reassign or delete the trails first.');
        }

        if ($trailNetwork->image) {
            Storage::disk('public')->delete($trailNetwork->image);
        }

        foreach ($trailNetwork->sponsors as $sponsor) {
            if ($sponsor->logo) {
                Storage::disk('public')->delete($sponsor->logo);
            }
        }

        $trailNetwork->delete();

        return redirect()->route('admin.trail-networks.index')
            ->with('success', 'Trail network deleted successfully.');
    }

    /**
     * @return array<string, string>
     */
    private function networkRules(?int $ignoreId = null): array
    {
        $slugRule = 'nullable|string|max:255|unique:trail_networks,slug';
        if ($ignoreId) {
            $slugRule .= ','.$ignoreId;
        }

        return [
            'network_name' => 'required|string|max:255',
            'slug' => $slugRule,
            'type' => 'required|in:nordic_skiing,downhill_skiing,hiking,mountain_biking',
            'season' => 'required|in:summer,winter,both',
            'icon' => 'nullable|string|max:10',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
            'website_url' => 'nullable|url|max:500',
            'is_always_visible' => 'boolean',
            'is_active' => 'boolean',

            'sponsors' => 'nullable|array',
            'sponsors.*.id' => 'nullable|integer|exists:trail_network_sponsors,id',
            'sponsors.*.name' => 'nullable|string|max:255',
            'sponsors.*.tagline' => 'nullable|string|max:255',
            'sponsors.*.logo' => 'nullable|image|mimes:jpeg,jpg,png,webp,svg|max:2048',
            'sponsors.*.remove_logo' => 'nullable|in:0,1',
            'sponsors.*.url' => 'nullable|url|max:500',
            'sponsors.*.welcome_message' => 'nullable|string|max:255',
            'sponsors.*.banner_text' => 'nullable|string|max:255',
            'sponsors.*.cta_text' => 'nullable|string|max:255',
            'sponsors.*.sort_order' => 'nullable|integer|min:0|max:9999',
            'sponsors.*.is_active' => 'nullable|in:0,1',
            'sponsors.*._delete' => 'nullable|in:0,1',
        ];
    }

    /**
     * Pull only the validated sponsor rows out of the request.
     *
     * @return array<int, array<string, mixed>>
     */
    private function validatedSponsors(Request $request): array
    {
        $rows = $request->input('sponsors', []);
        if (! is_array($rows)) {
            return [];
        }

        return array_values($rows);
    }

    /**
     * Sync the sponsors array against the network: create, update, and delete rows as needed.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncSponsors(TrailNetwork $trailNetwork, array $rows, Request $request): void
    {
        $existing = $trailNetwork->sponsors()->get()->keyBy('id');
        $seenIds = [];

        foreach ($rows as $index => $row) {
            $markedForDelete = isset($row['_delete']) && (string) $row['_delete'] === '1';
            $rowId = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null;
            $name = trim((string) ($row['name'] ?? ''));

            if ($markedForDelete) {
                if ($rowId && $existing->has($rowId)) {
                    $sponsor = $existing->get($rowId);
                    if ($sponsor->logo) {
                        Storage::disk('public')->delete($sponsor->logo);
                    }
                    $sponsor->delete();
                }

                continue;
            }

            if ($name === '') {
                continue;
            }

            $data = [
                'name' => $name,
                'tagline' => $this->nullable($row['tagline'] ?? null),
                'url' => $this->nullable($row['url'] ?? null),
                'welcome_message' => $this->nullable($row['welcome_message'] ?? null),
                'banner_text' => $this->nullable($row['banner_text'] ?? null),
                'cta_text' => $this->nullable($row['cta_text'] ?? null),
                'sort_order' => (int) ($row['sort_order'] ?? 0),
                'is_active' => isset($row['is_active']) ? (string) $row['is_active'] === '1' : true,
            ];

            $logoFile = $request->file("sponsors.$index.logo");
            $removeLogo = isset($row['remove_logo']) && (string) $row['remove_logo'] === '1';

            $sponsor = $rowId ? $existing->get($rowId) : null;

            if ($logoFile) {
                if ($sponsor && $sponsor->logo) {
                    Storage::disk('public')->delete($sponsor->logo);
                }
                $data['logo'] = $logoFile->store('trail-network-sponsors', 'public');
            } elseif ($removeLogo && $sponsor && $sponsor->logo) {
                Storage::disk('public')->delete($sponsor->logo);
                $data['logo'] = null;
            }

            if ($sponsor) {
                $sponsor->update($data);
                $seenIds[] = $sponsor->id;
            } else {
                $created = $trailNetwork->sponsors()->create($data);
                $seenIds[] = $created->id;
            }
        }
    }

    private function nullable(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (TrailNetwork::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.++$i;
        }

        return $slug;
    }
}
