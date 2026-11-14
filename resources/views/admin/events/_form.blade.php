{{--
    Event form. It keeps the element ids of the tour form so it can reuse the
    tour editor script (admin.tours._scripts): icon gallery, cover image, video
    preview, and the Route & Map editor with pinned / roaming activities.
--}}
@php
$isEdit = $event !== null;
$inputClass = 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';

$existingStopsJson = $isEdit
    ? $event->stops->map(fn ($s) => [
        'latitude' => $s->is_roaming ? null : $s->latitude,
        'longitude' => $s->is_roaming ? null : $s->longitude,
        'item_type' => $s->is_roaming ? 'roaming' : 'pinned',
        'name' => $s->is_roaming ? 'Roaming activity' : 'Pinned on map',
        'start_coordinates' => $s->coordinates(),
        'stop_label' => $s->stop_label ?? '',
        'estimated_visit_time' => $s->time_label ?? '',
        'driving_notes' => $s->notes ?? '',
        'icon' => $s->icon ?? '',
        'icon_image' => $s->icon_image ?? '',
        'icon_image_url' => $s->icon_image_url,
        'description' => $s->description ?? '',
        'existing_image' => $s->image ?? '',
        'image_url' => $s->image_url,
    ])->values()->toJson()
    : '[]';

$routeJson = $isEdit && $event->route_coordinates ? json_encode($event->route_coordinates) : '';
$galleryItems = $isEdit
    ? $event->media->map(fn ($m) => [
        'id' => $m->id,
        'type' => $m->isPhoto() ? 'photo' : 'video',
        'thumbnail_url' => $m->thumbnail_url,
        'video_url' => $m->isVideo() ? $m->url : null,
        'caption' => $m->caption ?? '',
    ])->values()
    : collect();
