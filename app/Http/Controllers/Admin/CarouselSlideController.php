<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarouselSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CarouselSlideController extends Controller
{
    public function index(Request $request): View
    {
        // Also done nightly by carousel:expire; repeated here so the list is right even
        // if the server's scheduler is not running.
        CarouselSlide::deactivateExpired();

        $allSlides = CarouselSlide::ordered()->get();

        // Active / Inactive / All tabs; Active is the default.
        $status = in_array($request->query('status'), ['active', 'inactive', 'all'], true) ? $request->query('status') : 'active';
        $counts = [
            'active' => $allSlides->where('is_active', true)->count(),
            'inactive' => $allSlides->where('is_active', false)->count(),
            'all' => $allSlides->count(),
        ];
        $slides = match ($status) {
            'active' => $allSlides->where('is_active', true)->values(),
            'inactive' => $allSlides->where('is_active', false)->values(),
            default => $allSlides,
        };

        // Files already in storage but not yet registered in the database
        $registeredFilenames = $allSlides->pluck('filename')->all();

        $unregistered = collect(Storage::disk('public')->files('slide-show'))
            ->filter(fn ($f) => preg_match('/\.(jpe?g|png|gif|webp)$/i', $f))
            ->map(fn ($f) => basename($f))
            ->filter(fn ($f) => ! in_array($f, $registeredFilenames))
            ->values();

        return view('admin.carousel.index', compact('slides', 'unregistered', 'status', 'counts'));
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['filename' => 'required|string|max:255']);

        $filename = basename($request->input('filename'));

        if (! Storage::disk('public')->exists('slide-show/'.$filename)) {
            return back()->with('error', 'File not found in storage.');
        }

        CarouselSlide::create([
            'filename' => $filename,
            'caption' => ucwords(str_replace(['-', '_'], ' ', pathinfo($filename, PATHINFO_FILENAME))),
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return redirect()->route('admin.carousel.index')->with('success', 'Slide imported.');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'caption' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at', 'after_or_equal:'.CarouselSlide::today()],
        ], [
            'ends_at.after_or_equal' => 'The end date cannot be in the past or before the start date.',
        ]);

        $file = $request->file('image');
        $filename = $file->store('slide-show', 'public');
        $filename = basename($filename);

        CarouselSlide::create([
            'filename' => $filename,
            'caption' => $request->input('caption') ?: ucwords(str_replace(['-', '_'], ' ', pathinfo($filename, PATHINFO_FILENAME))),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => true,
            'starts_at' => $request->input('starts_at') ?: CarouselSlide::today(),
            'ends_at' => $request->input('ends_at') ?: null,
        ]);

        return redirect()->route('admin.carousel.index')->with('success', 'Slide uploaded successfully.');
    }

    public function update(Request $request, CarouselSlide $carousel): RedirectResponse
    {
        $request->validate([
            'caption' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'return_status' => 'nullable|in:active,inactive,all',
            'starts_at' => 'nullable|date',
            'ends_at' => [
                'nullable', 'date', 'after_or_equal:starts_at',
                // Re-activating a slide whose end date has passed would be switched off again.
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    if ($value && $request->boolean('is_active') && $value < CarouselSlide::today()) {
                        $fail('This slide\'s end date has passed. Change or clear the "To" date to make it active again.');
                    }
                },
            ],
        ]);

        $carousel->update([
            'caption' => $request->input('caption'),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
            'starts_at' => $request->input('starts_at') ?: null,
            'ends_at' => $request->input('ends_at') ?: null,
        ]);

        // Back to the same tab (Active / Inactive / All) the slide was edited from.
        return redirect()->route('admin.carousel.index', array_filter(['status' => $request->input('return_status')]))
            ->with('success', 'Slide updated.');
    }

    public function destroy(Request $request, CarouselSlide $carousel): RedirectResponse
    {
        Storage::disk('public')->delete('slide-show/'.$carousel->filename);
        $carousel->delete();

        return redirect()->route('admin.carousel.index', array_filter(['status' => $request->input('return_status')]))
            ->with('success', 'Slide deleted.');
    }
}
