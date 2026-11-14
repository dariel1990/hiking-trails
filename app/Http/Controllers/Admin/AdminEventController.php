<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventMedia;
use App\Models\EventStop;
use App\Services\ImageThumbnailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Admin CRUD for events. Events appear on /local-events (with their map) and
 * on the /events calendar. Older events imported by the SmithersEvents.com
 * scraper are listed and editable here too.
 */
class AdminEventController extends Controller
{
    public function index(Request $request): View
    {
        $when = $request->input('when', 'upcoming');
        $search = trim((string) $request->input('q', ''));
        $today = Carbon::today()->toDateString();

        $events = Event::query()
            ->withCount('stops')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('venue', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")))
            ->when($when === 'upcoming', fn ($q) => $q->where(fn ($q) => $q
                ->where('end_date', '>=', $today)
                ->orWhere(fn ($q) => $q->whereNull('end_date')->where('event_date', '>=', $today))))
            ->when($when === 'past', fn ($q) => $q->where(fn ($q) => $q
                ->where('end_date', '<', $today)
                ->orWhere(fn ($q) => $q->whereNull('end_date')->where('event_date', '<', $today))))
            ->when($request->input('source') === 'manual', fn ($q) => $q->whereNull('source_id'))
            ->when($request->input('source') === 'imported', fn ($q) => $q->whereNotNull('source_id'))
            ->when($when === 'past', fn ($q) => $q->orderByDesc('event_date'), fn ($q) => $q->orderBy('event_date'))
            ->orderBy('event_time')
            ->paginate(25)
            ->withQueryString();

        return view('admin.events.index', [
            'events' => $events,
            'when' => $when,
            'search' => $search,
            'source' => $request->input('source'),
        ]);
    }

    public function create(): View
    {
        return view('admin.events.create', ['event' => null, 'categories' => $this->categories()]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $event = Event::create($this->eventAttributes($request));

        if ($request->hasFile('cover_image')) {
            $event->update(['cover_image' => $this->storeCoverImage($request->file('cover_image'), $event)]);
        }

        $this->syncStops($request, $event);
        $this->syncMedia($request, $event);

        return redirect()->route('admin.local-events.index')
            ->with('success', 'Event created successfully.');
    }

    public function edit(Event $event): View
    {
        $event->load('stops', 'media');

        return view('admin.events.edit', ['event' => $event, 'categories' => $this->categories()]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $attributes = $this->eventAttributes($request, $event);

        if ($request->hasFile('cover_image')) {
            if ($event->cover_image) {
                Storage::disk('public')->delete($event->cover_image);
            }
            $attributes['cover_image'] = $this->storeCoverImage($request->file('cover_image'), $event);
        }

        $event->update($attributes);
        $this->syncStops($request, $event);
        $this->syncMedia($request, $event);

        return redirect()->route('admin.local-events.index')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->deleteEvent($event);

        return redirect()->route('admin.local-events.index')
            ->with('success', 'Event deleted successfully.');
    }

    public function deleteCoverImage(Event $event): JsonResponse
    {
        if ($event->cover_image) {
            Storage::disk('public')->delete($event->cover_image);
            $event->update(['cover_image' => null]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Upload one gallery photo to temporary storage as soon as it is picked,
     * so large photos don't all have to fit in one form submit. It is moved
     * into the event's folder when the event is saved.
     */
    public function uploadMediaPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:51200'],
        ], [
            'photo.image' => 'That file is not an image.',
            'photo.max' => 'Photos can be at most 50 MB.',
        ]);

        try {
            $processed = app(ImageThumbnailService::class)->process($request->file('photo'), 'events/tmp/photos');
        } catch (\Throwable $e) {
            Log::error('Event gallery photo upload failed to process.', ['message' => $e->getMessage()]);

            return response()->json([
                'message' => 'The server could not process that image. It may be too large or an unsupported format.',
            ], 422);
        }

        return response()->json([
            'path' => $processed['path'],
            'thumbnail_path' => $processed['thumbnail_path'],
            'thumbnail_url' => asset('storage/'.$processed['thumbnail_path']),
        ]);
    }

    /**
     * Delete a temporary gallery photo that was removed before the event was saved.
     */
    public function deleteUploadedMediaPhoto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'path' => ['required', 'string', 'starts_with:events/tmp/photos/', 'not_regex:/\.\./'],
            'thumbnail_path' => ['nullable', 'string', 'starts_with:events/tmp/photos/', 'not_regex:/\.\./'],
        ]);

        Storage::disk('public')->delete(array_filter([$validated['path'], $validated['thumbnail_path'] ?? null]));

        return response()->json(['deleted' => true]);
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:delete,activate,deactivate'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:events,id'],
        ], [
            'ids.required' => 'Select at least one event.',
        ]);

        $events = Event::query()->whereIn('id', $validated['ids'])->get();

        $events->each(match ($validated['action']) {
            'delete' => fn (Event $event) => $this->deleteEvent($event),
            'activate' => fn (Event $event) => $event->update(['is_active' => true]),
            'deactivate' => fn (Event $event) => $event->update(['is_active' => false]),
        });

        $verb = ['delete' => 'deleted', 'activate' => 'marked active', 'deactivate' => 'marked inactive'][$validated['action']];

        return redirect()->route('admin.local-events.index')
            ->with('success', "{$events->count()} event(s) {$verb}.");
    }

    /**
     * Categories already in use, suggested in the form so the calendar filter stays tidy.
     *
     * @return list<string>
     */
    private function categories(): array
    {
        return Event::query()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->all();
    }

    /**
     * Validated form fields mapped to event columns.
     *
     * @return array<string, mixed>
     */
    private function eventAttributes(StoreEventRequest $request, ?Event $event = null): array
    {
        $attributes = collect($request->validated())
            ->except(['cover_image', 'stops', 'slug', 'route_coordinates', 'media', 'media_sync'])
            ->all();

        $attributes['is_active'] = $request->boolean('is_active');
        $attributes['is_featured'] = $request->boolean('is_featured');
        $attributes['theme'] = $request->input('theme', Event::THEME_STANDARD);

        $slug = $request->validated('slug');
        if ($slug) {
            $attributes['slug'] = $slug;
        } elseif (! $event) {
            $attributes['slug'] = Event::uniqueSlug($request->validated('title'));
        }

        // An explicit "[]" (route cleared in the editor) removes the route; a
        // blank or invalid value keeps the existing one.
        $route = json_decode((string) $request->validated('route_coordinates'), true);
        if (is_array($route)) {
            $attributes['route_coordinates'] = $route ?: null;
            if (! $route) {
                $attributes['route_distance_km'] = null;
            }
        } elseif (! $event) {
            $attributes['route_coordinates'] = null;
        }

        return $attributes;
    }

    private function deleteEvent(Event $event): void
    {
        $paths = EventStop::where('event_id', $event->id)->whereNotNull('image')->pluck('image')->all();
        if ($event->cover_image) {
            $paths[] = $event->cover_image;
        }
        if ($paths) {
            Storage::disk('public')->delete($paths);
        }

        $event->media->each->delete();
        $event->delete();
    }

    private function storeCoverImage(UploadedFile $file, Event $event): string
    {
        return $this->compressAndStoreImage($file, 'events/'.$event->id, 1920, 1080);
    }

    private function compressAndStoreImage(UploadedFile $file, string $directory, int $maxWidth, int $maxHeight): string
    {
        $image = (new ImageManager(new Driver))->read($file->getRealPath());
        $image->scaleDown(width: $maxWidth, height: $maxHeight);

        $path = $directory.'/'.Str::random(40).'.webp';
        Storage::disk('public')->put($path, (string) $image->toWebp(85));

        return $path;
    }

    /**
     * Stops are re-created on every save, so a stop's current photo comes back
     * as `existing_image`. It is only kept when it really belongs to this event.
     *
     * @param  array<string, mixed>  $stop
     * @param  list<string>  $previousImages
     */
    private function resolveStopImage(Request $request, int|string $index, array $stop, Event $event, array $previousImages): ?string
    {
        $upload = $request->file("stops.{$index}.image");
        if ($upload instanceof UploadedFile) {
            return $this->compressAndStoreImage($upload, 'events/'.$event->id.'/stops', 1600, 1600);
        }

        if (filter_var($stop['remove_image'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return null;
        }

        $existing = $stop['existing_image'] ?? null;

        return $existing && in_array($existing, $previousImages, true) ? $existing : null;
    }

    private function syncStops(Request $request, Event $event): void
    {
        $previousImages = EventStop::where('event_id', $event->id)->whereNotNull('image')->pluck('image')->all();
        $keptImages = [];

        EventStop::where('event_id', $event->id)->delete();

        foreach ($request->input('stops', []) as $index => $stop) {
            $isPinned = isset($stop['latitude'], $stop['longitude'])
                && $stop['latitude'] !== ''
                && $stop['longitude'] !== '';
            $isRoaming = ! $isPinned && filter_var($stop['is_roaming'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (! $isPinned && ! $isRoaming) {
                continue;
            }

            $image = $this->resolveStopImage($request, $index, $stop, $event, $previousImages);
            if ($image) {
                $keptImages[] = $image;
            }

            EventStop::create([
                'event_id' => $event->id,
                'latitude' => $isPinned ? (float) $stop['latitude'] : null,
                'longitude' => $isPinned ? (float) $stop['longitude'] : null,
                'is_roaming' => $isRoaming,
                'stop_order' => $index,
                'stop_label' => $stop['stop_label'],
                'time_label' => $stop['time_label'] ?? null,
                'notes' => $stop['notes'] ?? null,
                'icon' => filled($stop['icon'] ?? null) ? trim($stop['icon']) : null,
                'icon_image' => filled($stop['icon_image'] ?? null) ? $stop['icon_image'] : null,
                'description' => $stop['description'] ?? null,
                'image' => $image,
            ]);
        }

        $orphanedImages = array_diff($previousImages, $keptImages);
        if ($orphanedImages) {
            Storage::disk('public')->delete(array_values($orphanedImages));
        }
    }

    /**
     * Make the gallery match the submitted list: its order is the display
     * order, existing items not in it are deleted, uploaded photos are moved
     * into the event's folder and video links are added. Skipped when the
     * gallery editor did not load (no media_sync flag), so nothing is lost.
     */
    private function syncMedia(StoreEventRequest $request, Event $event): void
    {
        if (! $request->boolean('media_sync')) {
            return;
        }

        $existing = $event->media()->get()->keyBy('id');
        $keptIds = [];
        $directory = 'events/'.$event->id.'/gallery';
        $disk = Storage::disk('public');

        foreach (array_values($request->validated('media') ?? []) as $order => $item) {
            $caption = filled($item['caption'] ?? null) ? trim($item['caption']) : null;

            if (! empty($item['id'])) {
                $media = $existing->get((int) $item['id']);
                if ($media) {
                    $media->update(['caption' => $caption, 'sort_order' => $order]);
                    $keptIds[] = $media->id;
                }

                continue;
            }

            if (! empty($item['path'])) {
                if (! $disk->exists($item['path'])) {
                    continue;
                }

                $filePath = $directory.'/'.basename($item['path']);
                $disk->move($item['path'], $filePath);

                $thumbnailPath = null;
                if (! empty($item['thumbnail_path']) && $disk->exists($item['thumbnail_path'])) {
                    $thumbnailPath = $directory.'/thumbs/'.basename($item['thumbnail_path']);
                    $disk->move($item['thumbnail_path'], $thumbnailPath);
                }

                $event->media()->create([
                    'media_type' => EventMedia::TYPE_PHOTO,
                    'file_path' => $filePath,
                    'thumbnail_path' => $thumbnailPath,
                    'caption' => $caption,
                    'sort_order' => $order,
                ]);

                continue;
            }

            $video = EventMedia::parseVideo($item['video_url']);
            $event->media()->create([
                'media_type' => EventMedia::TYPE_VIDEO,
                'url' => trim($item['video_url']),
                'video_provider' => $video['provider'],
                'caption' => $caption,
                'sort_order' => $order,
            ]);
        }

        $existing->except($keptIds)->each->delete();
    }
}
