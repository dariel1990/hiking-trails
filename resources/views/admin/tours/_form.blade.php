@php $isEdit = isset($tour) && $tour !== null; @endphp

{{-- Available trails as JSON for Alpine.js --}}
@php
$availableTrailsJson = $availableTrails->map(fn ($t) => [
    'item_type' => 'trail',
    'id' => $t->id,
    'name' => $t->name,
    'location_type' => $t->location_type,
    'start_coordinates' => $t->start_coordinates,
])->values()->toJson();

$availableFeaturesJson = $availableFeatures->map(fn ($f) => [
    'item_type' => 'feature',
    'id' => $f->id,
    'trail_id' => $f->trail_id,
    'trail_name' => $f->trail?->name ?? '',
    'feature_type' => $f->feature_type,
    'name' => $f->name,
    'coordinates' => $f->coordinates,
])->values()->toJson();

$availableFacilitiesJson = $availableFacilities->map(fn ($f) => [
    'item_type' => 'facility',
    'id' => $f->id,
    'facility_type' => $f->facility_type,
    'facility_type_label' => \App\Models\Facility::getFacilityTypes()[$f->facility_type] ?? $f->facility_type,
    'name' => $f->name,
    'start_coordinates' => [(float) $f->latitude, (float) $f->longitude],
])->values()->toJson();

$stopsWithErrors = \App\Support\TourStopFormData::positionsWithErrors($errors->getMessages());
$formStops = is_array(old('stops'))
    ? \App\Support\TourStopFormData::fromOldInput(old('stops'), $availableTrails, $availableFeatures, $availableFacilities, $stopsWithErrors)
    : ($isEdit ? \App\Support\TourStopFormData::fromStops($tour->stops) : []);
$existingStopsJson = json_encode($formStops);

