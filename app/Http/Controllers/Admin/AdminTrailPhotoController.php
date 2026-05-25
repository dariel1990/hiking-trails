<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trail;
use App\Models\TrailPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTrailPhotoController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->value() ?: TrailPhoto::STATUS_PENDING;
        $trailId = $request->integer('trail_id') ?: null;

        $photos = TrailPhoto::query()
            ->with(['trail:id,name,location_type', 'reviewer:id,name'])
            ->when(in_array($status, $this->validStatuses(), true), fn ($q) => $q->where('status', $status))
            ->when($trailId, fn ($q) => $q->where('trail_id', $trailId))
            ->latest()
            ->paginate(24)
            ->withQueryString();

        $counts = TrailPhoto::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $trailsWithSubmissions = Trail::query()
            ->whereHas('photos')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.trail-photos.index', [
            'photos' => $photos,
            'status' => $status,
            'trailId' => $trailId,
            'counts' => $counts,
            'trailsWithSubmissions' => $trailsWithSubmissions,
        ]);
    }

    public function update(Request $request, TrailPhoto $trailPhoto): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', $this->validStatuses())],
        ]);

        $trailPhoto->setStatus($data['status'], $request->user());

        return redirect()
            ->back()
            ->with('success', match ($data['status']) {
                TrailPhoto::STATUS_APPROVED => 'Photo approved and is now public.',
                TrailPhoto::STATUS_REJECTED => 'Photo rejected and files removed.',
                default => 'Photo status updated.',
            });
    }

    public function destroy(TrailPhoto $trailPhoto): RedirectResponse
    {
        $trailPhoto->delete();

        return redirect()
            ->back()
            ->with('success', 'Photo permanently deleted.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'photo_ids' => ['required', 'array', 'min:1'],
            'photo_ids.*' => ['integer', 'exists:trail_photos,id'],
            'status' => ['required', 'string', 'in:'.implode(',', $this->validStatuses())],
        ]);

        $photos = TrailPhoto::query()->whereIn('id', $data['photo_ids'])->get();

        foreach ($photos as $photo) {
            $photo->setStatus($data['status'], $request->user());
        }

        return redirect()
            ->back()
            ->with('success', "{$photos->count()} photo(s) updated.");
    }

    /**
     * @return array<int, string>
     */
    private function validStatuses(): array
    {
        return [
            TrailPhoto::STATUS_PENDING,
            TrailPhoto::STATUS_APPROVED,
            TrailPhoto::STATUS_REJECTED,
        ];
    }
}
