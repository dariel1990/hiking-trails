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

$existingStopsJson = $isEdit
    ? $tour->stops->map(fn ($s) => [
        'trail_id' => $s->trail_id,
        'feature_id' => $s->trail_feature_id,
        'facility_id' => $s->facility_id,
        'latitude' => $s->isPinned() ? $s->latitude : null,
        'longitude' => $s->isPinned() ? $s->longitude : null,
        'item_type' => $s->is_roaming ? 'roaming' : ($s->isPinned() ? 'pinned' : ($s->facility_id ? 'facility' : ($s->trail_feature_id ? 'feature' : 'trail'))),
        'feature_type' => $s->feature?->feature_type ?? null,
        'facility_type_label' => $s->facility ? (\App\Models\Facility::getFacilityTypes()[$s->facility->facility_type] ?? $s->facility->facility_type) : null,
        'name' => $s->is_roaming
            ? 'Roaming activity'
            : ($s->isPinned()
                ? 'Pinned on map'
                : ($s->feature?->name ?? ($s->facility?->name ?? ($s->trail?->name ?? 'Unknown')))),
        'start_coordinates' => $s->is_roaming
            ? null
            : ($s->isPinned()
                ? [$s->latitude, $s->longitude]
                : ($s->feature?->coordinates
                    ?? ($s->facility ? [(float) $s->facility->latitude, (float) $s->facility->longitude] : null)
                    ?? ($s->trail?->start_coordinates ?? null))),
        'stop_label' => $s->stop_label ?? '',
        'estimated_visit_time' => $s->estimated_visit_time ?? '',
        'driving_notes' => $s->driving_notes ?? '',
        'icon' => $s->icon ?? '',
        'icon_image' => $s->icon_image ?? '',
        'icon_image_url' => $s->icon_image_url,
        'description' => $s->description ?? '',
        'existing_image' => $s->image ?? '',
        'image_url' => $s->image_url,
    ])->values()->toJson()
    : '[]';
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" id="tour-form-root"
     x-data="tourForm({{ $availableTrailsJson }}, {{ $availableFeaturesJson }}, {{ $availableFacilitiesJson }}, {{ $existingStopsJson }})">

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
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Tour Stops</h3>
                <p class="text-sm text-muted-foreground">Add trails, trail features, or facilities as stops in the order visitors should visit them — or pin any spot (e.g. a street corner or venue) with <strong>Pin stop on map</strong> in the Route &amp; Map section below.</p>
            </div>
            <div class="p-6 space-y-4">

                {{-- Trail/Feature/Facility search --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Add a Stop</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="search"
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
                    <template x-for="(stop, index) in stops" :key="stop.uid">
                        <div class="flex gap-3 items-start p-3 rounded-md border bg-muted/30" data-stop-row>
                            {{-- Number badge (shows the stop's icon when it has one) --}}
                            <div class="flex-shrink-0 flex h-7 w-7 items-center justify-center rounded-full bg-black text-white text-xs font-bold mt-0.5 overflow-hidden">
                                <template x-if="stop.icon_image_url">
                                    <img :src="stop.icon_image_url" alt="" class="h-full w-full object-contain bg-white">
                                </template>
                                <span x-show="!stop.icon_image_url" x-text="stop.icon || (index + 1)" :class="stop.icon ? 'text-base' : ''"></span>
                            </div>

                            {{-- Stop details --}}
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-sm" x-text="stop.name"></span>
                                    <span x-show="stop.item_type === 'feature'"
                                        class="inline-flex items-center rounded-full bg-blue-50 border border-blue-200 px-1.5 py-0.5 text-xs font-medium text-blue-700"
                                        x-text="stop.feature_type || 'Feature'"></span>
                                    <span x-show="stop.item_type === 'facility'"
                                        class="inline-flex items-center rounded-full bg-amber-50 border border-amber-200 px-1.5 py-0.5 text-xs font-medium text-amber-700"
                                        x-text="stop.facility_type_label || 'Facility'"></span>
                                    <span x-show="stop.item_type === 'pinned'"
                                        class="inline-flex items-center rounded-full bg-red-50 border border-red-200 px-1.5 py-0.5 text-xs font-medium text-red-700"
                                        x-text="'📍 ' + Number(stop.latitude).toFixed(5) + ', ' + Number(stop.longitude).toFixed(5)"></span>
                                    <span x-show="stop.item_type === 'roaming'"
                                        class="inline-flex items-center rounded-full bg-purple-50 border border-purple-200 px-1.5 py-0.5 text-xs font-medium text-purple-700">
                                        🚶 Roaming · listed only, no map pin
                                    </span>
                                </div>
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
                                </div>

                                <button type="button" x-show="!isCustomStop(stop) && !stop.showDetails" @click="stop.showDetails = true"
                                    class="text-xs font-medium text-muted-foreground hover:text-foreground">
                                    ＋ Icon, description &amp; photo
                                </button>

                                {{-- Icon, description & photo (always shown for pinned / roaming activities) --}}
                                <div x-show="isCustomStop(stop) || stop.showDetails" class="space-y-2 rounded-md border bg-background p-2.5">
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

                                    {{-- Description --}}
                                    <textarea :name="`stops[${index}][description]`" x-model="stop.description" rows="2" maxlength="2000"
                                        placeholder="What's happening (e.g., Free horse-drawn wagon rides around downtown, starting at Bugwood Beans)"
                                        class="flex w-full rounded-md border border-input bg-background px-2 py-1.5 text-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"></textarea>

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
                        onchange="previewCoverImage(this)">
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

        {{-- Route & Map editor --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Route &amp; Map</h3>
                <p class="text-sm text-muted-foreground">
                    Draw the route yourself by clicking along the streets in order, or compute a driving route through the stops.
                    The route line shows arrows in the direction you draw it.
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
                        🚗 Compute route through stops
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
    <div class="space-y-6">

        {{-- Save --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">{{ $isEdit ? 'Save Changes' : 'Publish' }}</h3>
            </div>
            <div class="p-6 flex flex-col gap-2">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors w-full">
                    {{ ($isEdit ? 'Update ' : 'Create ') . $section['singular'] }}
                </button>
                <a href="{{ route($section['routePrefix'] . '.index') }}"
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

        {{-- Stops count summary --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-4">
            <p class="text-sm text-muted-foreground">
                <span class="font-semibold text-foreground" x-text="stops.length"></span> stop<span x-show="stops.length !== 1">s</span> added
            </p>
        </div>

    </div>
</div>