$formMeta = [
    'title' => old('title', $isEdit ? $tour->title : ''),
    'description' => old('description', $isEdit ? ($tour->description ?? '') : ''),
    'hasCover' => $isEdit && $tour->cover_image,
    'hasErrors' => $errors->any(),
];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" id="tour-form-root"
     x-data="tourForm({{ $availableTrailsJson }}, {{ $availableFeaturesJson }}, {{ $availableFacilitiesJson }}, {{ $existingStopsJson }}, {{ json_encode($formMeta) }})">

    @include('admin.tours._form-toolbar')

    <!-- Left: Main fields -->
    <div class="lg:col-span-2 space-y-6">

        {{-- Basic Info --}}
        <div id="tour-basics" class="scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Basic Information</h3>
            </div>
            <div class="p-6 space-y-4">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2 space-y-2">
                        <label for="tour_type" class="text-sm font-medium leading-none">
                            {{ $section['singular'] }} Type <span class="text-red-500">*</span>
                        </label>
                        <select name="tour_type" id="tour_type" required
                            onchange="syncTourTypeIcon(this.value)"
                            class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                            <option value="">Select tour type</option>
                            @foreach($section['types'] as $type => $label)
                                <option value="{{ $type }}" {{ old('tour_type', $isEdit ? $tour->tour_type : (count($section['types']) === 1 ? array_key_first($section['types']) : '')) === $type ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('tour_type') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-3">
                        <label class="text-sm font-medium leading-none">Custom Icon</label>

                        {{-- Gallery of previously uploaded icons --}}
                        <div class="flex flex-wrap gap-2 min-h-[2.5rem] items-center" id="tour-icon-gallery">
                            <span class="text-xs text-muted-foreground italic self-center">Loading icons…</span>
                        </div>

                        {{-- Selected icon preview --}}
                        <div id="tour-icon-image-preview" class="hidden items-center gap-2 text-xs text-green-700 bg-green-50 border border-green-200 rounded-md px-3 py-1.5">
                            <img id="tour-icon-image-preview-img" src="" alt="" class="w-6 h-6 object-contain rounded">
                            <span id="tour-icon-image-name" class="truncate flex-1"></span>
                            <button type="button" id="tour-icon-image-clear" class="ml-auto text-red-500 hover:text-red-700 shrink-0" title="Remove custom icon">✕</button>
                        </div>

                        {{-- Upload new icon --}}
                        <div class="flex items-center gap-2">
                            <label for="tour-icon-image-input" id="tour-icon-dropzone" class="cursor-pointer inline-flex items-center gap-1.5 rounded-md border border-dashed border-input px-3 py-1.5 text-xs text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Upload or drag new icon
                            </label>
                            <input type="file" id="tour-icon-image-input" accept="image/*" class="hidden">
                            <span id="tour-icon-upload-status" class="text-xs text-muted-foreground"></span>
                        </div>

                        {{-- Hidden field submitted with form --}}
                        <input type="hidden" name="icon_image" id="tour-icon-image-path" value="{{ old('icon_image', $isEdit ? ($tour->icon_image ?? '') : '') }}">

                        {{-- Emoji fallback --}}
                        <div class="border-t pt-2 space-y-1">
                            <label for="tour_icon" class="text-xs font-medium text-muted-foreground">Or emoji fallback (used when no image selected)</label>
                            <div class="flex gap-2 items-center">
                                <input type="text" name="icon" id="tour_icon"
                                    value="{{ old('icon', $isEdit ? ($tour->icon ?? '') : '') }}"
                                    placeholder="e.g. 🏔️"
                                    maxlength="10"
                                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-2 text-xl ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <button type="button" onclick="document.getElementById('tour_icon').value=''"
                                    title="Clear emoji"
                                    class="flex-shrink-0 h-9 w-9 flex items-center justify-center rounded-md border border-input bg-background hover:bg-muted text-muted-foreground text-xs">✕</button>
                            </div>
                        </div>
                        @error('icon') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('icon_image') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="title" class="text-sm font-medium leading-none">
                        {{ $section['singular'] }} Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" required x-model="meta.title"
                        value="{{ old('title', $isEdit ? $tour->title : '') }}"
                        placeholder="e.g., Houston Waterfalls Tour"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    @error('title') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="tagline" class="text-sm font-medium leading-none">Tagline</label>
                    <input type="text" name="tagline" id="tagline"
                        value="{{ old('tagline', $isEdit ? $tour->tagline : '') }}"
                        placeholder="Short one-liner shown on tour cards"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    @error('tagline') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="description" class="text-sm font-medium leading-none">Description</label>
                    <textarea name="description" id="description" rows="4" x-model="meta.description"
                        placeholder="Describe this tour experience..."
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">{{ old('description', $isEdit ? $tour->description : '') }}</textarea>
                    @error('description') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Cover Image --}}
        <div id="tour-cover" class="scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Cover Image</h3>
                <p class="text-sm text-muted-foreground">The large photo at the top of the tour page and on tour cards. A wide (landscape) photo works best.</p>
            </div>
            <div class="p-6 space-y-4">
                @if($isEdit && $tour->cover_image_url)
                    <div id="current-cover-image" class="space-y-2">
                        <p class="text-sm font-medium leading-none">Current Cover Image</p>
                        <div class="relative w-full max-w-sm">
                            <img src="{{ $tour->cover_image_url }}" alt="Current cover"
                                class="w-full h-40 object-cover rounded-md border">
                            <button type="button" title="Delete cover image"
                                onclick="deleteTourCoverImage('{{ route('admin.tours.cover-image.delete', $tour) }}', this)"
                                class="absolute top-2 right-2 inline-flex h-7 w-7 items-center justify-center rounded-full bg-black/60 text-white hover:bg-destructive transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-muted-foreground">Upload a new image below to replace it, or delete it.</p>
                    </div>
                @endif
                <div id="cover-image-dropzone" class="border-2 border-dashed border-input rounded-lg p-8 text-center hover:bg-muted/50 transition-colors cursor-pointer"
                    onclick="document.getElementById('cover_image').click()">
                    <input type="file" id="cover_image" name="cover_image" accept="image/*" class="hidden"
                        onchange="previewCoverImage(this); hasNewCover = this.files.length > 0">
                    <svg class="mx-auto h-12 w-12 text-muted-foreground mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-muted-foreground">Click or drag cover image here to upload</p>
                    <p class="text-xs text-muted-foreground mt-1">JPEG, PNG, WebP up to 50MB</p>
                </div>
                <div id="cover-preview" class="hidden">
                    <img id="cover-preview-img" src="" alt="Preview" class="w-full max-w-sm h-40 object-cover rounded-md border">
                </div>
            </div>
        </div>

        {{-- Tour Details --}}
        <div id="tour-details" class="scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">{{ $section['singular'] }} Details</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">

                <div class="space-y-2">
                    <label for="difficulty_summary" class="text-sm font-medium leading-none">Difficulty</label>
                    <input type="text" name="difficulty_summary" id="difficulty_summary"
                        value="{{ old('difficulty_summary', $isEdit ? $tour->difficulty_summary : '') }}"
                        placeholder="e.g., Easy to Moderate"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    @error('difficulty_summary') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="duration_estimate" class="text-sm font-medium leading-none">Duration</label>
                    <input type="text" name="duration_estimate" id="duration_estimate"
                        value="{{ old('duration_estimate', $isEdit ? $tour->duration_estimate : '') }}"
                        placeholder="e.g., Half day"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    @error('duration_estimate') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="total_driving_km" class="text-sm font-medium leading-none">Total Driving km</label>
                    <input type="number" name="total_driving_km" id="total_driving_km" step="0.1" min="0"
                        value="{{ old('total_driving_km', $isEdit ? $tour->total_driving_km : '') }}"
                        placeholder="Auto-filled by route"
                        class="flex h-10 w-full rounded-md border border-input bg-muted px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    @error('total_driving_km') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2 sm:col-span-3">
                    <label for="video_url" class="text-sm font-medium leading-none">Video Link (YouTube or Vimeo)</label>
                    <input type="url" name="video_url" id="video_url"
                        value="{{ old('video_url', $isEdit ? $tour->video_url : '') }}"
                        placeholder="https://www.youtube.com/watch?v=..."
                        oninput="updateTourVideoPreview(this.value)"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <p class="text-xs text-muted-foreground">Paste a YouTube or Vimeo link to show a video on the tour page</p>
                    @error('video_url') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                    @php $tourVideoThumb = $isEdit ? $tour->video_thumbnail_url : null; @endphp
                    <div id="tour-video-preview" class="{{ $tourVideoThumb ? '' : 'hidden' }} pt-1">
                        <button type="button" onclick="playAdminVideo(document.getElementById('video_url').value)"
                            class="relative block w-full max-w-xs aspect-video rounded-md overflow-hidden bg-gray-900 group">
                            <img id="tour-video-preview-img" src="{{ $tourVideoThumb ?? '' }}" alt="Video preview"
                                class="absolute inset-0 w-full h-full object-cover {{ $tourVideoThumb ? '' : 'hidden' }}">
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

        {{-- Stops Manager --}}
        <div id="tour-stops" class="scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Tour Stops</h3>
                <p class="text-sm text-muted-foreground">
                    Add stops in the order visitors should visit them. Search for a trail, feature or facility below,
                    or <a href="#tour-route" class="font-medium text-foreground underline">pin any spot on the map</a> (e.g. a mural or street corner).
                    Drag <span class="font-mono">⋮⋮</span> to reorder; click a stop to edit its details.
                </p>
            </div>
            <div class="p-6 space-y-4">

                {{-- Trail/Feature/Facility search --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Add a Stop</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="search" data-no-dirty
                            placeholder="Search trails, features, or facilities..."
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                    <div x-show="search.length > 0 && filteredItems.length > 0"
                        class="border rounded-md bg-white shadow-md max-h-56 overflow-y-auto z-10">
                        <template x-for="item in filteredItems" :key="item.item_type + '-' + item.id">
                            <button type="button"
                                @click="addStop(item)"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-muted/50 flex items-center justify-between gap-2 border-b last:border-b-0">
                                <span class="flex flex-col min-w-0">
                                    <span x-text="item.name" class="font-medium truncate"></span>
                                    <span x-show="item.item_type === 'feature'" class="text-xs text-muted-foreground" x-text="'📍 Feature on: ' + (item.trail_name || '')"></span>
                                    <span x-show="item.item_type === 'facility'" class="text-xs text-muted-foreground" x-text="'🏷️ Facility: ' + (item.facility_type_label || '')"></span>
                                </span>
                                <span class="text-xs text-muted-foreground flex-shrink-0"
                                    x-text="item.item_type === 'feature' ? '🏔️' : (item.item_type === 'facility' ? '🏷️' : (item.location_type === 'fishing_lake' ? '🎣' : '🥾'))"></span>
                            </button>
                        </template>
                    </div>
                    <p x-show="search.length > 1 && filteredItems.length === 0" class="text-sm text-muted-foreground">No matching trails, features, or facilities found.</p>
                    <button type="button" @click="addRoamingStop()"
                        class="inline-flex items-center gap-1.5 rounded-md border border-dashed border-input bg-background hover:bg-accent h-8 px-3 text-xs font-medium transition-colors">
                        🚶 Add roaming activity <span class="text-muted-foreground">(no fixed spot, e.g. carollers)</span>
                    </button>
                </div>

                {{-- Stops list --}}
                <div x-show="stops.length > 0" class="space-y-2">
                    <div class="flex items-center justify-between text-xs text-muted-foreground">
                        <span x-text="`${stops.length} stop${stops.length === 1 ? '' : 's'} · ${stopsNeedingDescription} without a description · ${stopsNeedingPhoto} without a photo`"></span>
                        <span class="flex gap-2">
                            <button type="button" @click="setAllExpanded(true)" class="font-medium hover:text-foreground">Expand all</button>
                            <span>·</span>
                            <button type="button" @click="setAllExpanded(false)" class="font-medium hover:text-foreground">Collapse all</button>
                        </span>
                    </div>

                    <template x-for="(stop, index) in stops" :key="stop.uid">
                        <div class="rounded-md border bg-muted/30 transition-shadow" data-stop-row
                            :class="{
                                'ring-2 ring-red-400 border-red-300': stop.has_error,
                                'ring-2 ring-blue-400': dragOverIndex === index && dragIndex !== index,
                                'opacity-50': dragIndex === index,
                            }"
                            :draggable="stop.dragArmed ? 'true' : 'false'"
                            @dragstart="startStopDrag(index, $event)"
                            @dragover.prevent="dragOverIndex = index"
                            @dragleave="if (dragOverIndex === index) { dragOverIndex = null }"
                            @drop.prevent="dropStop(index)"
                            @dragend="endStopDrag(stop)">

                            {{-- Summary (always visible; click to expand) --}}
                            <div class="flex items-center gap-2 p-2.5">
                                <span class="cursor-grab select-none px-1 font-mono text-sm leading-none text-muted-foreground hover:text-foreground active:cursor-grabbing"
                                    title="Drag to reorder"
                                    @mousedown="stop.dragArmed = true" @mouseup="stop.dragArmed = false"
                                    @touchstart.passive="stop.dragArmed = true">⋮⋮</span>

                                {{-- Number badge (shows the stop's icon when it has one) --}}
                                <div class="flex-shrink-0 flex h-7 w-7 items-center justify-center rounded-full bg-black text-white text-xs font-bold overflow-hidden">
                                    <template x-if="stop.icon_image_url">
                                        <img :src="stop.icon_image_url" alt="" class="h-full w-full object-contain bg-white">
                                    </template>
                                    <span x-show="!stop.icon_image_url" x-text="stop.icon || (index + 1)" :class="stop.icon ? 'text-base' : ''"></span>
                                </div>

                                <template x-if="stop.new_image_preview || (stop.image_url && !stop.remove_image) || stop.gallery.length">
                                    <img :src="stop.new_image_preview || (!stop.remove_image && stop.image_url) || stop.gallery[0]?.url" alt=""
                                        class="h-9 w-9 flex-shrink-0 rounded object-cover border">
                                </template>

                                <button type="button" @click="stop.expanded = !stop.expanded"
                                    class="min-w-0 flex-1 text-left" :aria-expanded="stop.expanded">
                                    <span class="flex flex-wrap items-center gap-1.5">
                                        <span class="truncate text-sm font-medium" x-text="stop.stop_label || stop.name"></span>
                                        <span x-show="stop.stop_label && stop.stop_label !== stop.name && !isCustomStop(stop)"
                                            class="truncate text-xs text-muted-foreground" x-text="'· ' + stop.name"></span>
                                        <span x-show="stop.item_type === 'trail'"
                                            class="inline-flex items-center rounded-full bg-green-50 border border-green-200 px-1.5 py-0.5 text-[11px] font-medium text-green-700">Trail</span>
                                        <span x-show="stop.item_type === 'feature'"
                                            class="inline-flex items-center rounded-full bg-blue-50 border border-blue-200 px-1.5 py-0.5 text-[11px] font-medium text-blue-700"
                                            x-text="stop.feature_type || 'Feature'"></span>
                                        <span x-show="stop.item_type === 'facility'"
                                            class="inline-flex items-center rounded-full bg-amber-50 border border-amber-200 px-1.5 py-0.5 text-[11px] font-medium text-amber-700"
                                            x-text="stop.facility_type_label || 'Facility'"></span>
                                        <span x-show="stop.item_type === 'pinned'"
                                            class="inline-flex items-center rounded-full bg-red-50 border border-red-200 px-1.5 py-0.5 text-[11px] font-medium text-red-700">📍 Pinned</span>
                                        <span x-show="stop.item_type === 'roaming'"
                                            class="inline-flex items-center rounded-full bg-purple-50 border border-purple-200 px-1.5 py-0.5 text-[11px] font-medium text-purple-700">🚶 Roaming · no map pin</span>
                                    </span>
                                    <span class="mt-0.5 flex flex-wrap gap-x-3 text-[11px] text-muted-foreground">
                                        <span x-show="isCustomStop(stop) && !stop.stop_label" class="font-medium text-red-600">Needs a name</span>
                                        <span x-text="stop.description ? '📝 Description' : 'No description'" :class="stop.description ? '' : 'text-amber-700'"></span>
                                        <span x-text="photoCount(stop) ? `📷 ${photoCount(stop)} photo${photoCount(stop) === 1 ? '' : 's'}` : 'No photo'" :class="photoCount(stop) ? '' : 'text-amber-700'"></span>
                                        <span x-show="stop.estimated_visit_time" x-text="'🕒 ' + stop.estimated_visit_time"></span>
                                    </span>
                                </button>

                                <div class="flex flex-shrink-0 items-center gap-1">
                                    <button type="button" @click="stop.expanded = !stop.expanded"
                                        class="inline-flex h-7 items-center rounded border border-input bg-background px-2 text-xs font-medium hover:bg-accent"
                                        x-text="stop.expanded ? 'Done' : 'Edit'"></button>
                                    <button type="button" @click="moveUp(index)" :disabled="index === 0" title="Move up"
                                        class="inline-flex items-center justify-center h-7 w-7 rounded border border-input bg-background hover:bg-accent disabled:opacity-30 disabled:cursor-not-allowed text-xs">▲</button>
                                    <button type="button" @click="moveDown(index)" :disabled="index === stops.length - 1" title="Move down"
                                        class="inline-flex items-center justify-center h-7 w-7 rounded border border-input bg-background hover:bg-accent disabled:opacity-30 disabled:cursor-not-allowed text-xs">▼</button>
                                    <button type="button" @click="removeStop(index)" title="Remove stop"
                                        class="inline-flex items-center justify-center h-7 w-7 rounded border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 text-xs">✕</button>
                                </div>
                            </div>

                            {{-- Editor (hidden inputs stay in the DOM while collapsed so they still submit) --}}
                            <div x-show="stop.expanded" class="space-y-3 border-t bg-background/60 p-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <input type="text"
                                        :name="`stops[${index}][stop_label]`"
                                        x-model="stop.stop_label"
                                        :required="isCustomStop(stop)"
                                        :placeholder="isCustomStop(stop) ? 'Name (required, e.g. Wagon Rides)' : 'Label override (optional)'"
                                        class="flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                    <input type="text"
                                        :name="`stops[${index}][estimated_visit_time]`"
                                        x-model="stop.estimated_visit_time"
                                        :placeholder="isCustomStop(stop) ? 'Time (e.g., 12:00 – 4:00 PM)' : 'Visit time (e.g., 1–2 hours)'"
                                        class="flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                    <input type="text"
                                        :name="`stops[${index}][driving_notes]`"
                                        x-model="stop.driving_notes"
                                        :placeholder="isCustomStop(stop) ? 'Where / note (e.g., Bugwood Beans, Main St)' : 'Driving notes (optional)'"
                                        class="flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring sm:col-span-2">
                                    {{-- Hidden inputs --}}
                                    <input type="hidden" :name="`stops[${index}][trail_id]`" :value="stop.trail_id || ''">
                                    <input type="hidden" :name="`stops[${index}][feature_id]`" :value="stop.feature_id || ''">
                                    <input type="hidden" :name="`stops[${index}][facility_id]`" :value="stop.facility_id || ''">
                                    <input type="hidden" :name="`stops[${index}][latitude]`" :value="stop.item_type === 'pinned' ? stop.latitude : ''">
                                    <input type="hidden" :name="`stops[${index}][longitude]`" :value="stop.item_type === 'pinned' ? stop.longitude : ''">
                                    <input type="hidden" :name="`stops[${index}][is_roaming]`" :value="stop.item_type === 'roaming' ? 1 : 0">
                                    <input type="hidden" :name="`stops[${index}][icon]`" :value="stop.icon || ''">
                                    <input type="hidden" :name="`stops[${index}][icon_image]`" :value="stop.icon_image || ''">
                                    <input type="hidden" :name="`stops[${index}][existing_image]`" :value="stop.existing_image || ''">
                                    <input type="hidden" :name="`stops[${index}][remove_image]`" :value="stop.remove_image ? 1 : 0">
                                    <template x-for="photo in stop.gallery" :key="photo.path">
                                        <input type="hidden" :name="`stops[${index}][existing_gallery][]`" :value="photo.path">
                                    </template>
                                    <input type="file" multiple accept="image/*" class="hidden" data-gallery-upload
                                        :name="`stops[${index}][gallery][]`">
                                </div>


                                <div class="space-y-3">
                                    {{-- Icon --}}
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

                                    {{-- Description (light Markdown) --}}
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-1">
                                            <button type="button" @click="formatStopDescription(stop, $el, 'bold')" title="Bold"
                                                class="h-6 min-w-6 rounded border border-input bg-background px-1.5 text-xs font-bold hover:bg-muted">B</button>
                                            <button type="button" @click="formatStopDescription(stop, $el, 'italic')" title="Italic"
                                                class="h-6 min-w-6 rounded border border-input bg-background px-1.5 text-xs italic hover:bg-muted">I</button>
                                            <button type="button" @click="formatStopDescription(stop, $el, 'link')" title="Link"
                                                class="h-6 rounded border border-input bg-background px-1.5 text-xs hover:bg-muted">🔗 Link</button>
                                            <button type="button" @click="formatStopDescription(stop, $el, 'list')" title="Bulleted list"
                                                class="h-6 rounded border border-input bg-background px-1.5 text-xs hover:bg-muted">• List</button>
                                            <span class="ml-auto text-[11px] text-muted-foreground tabular-nums"
                                                x-text="`${(stop.description || '').length.toLocaleString()} / 10,000`"></span>
                                        </div>
                                        <textarea :name="`stops[${index}][description]`" x-model="stop.description" rows="6" maxlength="10000"
                                            data-stop-description
                                            placeholder="What visitors should know about this stop. Leave a blank line between paragraphs."
                                            class="flex w-full rounded-md border border-input bg-background px-2 py-1.5 text-xs leading-relaxed placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"></textarea>
                                        <p class="text-[11px] text-muted-foreground">
                                            Formatting: <code>**bold**</code>, <code>*italic*</code>, <code>[link text](https://…)</code>, lines starting with <code>- </code> for a list.
                                        </p>
                                    </div>

                                    {{-- Photo --}}
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

                                    {{-- Extra photos --}}
                                    <div class="space-y-1.5">
                                        <p class="text-xs font-medium">
                                            More photos
                                            <span class="font-normal text-muted-foreground"
                                                x-text="`(${stop.gallery.length + stop.new_gallery.length} / {{ \App\Models\TourStop::MAX_GALLERY_PHOTOS }})`"></span>
                                        </p>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <template x-for="(photo, photoIndex) in stop.gallery" :key="photo.path">
                                                <div class="group relative h-14 w-14">
                                                    <img :src="photo.url" alt="" class="h-full w-full rounded-md border object-cover">
                                                    <button type="button" @click="stop.gallery.splice(photoIndex, 1)" title="Remove photo"
                                                        class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] text-white shadow">✕</button>
                                                </div>
                                            </template>
                                            <template x-for="(photo, photoIndex) in stop.new_gallery" :key="photo.preview">
                                                <div class="relative h-14 w-14">
                                                    <img :src="photo.preview" alt="" class="h-full w-full rounded-md border border-dashed border-green-500 object-cover">
                                                    <button type="button" @click="removeNewGalleryPhoto(stop, photoIndex, $el)" title="Remove photo"
                                                        class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] text-white shadow">✕</button>
                                                </div>
                                            </template>
                                            <label x-show="stop.gallery.length + stop.new_gallery.length < {{ \App\Models\TourStop::MAX_GALLERY_PHOTOS }}"
                                                class="inline-flex h-14 cursor-pointer items-center gap-1.5 rounded-md border border-dashed border-input bg-background px-3 text-xs text-muted-foreground hover:bg-muted">
                                                ＋ Add photos
                                                <input type="file" multiple accept="image/*" class="hidden" @change="addGalleryPhotos(stop, $event)">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <p x-show="stops.length === 0" class="text-sm text-muted-foreground text-center py-4">
                    No stops added yet. Search for trails or features above to add them.
                </p>

            </div>
        </div>

        {{-- Route & Map editor --}}
        <div id="tour-route" class="scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Route &amp; Map</h3>
                <p class="text-sm text-muted-foreground">
                    Use <strong>📍 Pin stop on map</strong> to add a stop anywhere by clicking the map.
                </p>
                <div class="rounded-md border border-green-200 bg-green-50 px-3 py-2 text-xs text-green-800">
                    <strong>Drawing a route is optional.</strong> Leave it empty and the public page automatically draws a street route through your stops
                    (walking when the stops are close together, driving otherwise). Only draw one if you need the line to follow a specific path.
                </div>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex flex-wrap gap-2">
                    <button type="button" id="route-draw-btn" onclick="tourRouteEditor.toggleMode('draw')"
                        class="route-tool-btn inline-flex items-center gap-1.5 rounded-md border border-input bg-background hover:bg-accent h-9 px-3 text-sm font-medium transition-colors">
                        ✏️ Draw route
                    </button>
                    <button type="button" id="route-pin-btn" onclick="tourRouteEditor.toggleMode('pin')"
                        class="route-tool-btn inline-flex items-center gap-1.5 rounded-md border border-input bg-background hover:bg-accent h-9 px-3 text-sm font-medium transition-colors">
                        📍 Pin stop on map
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
                    <button type="button" id="compute-route-btn" onclick="computeTourRoute()" x-show="stops.length >= 2"
                        class="inline-flex items-center gap-1.5 rounded-md border border-input bg-background hover:bg-accent h-9 px-3 text-sm font-medium transition-colors">
                        🚗 Compute driving route
                    </button>
                </div>

                <div id="route-mode-hint" class="hidden rounded-md bg-blue-50 border border-blue-200 px-3 py-2 text-xs text-blue-800"></div>
                <div id="route-status" class="hidden text-sm text-muted-foreground"></div>

                <div id="route-map" class="w-full h-[460px] rounded-md border"></div>

                <p class="text-xs text-muted-foreground">
                    <span id="route-point-count">0</span> route points · <span id="route-km">0.0</span> km.
                    Tip: zoom in close and click at each corner. Pinned stops can be dragged to fine-tune them.
                </p>
                <textarea name="driving_route_coordinates" id="driving_route_coordinates" class="hidden">{{ $isEdit && $tour->driving_route_coordinates ? json_encode($tour->driving_route_coordinates) : '' }}</textarea>
            </div>
        </div>

    </div>

    <!-- Right: Settings sidebar -->
    <div class="space-y-6 lg:sticky lg:top-40 lg:self-start">

        {{-- Ready-to-publish checklist --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-sm font-semibold">Ready to publish?</h3>
                <span class="text-xs font-medium tabular-nums text-muted-foreground"
                    x-text="`${checklist.filter((item) => item.done).length} / ${checklist.length}`"></span>
            </div>
            <ul class="p-4 space-y-2 text-sm">
                <template x-for="item in checklist" :key="item.label">
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5 flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full text-[10px] font-bold"
                            :class="item.done ? 'bg-green-600 text-white' : 'border border-gray-300 text-transparent'">✓</span>
                        <a :href="item.anchor" class="hover:underline" :class="item.done ? 'text-muted-foreground' : 'text-foreground'">
                            <span x-text="item.label"></span>
                            <span x-show="item.hint && !item.done" x-text="item.hint" class="block text-xs text-amber-700"></span>
                        </a>
                    </li>
                </template>
            </ul>
            <p class="px-4 pb-4 text-xs text-muted-foreground">Only the title and type are required — the rest makes the tour page look its best.</p>
        </div>

        {{-- Settings --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Settings</h3>
            </div>
            <div class="p-6 space-y-4">
                @include('admin._town-select', ['selected' => $isEdit ? $tour->town_id : null])

                <div class="space-y-2">
                    <label for="sort_order" class="text-sm font-medium leading-none">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" min="0"
                        value="{{ old('sort_order', $isEdit ? $tour->sort_order : 0) }}"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <p class="text-xs text-muted-foreground">Lower numbers appear first</p>
                </div>

                <div class="pt-2 border-t space-y-3">
                    <div class="flex items-center space-x-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', $isEdit ? $tour->is_active : true) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
                        <label for="is_active" class="text-sm font-medium leading-none">Active (visible on site)</label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1"
                            {{ old('is_featured', $isEdit ? $tour->is_featured : false) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
                        <label for="is_featured" class="text-sm font-medium leading-none">Featured</label>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