$paradeSlug = config('app.christmas_parade_slug');
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" id="tour-form-root"
     x-data="tourForm([], [], [], {{ $existingStopsJson }})">

    <!-- Left: Main fields -->
    <div class="lg:col-span-2 space-y-6">

        @if($isEdit && ! $event->isManual())
            <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                This event was imported from SmithersEvents.com. Your changes are kept as long as the importer is not run again.
            </div>
        @endif

        {{-- Basic Info --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Basic Information</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="space-y-2">
                    <label for="title" class="text-sm font-medium leading-none">Event Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" required maxlength="255"
                        value="{{ old('title', $isEdit ? $event->title : '') }}"
                        placeholder="e.g., Smithers Christmas Parade"
                        class="{{ $inputClass }}">
                    @error('title') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="slug" class="text-sm font-medium leading-none">Web address</label>
                    <div class="flex items-center gap-1 text-sm">
                        <span class="text-muted-foreground whitespace-nowrap">{{ url('/local-events') }}/</span>
                        <input type="text" name="slug" id="slug" maxlength="255"
                            value="{{ old('slug', $isEdit ? $event->slug : '') }}"
                            placeholder="made from the title if left blank"
                            class="{{ $inputClass }}">
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Use <code>{{ $paradeSlug }}</code> for the Christmas parade so the {{ url('/christmas-parade') }} link shows it.
                    </p>
                    @error('slug') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="tagline" class="text-sm font-medium leading-none">Tagline</label>
                    <input type="text" name="tagline" id="tagline" maxlength="255"
                        value="{{ old('tagline', $isEdit ? $event->tagline : '') }}"
                        placeholder="Short one-liner shown on event cards"
                        class="{{ $inputClass }}">
                    @error('tagline') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="description" class="text-sm font-medium leading-none">Description</label>
                    <textarea name="description" id="description" rows="5"
                        placeholder="Describe the event..."
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">{{ old('description', $isEdit ? $event->description : '') }}</textarea>
                    @error('description') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Date & Time --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Date &amp; Time</h3>
                <p class="text-sm text-muted-foreground">The event drops off the Events page after its end date (or start date if it has no end date).</p>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="event_date" class="text-sm font-medium leading-none">Start date <span class="text-red-500">*</span></label>
                    <input type="date" name="event_date" id="event_date" required
                        value="{{ old('event_date', $isEdit ? $event->event_date?->toDateString() : '') }}"
                        class="{{ $inputClass }}">
                    @error('event_date') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-2">
                    <label for="end_date" class="text-sm font-medium leading-none">End date <span class="text-muted-foreground font-normal">(multi-day events)</span></label>
                    <input type="date" name="end_date" id="end_date"
                        value="{{ old('end_date', $isEdit ? $event->end_date?->toDateString() : '') }}"
                        class="{{ $inputClass }}">
                    @error('end_date') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-2">
                    <label for="event_time" class="text-sm font-medium leading-none">Start time</label>
                    <input type="time" name="event_time" id="event_time"
                        value="{{ old('event_time', $isEdit ? $event->event_time?->format('H:i') : '') }}"
                        class="{{ $inputClass }}">
                    @error('event_time') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-2">
                    <label for="end_time" class="text-sm font-medium leading-none">End time</label>
                    <input type="time" name="end_time" id="end_time"
                        value="{{ old('end_time', $isEdit ? $event->end_time?->format('H:i') : '') }}"
                        class="{{ $inputClass }}">
                    @error('end_time') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Where & Who --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Where &amp; Who</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="venue" class="text-sm font-medium leading-none">Venue</label>
                    <input type="text" name="venue" id="venue" maxlength="255"
                        value="{{ old('venue', $isEdit ? $event->venue : '') }}"
                        placeholder="e.g., Main Street"
                        class="{{ $inputClass }}">
                    @error('venue') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-2">
                    <label for="location" class="text-sm font-medium leading-none">Location / address</label>
                    <input type="text" name="location" id="location" maxlength="255"
                        value="{{ old('location', $isEdit ? $event->location : '') }}"
                        placeholder="e.g., Downtown Smithers, BC"
                        class="{{ $inputClass }}">
                    <p class="text-xs text-muted-foreground">Shown on the events calendar and in calendar invites. Uses the venue if left blank.</p>
                    @error('location') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-2">
                    <label for="organizer" class="text-sm font-medium leading-none">Organizer</label>
                    <input type="text" name="organizer" id="organizer" maxlength="255"
                        value="{{ old('organizer', $isEdit ? $event->organizer : '') }}"
                        class="{{ $inputClass }}">
                    @error('organizer') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-2">
                    <label for="category" class="text-sm font-medium leading-none">Category</label>
                    <input type="text" name="category" id="category" maxlength="100" list="event-categories"
                        value="{{ old('category', $isEdit ? $event->category : '') }}"
                        placeholder="e.g., Community"
                        class="{{ $inputClass }}">
                    <datalist id="event-categories">
                        @foreach($categories as $category)
                            <option value="{{ $category }}"></option>
                        @endforeach
                    </datalist>
                    <p class="text-xs text-muted-foreground">Used by the category filter on the events calendar.</p>
                    @error('category') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-2 sm:col-span-2">
                    <label for="external_url" class="text-sm font-medium leading-none">"More info" link</label>
                    <input type="url" name="external_url" id="external_url" maxlength="500"
                        value="{{ old('external_url', $isEdit ? $event->external_url : '') }}"
                        placeholder="https://www.facebook.com/events/..."
                        class="{{ $inputClass }}">
                    @error('external_url') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Look --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Look</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-2" x-data="{ christmas: @js(old('theme', $isEdit ? $event->theme : App\Models\Event::THEME_STANDARD) === App\Models\Event::THEME_CHRISTMAS) }">
                    <label for="theme" class="text-sm font-medium leading-none">Christmas theme</label>
                    <label for="theme" class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-md border border-input bg-background px-3 py-2">
                        <span class="text-sm" x-text="christmas ? '🎄 On' : 'Off'">{{ old('theme', $isEdit ? $event->theme : App\Models\Event::THEME_STANDARD) === App\Models\Event::THEME_CHRISTMAS ? '🎄 On' : 'Off' }}</span>
                        <input type="hidden" name="theme" value="{{ App\Models\Event::THEME_STANDARD }}">
                        <input type="checkbox" id="theme" name="theme" value="{{ App\Models\Event::THEME_CHRISTMAS }}" x-model="christmas" class="peer sr-only"
                            @checked(old('theme', $isEdit ? $event->theme : App\Models\Event::THEME_STANDARD) === App\Models\Event::THEME_CHRISTMAS)>
                        <span class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full bg-gray-300 transition-colors duration-200 peer-checked:bg-red-600 peer-focus-visible:ring-4 peer-focus-visible:ring-red-100">
                            <span class="absolute left-0.5 inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200" :class="christmas ? 'translate-x-5' : 'translate-x-0'"></span>
                        </span>
                    </label>
                    <p class="text-xs text-muted-foreground">Turns the event page festive: Christmas lights, snowfall, holiday fonts and colours (set the colour in Settings → Events), and a red route on the map.</p>
                    @error('theme') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 space-y-3">
                    <label class="text-sm font-medium leading-none">Event Icon</label>

                    <div class="flex flex-wrap gap-2 min-h-[2.5rem] items-center" id="tour-icon-gallery">
                        <span class="text-xs text-muted-foreground italic self-center">Loading icons…</span>
                    </div>

                    <div id="tour-icon-image-preview" class="hidden items-center gap-2 text-xs text-green-700 bg-green-50 border border-green-200 rounded-md px-3 py-1.5">
                        <img id="tour-icon-image-preview-img" src="" alt="" class="w-6 h-6 object-contain rounded">
                        <span id="tour-icon-image-name" class="truncate flex-1"></span>
                        <button type="button" id="tour-icon-image-clear" class="ml-auto text-red-500 hover:text-red-700 shrink-0" title="Remove custom icon">✕</button>
                    </div>

                    <div class="flex items-center gap-2">
                        <label for="tour-icon-image-input" id="tour-icon-dropzone" class="cursor-pointer inline-flex items-center gap-1.5 rounded-md border border-dashed border-input px-3 py-1.5 text-xs text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Upload or drag new icon
                        </label>
                        <input type="file" id="tour-icon-image-input" accept="image/*" class="hidden">
                        <span id="tour-icon-upload-status" class="text-xs text-muted-foreground"></span>
                    </div>

                    <input type="hidden" name="icon_image" id="tour-icon-image-path" value="{{ old('icon_image', $isEdit ? ($event->icon_image ?? '') : '') }}">

                    <div class="border-t pt-2 space-y-1">
                        <label for="tour_icon" class="text-xs font-medium text-muted-foreground">Or emoji (used when no image is selected)</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="icon" id="tour_icon" maxlength="16"
                                value="{{ old('icon', $isEdit ? ($event->icon ?? '') : '') }}"
                                placeholder="e.g. 🎄"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-2 text-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                            <button type="button" onclick="document.getElementById('tour_icon').value=''" title="Clear emoji"
                                class="flex-shrink-0 h-9 w-9 flex items-center justify-center rounded-md border border-input bg-background hover:bg-muted text-muted-foreground text-xs">✕</button>
                        </div>
                    </div>
                    @error('icon') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('icon_image') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2 sm:col-span-3">
                    <label for="video_url" class="text-sm font-medium leading-none">Video Link (YouTube or Vimeo)</label>
                    <input type="url" name="video_url" id="video_url" maxlength="500"
                        value="{{ old('video_url', $isEdit ? $event->video_url : '') }}"
                        placeholder="https://www.youtube.com/watch?v=..."
                        oninput="updateTourVideoPreview(this.value)"
                        class="{{ $inputClass }}">
                    @error('video_url') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    @php $videoThumb = $isEdit ? $event->video_thumbnail_url : null; @endphp
                    <div id="tour-video-preview" class="{{ $videoThumb ? '' : 'hidden' }} pt-1">
                        <button type="button" onclick="playAdminVideo(document.getElementById('video_url').value)"
                            class="relative block w-full max-w-xs aspect-video rounded-md overflow-hidden bg-gray-900 group">
                            <img id="tour-video-preview-img" src="{{ $videoThumb ?? '' }}" alt="Video preview"
                                class="absolute inset-0 w-full h-full object-cover {{ $videoThumb ? '' : 'hidden' }}">
                            <span class="absolute inset-0 flex items-center justify-center">
                                <span class="w-12 h-12 rounded-full bg-white/90 flex items-center justify-center shadow group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-gray-900 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cover Image --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Cover Image</h3>
                <p class="text-sm text-muted-foreground">Hero photo for the event page and its card</p>
            </div>
            <div class="p-6 space-y-4">
                @if($isEdit && $event->cover_image)
                    <div id="current-cover-image" class="space-y-2">
                        <p class="text-sm font-medium leading-none">Current Cover Image</p>
                        <div class="relative w-full max-w-sm">
                            <img src="{{ $event->cover_image_url }}" alt="Current cover" class="w-full h-40 object-cover rounded-md border">
                            <button type="button" title="Delete cover image"
                                onclick="deleteTourCoverImage('{{ route('admin.local-events.cover-image.delete', $event) }}', this)"
                                class="absolute top-2 right-2 inline-flex h-7 w-7 items-center justify-center rounded-full bg-black/60 text-white hover:bg-destructive transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
                <div id="cover-image-dropzone" class="border-2 border-dashed border-input rounded-lg p-8 text-center hover:bg-muted/50 transition-colors cursor-pointer"
                    onclick="document.getElementById('cover_image').click()">
                    <input type="file" id="cover_image" name="cover_image" accept="image/*" class="hidden" onchange="previewCoverImage(this)">
                    <svg class="mx-auto h-12 w-12 text-muted-foreground mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-muted-foreground">Click or drag cover image here to upload</p>
                    <p class="text-xs text-muted-foreground mt-1">JPEG, PNG, WebP up to 50MB</p>
                </div>
                <div id="cover-preview" class="hidden">
                    <img id="cover-preview-img" src="" alt="Preview" class="w-full max-w-sm h-40 object-cover rounded-md border">
                </div>
                @error('cover_image') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Photos & Videos --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm"
            x-data="eventGallery(@js($galleryItems))">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Photos &amp; Videos</h3>
                <p class="text-sm text-muted-foreground">A gallery shown on the event page. Photos upload as soon as you pick them; use the arrows to change the order.</p>
            </div>
            <div class="p-6 space-y-4">
                <template x-if="true"><input type="hidden" name="media_sync" value="1"></template>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                    <label class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md border border-dashed border-input bg-background px-4 h-10 text-sm font-medium hover:bg-muted transition-colors"
                        @dragover.prevent @drop.prevent="addFiles($event.dataTransfer.files)">
                        📷 Add photos
                        <input type="file" accept="image/*" multiple class="hidden" @change="addFiles($event.target.files); $event.target.value = ''">
                    </label>
                    <div class="flex flex-1 gap-2">
                        <input type="url" x-model="newVideoUrl" @keydown.enter.prevent="addVideo()"
                            placeholder="Paste a YouTube or Vimeo link"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                        <button type="button" @click="addVideo()"
                            class="inline-flex flex-shrink-0 items-center justify-center rounded-md border border-input bg-background hover:bg-accent h-10 px-4 text-sm font-medium transition-colors">
                            🎬 Add video
                        </button>
                    </div>
                </div>
                <p x-show="error" x-text="error" class="text-sm text-red-600"></p>
                @error('media') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                @foreach($errors->get('media.*') as $messages)
                    <p class="text-sm text-red-600">{{ $messages[0] }}</p>
                @endforeach

                <div x-show="items.length > 0" class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <template x-for="(item, index) in items" :key="item.key">
                        <div class="overflow-hidden rounded-md border bg-muted/30">
                            <div class="relative aspect-video bg-gray-900">
                                <template x-if="item.thumbnail_url">
                                    <img :src="item.thumbnail_url" alt="" class="h-full w-full object-cover">
                                </template>
                                <div x-show="item.uploading" class="absolute inset-0 flex items-center justify-center bg-black/50 text-xs font-medium text-white">Uploading…</div>
                                <span x-show="item.type === 'video'" class="absolute left-1.5 top-1.5 rounded bg-red-600 px-1.5 py-0.5 text-[10px] font-semibold text-white">▶ Video</span>
                                <span x-show="index === 0" class="absolute right-1.5 top-1.5 rounded bg-black/60 px-1.5 py-0.5 text-[10px] font-semibold text-white">First</span>
                            </div>
                            <div class="space-y-2 p-2">
                                <input type="text" x-model="item.caption" maxlength="255" placeholder="Caption (optional)"
                                    class="flex h-8 w-full rounded-md border border-input bg-background px-2 text-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                <div class="flex items-center justify-between gap-1">
                                    <div class="flex gap-1">
                                        <button type="button" @click="move(index, -1)" :disabled="index === 0" title="Move earlier"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded border border-input bg-background text-xs hover:bg-accent disabled:opacity-30">◀</button>
                                        <button type="button" @click="move(index, 1)" :disabled="index === items.length - 1" title="Move later"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded border border-input bg-background text-xs hover:bg-accent disabled:opacity-30">▶</button>
                                    </div>
                                    <button type="button" @click="remove(index)"
                                        class="inline-flex h-7 items-center rounded border border-red-200 bg-red-50 px-2 text-xs text-red-600 hover:bg-red-100">Remove</button>
                                </div>
                            </div>

                            <template x-if="! item.uploading">
                                <div>
                                    <input type="hidden" :name="`media[${index}][id]`" :value="item.id || ''">
                                    <input type="hidden" :name="`media[${index}][path]`" :value="item.path || ''">
                                    <input type="hidden" :name="`media[${index}][thumbnail_path]`" :value="item.thumbnail_path || ''">
                                    <input type="hidden" :name="`media[${index}][video_url]`" :value="item.id ? '' : (item.video_url || '')">
                                    <input type="hidden" :name="`media[${index}][caption]`" :value="item.caption">
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <p x-show="items.length === 0" class="text-sm text-muted-foreground text-center py-4">No photos or videos yet.</p>
            </div>
        </div>

        {{-- Route & Map editor --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Route &amp; Map <span class="text-sm font-normal text-muted-foreground">(optional)</span></h3>
                <p class="text-sm text-muted-foreground">
                    Draw a route (e.g. the parade route) by clicking along the streets in order — arrows show the direction you draw it.
                    Use <strong>Pin activity on map</strong> to add places people can visit. Leave this empty for a simple event; the page then hides the map.
                </p>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex flex-wrap gap-2">
                    <button type="button" id="route-draw-btn" onclick="tourRouteEditor.toggleMode('draw')"
                        class="route-tool-btn inline-flex items-center gap-1.5 rounded-md border border-input bg-background hover:bg-accent h-9 px-3 text-sm font-medium transition-colors">
                        ✏️ Draw route
                    </button>
                    <button type="button" id="route-pin-btn" onclick="tourRouteEditor.toggleMode('pin')"
                        class="route-tool-btn inline-flex items-center gap-1.5 rounded-md border border-input bg-background hover:bg-accent h-9 px-3 text-sm font-medium transition-colors">
                        📍 Pin activity on map
                    </button>
                    <span class="w-px bg-border mx-1 hidden sm:block"></span>
                    <button type="button" id="route-undo-btn" onclick="tourRouteEditor.undo()"
                        class="inline-flex items-center gap-1.5 rounded-md border border-input bg-background hover:bg-accent h-9 px-3 text-sm font-medium transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                        ↶ Undo
                    </button>
                    <button type="button" id="route-clear-btn" onclick="tourRouteEditor.clear()"
                        class="inline-flex items-center gap-1.5 rounded-md border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 h-9 px-3 text-sm font-medium transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                        Clear route
                    </button>
                    <span class="w-px bg-border mx-1 hidden sm:block"></span>
                    <button type="button" id="route-snap-btn" onclick="tourRouteEditor.snapToStreets()"
                        title="Re-trace the drawn line along real roads (uses the route service)"
                        class="inline-flex items-center gap-1.5 rounded-md border border-input bg-background hover:bg-accent h-9 px-3 text-sm font-medium transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                        🧲 Snap to streets
                    </button>
                </div>

                <div id="route-mode-hint" class="hidden rounded-md bg-blue-50 border border-blue-200 px-3 py-2 text-xs text-blue-800"></div>
                <div id="route-status" class="hidden text-sm text-muted-foreground"></div>

                <div class="relative">
                    <div id="route-map" data-default-layer="satellite" class="w-full h-[460px] rounded-md border"></div>
                    <div class="absolute top-3 left-3 z-10 inline-flex bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden text-xs font-medium">
                        <button type="button" data-map-layer="standard" onclick="tourRouteEditor.setLayer('standard')"
                            class="route-layer-btn px-3 py-1.5 transition-colors bg-white text-gray-700 hover:bg-gray-50">Standard</button>
                        <button type="button" data-map-layer="satellite" onclick="tourRouteEditor.setLayer('satellite')"
                            class="route-layer-btn px-3 py-1.5 transition-colors bg-green-600 text-white border-l border-gray-200">Satellite</button>
                    </div>
                </div>

                <p class="text-xs text-muted-foreground">
                    <span id="route-point-count">0</span> route points · <span id="route-km">0.0</span> km.
                    Tip: zoom in close and click at each corner. Pinned activities can be dragged to fine-tune them.
                </p>
                <textarea name="route_coordinates" id="driving_route_coordinates" class="hidden">{{ $routeJson }}</textarea>
                <input type="hidden" name="route_distance_km" id="total_driving_km" value="{{ old('route_distance_km', $isEdit ? $event->route_distance_km : '') }}">
            </div>
        </div>

        {{-- Activities --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Activities &amp; Stops</h3>
                <p class="text-sm text-muted-foreground">Pin activities with <strong>Pin activity on map</strong> above, or add a roaming activity that has no fixed spot (e.g. carollers). Give each one an icon so visitors can tap it on the map.</p>
            </div>
            <div class="p-6 space-y-4">
                <button type="button" @click="addRoamingStop()"
                    class="inline-flex items-center gap-1.5 rounded-md border border-dashed border-input bg-background hover:bg-accent h-8 px-3 text-xs font-medium transition-colors">
                    🚶 Add roaming activity <span class="text-muted-foreground">(no fixed spot)</span>
                </button>
                @error('stops') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                @foreach($errors->get('stops.*') as $messages)
                    <p class="text-sm text-red-600">{{ $messages[0] }}</p>
                @endforeach

                <div x-show="stops.length > 0" class="space-y-2">
                    <template x-for="(stop, index) in stops" :key="stop.uid">
                        <div class="flex gap-3 items-start p-3 rounded-md border bg-muted/30" data-stop-row>
                            <div class="flex-shrink-0 flex h-7 w-7 items-center justify-center rounded-full bg-black text-white text-xs font-bold mt-0.5 overflow-hidden">
                                <template x-if="stop.icon_image_url">
                                    <img :src="stop.icon_image_url" alt="" class="h-full w-full object-contain bg-white">
                                </template>
                                <span x-show="!stop.icon_image_url" x-text="stop.icon || (index + 1)" :class="stop.icon ? 'text-base' : ''"></span>
                            </div>

                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <span x-show="stop.item_type === 'pinned'"
                                        class="inline-flex items-center rounded-full bg-red-50 border border-red-200 px-1.5 py-0.5 text-xs font-medium text-red-700"
                                        x-text="'📍 ' + Number(stop.latitude).toFixed(5) + ', ' + Number(stop.longitude).toFixed(5)"></span>
                                    <span x-show="stop.item_type === 'roaming'"
                                        class="inline-flex items-center rounded-full bg-purple-50 border border-purple-200 px-1.5 py-0.5 text-xs font-medium text-purple-700">
                                        🚶 Roaming · listed only, no map pin
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <input type="text" :name="`stops[${index}][stop_label]`" x-model="stop.stop_label" required maxlength="255"
                                        placeholder="Name (required, e.g. Wagon Rides)"
                                        class="flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                    <input type="text" :name="`stops[${index}][time_label]`" x-model="stop.estimated_visit_time" maxlength="100"
                                        placeholder="Time (e.g., 12:00 – 4:00 PM)"
                                        class="flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                    <input type="text" :name="`stops[${index}][notes]`" x-model="stop.driving_notes" maxlength="500"
                                        placeholder="Where / note (e.g., Bugwood Beans, Main St)"
                                        class="flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring sm:col-span-2">
                                    <input type="hidden" :name="`stops[${index}][latitude]`" :value="stop.item_type === 'pinned' ? stop.latitude : ''">
                                    <input type="hidden" :name="`stops[${index}][longitude]`" :value="stop.item_type === 'pinned' ? stop.longitude : ''">
                                    <input type="hidden" :name="`stops[${index}][is_roaming]`" :value="stop.item_type === 'roaming' ? 1 : 0">
                                    <input type="hidden" :name="`stops[${index}][icon]`" :value="stop.icon || ''">
                                    <input type="hidden" :name="`stops[${index}][icon_image]`" :value="stop.icon_image || ''">
                                    <input type="hidden" :name="`stops[${index}][existing_image]`" :value="stop.existing_image || ''">
                                    <input type="hidden" :name="`stops[${index}][remove_image]`" :value="stop.remove_image ? 1 : 0">
                                </div>

                                <div class="space-y-2 rounded-md border bg-background p-2.5">
                                    <div class="space-y-1.5">
                                        <p class="text-xs font-medium">Map icon</p>
                                        <div class="flex flex-wrap items-center gap-1">
                                            <template x-for="emoji in iconPresets" :key="emoji">
                                                <button type="button" @click="setStopEmoji(stop, emoji)"
                                                    :class="stop.icon === emoji && !stop.icon_image ? 'border-red-500 bg-red-50' : 'border-transparent hover:bg-muted'"
                                                    class="h-8 w-8 rounded-md border text-lg leading-none" x-text="emoji" :title="'Use ' + emoji"></button>
                                            </template>
                                            <input type="text" maxlength="16" x-model="stop.icon" @input="stop.icon_image = ''; stop.icon_image_url = null"
                                                placeholder="or type"
                                                class="h-8 w-20 rounded-md border border-input bg-background px-2 text-sm placeholder:text-xs placeholder:text-muted-foreground">
                                            <button type="button" @click="toggleIconPicker(stop)"
                                                class="h-8 rounded-md border border-input bg-background px-2 text-xs font-medium hover:bg-accent">
                                                🖼️ Image icon
                                            </button>
                                            <button type="button" x-show="stop.icon || stop.icon_image" @click="clearStopIcon(stop)"
                                                class="h-8 rounded-md px-2 text-xs text-red-600 hover:bg-red-50">Clear</button>
                                        </div>
                                        <div x-show="stop.showIconPicker" class="rounded-md border bg-muted/30 p-2 space-y-2">
                                            <div class="flex flex-wrap gap-1.5">
                                                <template x-for="ic in iconGallery" :key="ic.path">
                                                    <button type="button" @click="chooseStopIconImage(stop, ic)"
                                                        :class="stop.icon_image === ic.path ? 'border-red-500' : 'border-transparent hover:border-gray-300'"
                                                        class="h-9 w-9 rounded-md border-2 bg-white p-0.5">
                                                        <img :src="ic.url" alt="" class="h-full w-full object-contain">
                                                    </button>
                                                </template>
                                                <span x-show="iconGalleryStatus" x-text="iconGalleryStatus" class="text-xs text-muted-foreground self-center"></span>
                                            </div>
                                            <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-dashed border-input bg-background px-2 py-1 text-xs text-muted-foreground hover:bg-muted">
                                                Upload new icon
                                                <input type="file" accept="image/*" class="hidden" @change="uploadStopIcon(stop, $event)">
                                            </label>
                                        </div>
                                    </div>

                                    <textarea :name="`stops[${index}][description]`" x-model="stop.description" rows="2" maxlength="2000"
                                        placeholder="What's happening (e.g., Free horse-drawn wagon rides around downtown)"
                                        class="flex w-full rounded-md border border-input bg-background px-2 py-1.5 text-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"></textarea>

                                    <div class="flex items-center gap-2">
                                        <template x-if="stop.new_image_preview || (stop.image_url && !stop.remove_image)">
                                            <img :src="stop.new_image_preview || stop.image_url" alt="" class="h-12 w-12 rounded-md border object-cover">
                                        </template>
                                        <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-dashed border-input bg-background px-2 py-1.5 text-xs text-muted-foreground hover:bg-muted">
                                            📷 <span x-text="(stop.new_image_preview || (stop.image_url && !stop.remove_image)) ? 'Replace photo' : 'Add photo / flyer (optional)'"></span>
                                            <input type="file" accept="image/*" class="hidden" :name="`stops[${index}][image]`" @change="previewStopImage(stop, $event)">
                                        </label>
                                        <button type="button" x-show="stop.new_image_preview || (stop.image_url && !stop.remove_image)"
                                            @click="removeStopImage(stop, $el)"
                                            class="text-xs text-red-600 hover:underline">Remove photo</button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1 flex-shrink-0">
                                <button type="button" @click="moveUp(index)" :disabled="index === 0" title="Move up"
                                    class="inline-flex items-center justify-center h-6 w-6 rounded border border-input bg-background hover:bg-accent disabled:opacity-30 disabled:cursor-not-allowed text-xs">▲</button>
                                <button type="button" @click="moveDown(index)" :disabled="index === stops.length - 1" title="Move down"
                                    class="inline-flex items-center justify-center h-6 w-6 rounded border border-input bg-background hover:bg-accent disabled:opacity-30 disabled:cursor-not-allowed text-xs">▼</button>
                                <button type="button" @click="removeStop(index)" title="Remove"
                                    class="inline-flex items-center justify-center h-6 w-6 rounded border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 text-xs">✕</button>
                            </div>
                        </div>
                    </template>
                </div>

                <p x-show="stops.length === 0" class="text-sm text-muted-foreground text-center py-4">
                    No activities yet.
                </p>
            </div>
        </div>
    </div>

    <!-- Right: Settings sidebar -->
    <div class="space-y-6 lg:sticky lg:top-24 lg:self-start lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">{{ $isEdit ? 'Save Changes' : 'Publish' }}</h3>
            </div>
            <div class="p-6 flex flex-col gap-2">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium transition-colors w-full">
                    {{ $isEdit ? 'Update Event' : 'Create Event' }}
                </button>
                <a href="{{ route('admin.local-events.index') }}"
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent h-10 px-4 py-2 text-sm font-medium transition-colors w-full">
                    Cancel
                </a>
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Settings</h3>
            </div>
            <div class="p-6 space-y-4">
                @include('admin._town-select', ['selected' => $isEdit ? $event->town_id : null])

                <div class="pt-2 border-t space-y-3">
                    <div class="flex items-center space-x-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', $isEdit ? $event->is_active : true) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
                        <label for="is_active" class="text-sm font-medium leading-none">Active (visible on site)</label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1"
                            {{ old('is_featured', $isEdit ? $event->is_featured : false) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
                        <label for="is_featured" class="text-sm font-medium leading-none">Featured (shown first)</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-4">
            <p class="text-sm text-muted-foreground">
                <span class="font-semibold text-foreground" x-text="stops.length"></span> activit<span x-text="stops.length === 1 ? 'y' : 'ies'"></span> added
            </p>
        </div>
    </div>
</div>
