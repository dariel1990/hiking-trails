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

$existingStopsJson = $isEdit
    ? $tour->stops->map(fn ($s) => [
        'trail_id' => $s->trail_id,
        'feature_id' => $s->trail_feature_id,
        'item_type' => $s->trail_feature_id ? 'feature' : 'trail',
        'feature_type' => $s->feature?->feature_type ?? null,
        'name' => $s->feature?->name ?? ($s->trail?->name ?? 'Unknown'),
        'start_coordinates' => $s->feature?->coordinates ?? ($s->trail?->start_coordinates ?? null),
        'stop_label' => $s->stop_label ?? '',
        'estimated_visit_time' => $s->estimated_visit_time ?? '',
        'driving_notes' => $s->driving_notes ?? '',
    ])->values()->toJson()
    : '[]';
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6"
     x-data="tourForm({{ $availableTrailsJson }}, {{ $availableFeaturesJson }}, {{ $existingStopsJson }})">

    <!-- Left: Main fields -->
    <div class="lg:col-span-2 space-y-6">

        {{-- Basic Info --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Basic Information</h3>
            </div>
            <div class="p-6 space-y-4">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2 space-y-2">
                        <label for="tour_type" class="text-sm font-medium leading-none">
                            Tour Type <span class="text-red-500">*</span>
                        </label>
                        <select name="tour_type" id="tour_type" required
                            onchange="syncTourTypeIcon(this.value)"
                            class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                            <option value="">Select tour type</option>
                            @foreach(App\Models\Tour::getTourTypes() as $type => $label)
                                <option value="{{ $type }}" {{ old('tour_type', $isEdit ? $tour->tour_type : '') === $type ? 'selected' : '' }}>{{ $label }}</option>
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
                            <label for="tour-icon-image-input" class="cursor-pointer inline-flex items-center gap-1.5 rounded-md border border-dashed border-input px-3 py-1.5 text-xs text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Upload new icon
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
                        Tour Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" required
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
                    <textarea name="description" id="description" rows="4"
                        placeholder="Describe this tour experience..."
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">{{ old('description', $isEdit ? $tour->description : '') }}</textarea>
                    @error('description') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Tour Details --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Tour Details</h3>
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
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Tour Stops</h3>
                <p class="text-sm text-muted-foreground">Add trails or trail features as stops in the order visitors should visit them</p>
            </div>
            <div class="p-6 space-y-4">

                {{-- Trail/Feature search --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Add a Stop</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="search"
                            placeholder="Search trails or trail features..."
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
                                </span>
                                <span class="text-xs text-muted-foreground flex-shrink-0"
                                    x-text="item.item_type === 'feature' ? '🏔️' : (item.location_type === 'fishing_lake' ? '🎣' : '🥾')"></span>
                            </button>
                        </template>
                    </div>
                    <p x-show="search.length > 1 && filteredItems.length === 0" class="text-sm text-muted-foreground">No matching trails or features found.</p>
                </div>

                {{-- Stops list --}}
                <div x-show="stops.length > 0" class="space-y-2">
                    <template x-for="(stop, index) in stops" :key="index">
                        <div class="flex gap-3 items-start p-3 rounded-md border bg-muted/30">
                            {{-- Number badge --}}
                            <div class="flex-shrink-0 flex h-7 w-7 items-center justify-center rounded-full bg-black text-white text-xs font-bold mt-0.5"
                                x-text="index + 1"></div>

                            {{-- Stop details --}}
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-sm" x-text="stop.name"></span>
                                    <span x-show="stop.item_type === 'feature'"
                                        class="inline-flex items-center rounded-full bg-blue-50 border border-blue-200 px-1.5 py-0.5 text-xs font-medium text-blue-700"
                                        x-text="stop.feature_type || 'Feature'"></span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <input type="text"
                                        :name="`stops[${index}][stop_label]`"
                                        x-model="stop.stop_label"
                                        placeholder="Label override (optional)"
                                        class="flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                    <input type="text"
                                        :name="`stops[${index}][estimated_visit_time]`"
                                        x-model="stop.estimated_visit_time"
                                        placeholder="Visit time (e.g., 1–2 hours)"
                                        class="flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                    <input type="text"
                                        :name="`stops[${index}][driving_notes]`"
                                        x-model="stop.driving_notes"
                                        placeholder="Driving notes (optional)"
                                        class="flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring sm:col-span-2">
                                    {{-- Hidden inputs --}}
                                    <input type="hidden" :name="`stops[${index}][trail_id]`" :value="stop.trail_id">
                                    <input type="hidden" :name="`stops[${index}][feature_id]`" :value="stop.feature_id || ''">
                                </div>
                            </div>

                            {{-- Order + remove buttons --}}
                            <div class="flex flex-col gap-1 flex-shrink-0">
                                <button type="button" @click="moveUp(index)" :disabled="index === 0"
                                    class="inline-flex items-center justify-center h-6 w-6 rounded border border-input bg-background hover:bg-accent disabled:opacity-30 disabled:cursor-not-allowed text-xs">
                                    ▲
                                </button>
                                <button type="button" @click="moveDown(index)" :disabled="index === stops.length - 1"
                                    class="inline-flex items-center justify-center h-6 w-6 rounded border border-input bg-background hover:bg-accent disabled:opacity-30 disabled:cursor-not-allowed text-xs">
                                    ▼
                                </button>
                                <button type="button" @click="removeStop(index)"
                                    class="inline-flex items-center justify-center h-6 w-6 rounded border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 text-xs">
                                    ✕
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <p x-show="stops.length === 0" class="text-sm text-muted-foreground text-center py-4">
                    No stops added yet. Search for trails or features above to add them.
                </p>

            </div>
        </div>

        {{-- Cover Image --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Cover Image</h3>
                <p class="text-sm text-muted-foreground">Upload a hero photo for this tour</p>
            </div>
            <div class="p-6 space-y-4">
                @if($isEdit && $tour->cover_image_url)
                    <div class="space-y-2">
                        <p class="text-sm font-medium leading-none">Current Cover Image</p>
                        <img src="{{ $tour->cover_image_url }}" alt="Current cover"
                            class="w-full max-w-sm h-40 object-cover rounded-md border">
                        <p class="text-xs text-muted-foreground">Upload a new image below to replace it.</p>
                    </div>
                @endif
                <div class="border-2 border-dashed border-input rounded-lg p-8 text-center hover:bg-muted/50 transition-colors cursor-pointer"
                    onclick="document.getElementById('cover_image').click()">
                    <input type="file" id="cover_image" name="cover_image" accept="image/*" class="hidden"
                        onchange="previewCoverImage(this)">
                    <svg class="mx-auto h-12 w-12 text-muted-foreground mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-muted-foreground">Click to upload cover image</p>
                    <p class="text-xs text-muted-foreground mt-1">JPEG, PNG, WebP up to 50MB</p>
                </div>
                <div id="cover-preview" class="hidden">
                    <img id="cover-preview-img" src="" alt="Preview" class="w-full max-w-sm h-40 object-cover rounded-md border">
                </div>
            </div>
        </div>

        {{-- Driving Route (edit + stops present) --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm" x-show="stops.length >= 2">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Driving Route</h3>
                <p class="text-sm text-muted-foreground">Compute the road route connecting all stops</p>
            </div>
            <div class="p-6 space-y-4">
                <button type="button" id="compute-route-btn"
                    onclick="computeTourRoute()"
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Compute Driving Route
                </button>
                <div id="route-status" class="hidden text-sm text-muted-foreground"></div>
                <div id="route-map" class="w-full h-[300px] rounded-md border hidden"></div>
                <textarea name="driving_route_coordinates" id="driving_route_coordinates" class="hidden">{{ $isEdit && $tour->driving_route_coordinates ? json_encode($tour->driving_route_coordinates) : '' }}</textarea>
            </div>
        </div>

    </div>

    <!-- Right: Settings sidebar -->
    <div class="space-y-6">

        {{-- Save --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">{{ $isEdit ? 'Save Changes' : 'Publish' }}</h3>
            </div>
            <div class="p-6 flex flex-col gap-2">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors w-full">
                    {{ $isEdit ? 'Update Tour' : 'Create Tour' }}
                </button>
                <a href="{{ route('admin.tours.index') }}"
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors w-full">
                    Cancel
                </a>
            </div>
        </div>

        {{-- Settings --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Settings</h3>
            </div>
            <div class="p-6 space-y-4">
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

        {{-- Stops count summary --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-4">
            <p class="text-sm text-muted-foreground">
                <span class="font-semibold text-foreground" x-text="stops.length"></span> stop<span x-show="stops.length !== 1">s</span> added
            </p>
        </div>

    </div>
</div>
