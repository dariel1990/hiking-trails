@php
    $isEdit = isset($town) && $town->exists;
    $indexMode = old('is_indexable', $town->is_indexable === null ? 'auto' : ($town->is_indexable ? 'index' : 'noindex'));
@endphp

@if($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <p class="font-semibold mb-2">Please fix the following:</p>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-6 lg:grid-cols-3">

    {{-- Main column --}}
    <div class="lg:col-span-2 space-y-6">

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6 space-y-4">
            <h3 class="text-lg font-semibold">Basics</h3>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium mb-1.5">Town name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" required
                           value="{{ old('name', $town->name) }}"
                           class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="slug" class="block text-sm font-medium mb-1.5">URL slug</label>
                    <input type="text" name="slug" id="slug"
                           value="{{ old('slug', $town->slug) }}"
                           placeholder="houston-bc"
                           class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-muted-foreground">
                        Page will live at <code>/hiking-trails/{{ old('slug', $town->slug) ?: 'your-slug' }}</code>. Leave blank to generate from the name.
                    </p>
                </div>
                <div>
                    <label for="province" class="block text-sm font-medium mb-1.5">Province <span class="text-red-500">*</span></label>
                    <input type="text" name="province" id="province" required
                           value="{{ old('province', $town->province ?? 'British Columbia') }}"
                           class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="province_code" class="block text-sm font-medium mb-1.5">Province code <span class="text-red-500">*</span></label>
                    <input type="text" name="province_code" id="province_code" required maxlength="8"
                           value="{{ old('province_code', $town->province_code ?? 'BC') }}"
                           class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label for="tagline" class="block text-sm font-medium mb-1.5">Tagline</label>
                <input type="text" name="tagline" id="tagline" maxlength="255"
                       value="{{ old('tagline', $town->tagline) }}"
                       placeholder="Waterfall tours, forest trails and world-class steelhead water."
                       class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-muted-foreground">Shown under the page heading and on the town cards.</p>
            </div>

            <div>
                <label for="intro" class="block text-sm font-medium mb-1.5">Intro copy</label>
                <textarea name="intro" id="intro" rows="8"
                          class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm">{{ old('intro', $town->intro) }}</textarea>
                <p class="mt-1 text-xs text-muted-foreground">
                    The main body text that helps this page rank. Write for a visitor searching
                    &ldquo;hiking trails {{ $town->name ?: 'town' }} BC&rdquo;. Line breaks are preserved.
                </p>
            </div>

            <div>
                <label for="website_url" class="block text-sm font-medium mb-1.5">Municipality website</label>
                <input type="url" name="website_url" id="website_url"
                       value="{{ old('website_url', $town->website_url) }}"
                       placeholder="https://www.houston.ca"
                       class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
            </div>
        </div>

        {{-- Location --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6 space-y-4">
            <div>
                <h3 class="text-lg font-semibold">Town centre &amp; catchment</h3>
                <p class="text-sm text-muted-foreground mt-1">
                    Click the map to set the centre. Trails, businesses and facilities within the radius are
                    claimed by this town when you run <code>php artisan towns:assign</code>.
                </p>
            </div>

            <div id="town-picker" class="w-full h-80 rounded-lg border bg-muted"></div>

            <div class="grid gap-4 sm:grid-cols-4">
                <div>
                    <label for="latitude" class="block text-sm font-medium mb-1.5">Latitude <span class="text-red-500">*</span></label>
                    <input type="number" step="any" name="latitude" id="latitude" required
                           value="{{ old('latitude', $town->latitude) }}"
                           class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="longitude" class="block text-sm font-medium mb-1.5">Longitude <span class="text-red-500">*</span></label>
                    <input type="number" step="any" name="longitude" id="longitude" required
                           value="{{ old('longitude', $town->longitude) }}"
                           class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="radius_km" class="block text-sm font-medium mb-1.5">Radius (km) <span class="text-red-500">*</span></label>
                    <input type="number" name="radius_km" id="radius_km" required min="1" max="500"
                           value="{{ old('radius_km', $town->radius_km ?? 40) }}"
                           class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="map_zoom" class="block text-sm font-medium mb-1.5">Map zoom <span class="text-red-500">*</span></label>
                    <input type="number" name="map_zoom" id="map_zoom" required min="1" max="20"
                           value="{{ old('map_zoom', $town->map_zoom ?? 11) }}"
                           class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        {{-- SEO --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6 space-y-4">
            <h3 class="text-lg font-semibold">Search engine settings</h3>

            <div>
                <label for="seo_title" class="block text-sm font-medium mb-1.5">Page title</label>
                <input type="text" name="seo_title" id="seo_title" maxlength="255"
                       value="{{ old('seo_title', $town->seo_title) }}"
                       placeholder="Hiking Trails in {{ $town->name ?: 'Town' }}, BC"
                       class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-muted-foreground">Leave blank to generate automatically. Aim for under 60 characters.</p>
            </div>

            <div>
                <label for="meta_description" class="block text-sm font-medium mb-1.5">Meta description</label>
                <textarea name="meta_description" id="meta_description" rows="3" maxlength="500"
                          class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm">{{ old('meta_description', $town->meta_description) }}</textarea>
                <p class="mt-1 text-xs text-muted-foreground">Leave blank to generate from the intro. Aim for 150&ndash;160 characters.</p>
            </div>

            <div>
                <label for="is_indexable" class="block text-sm font-medium mb-1.5">Search engine indexing</label>
                <select name="is_indexable" id="is_indexable"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <option value="auto" @selected($indexMode === 'auto')>Automatic &mdash; index once the town has trails</option>
                    <option value="index" @selected($indexMode === 'index')>Always index</option>
                    <option value="noindex" @selected($indexMode === 'noindex')>Never index</option>
                </select>
                <p class="mt-1 text-xs text-muted-foreground">
                    A town with no trails is kept out of the sitemap on Automatic, so an empty page is never
                    submitted to Google as thin content.
                </p>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6 space-y-4">
            <h3 class="text-lg font-semibold">Publishing</h3>

            <label class="flex items-center gap-2 text-sm font-medium">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $town->is_active ?? true))
                       class="h-4 w-4 rounded border-input">
                Published
            </label>

            <div>
                <label for="sort_order" class="block text-sm font-medium mb-1.5">Sort order</label>
                <input type="number" name="sort_order" id="sort_order" min="0"
                       value="{{ old('sort_order', $town->sort_order ?? 0) }}"
                       class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
            </div>

            @if($isEdit)
                <a href="{{ route('towns.show', $town) }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:underline">
                    View live page
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            @endif
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6 space-y-4">
            <h3 class="text-lg font-semibold">Hero image</h3>

            @if($isEdit && $town->hero_image)
                <img src="{{ asset('storage/'.$town->hero_image) }}" alt="" class="w-full h-36 object-cover rounded-md border">
                <button type="button"
                        onclick="document.getElementById('delete-hero-form').submit()"
                        class="text-sm font-medium text-red-600 hover:underline">
                    Remove image
                </button>
            @endif

            <input type="file" name="hero_image" accept="image/*"
                   class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-black file:px-3 file:py-2 file:text-sm file:font-medium file:text-white">
            <p class="text-xs text-muted-foreground">Wide landscape shot, ideally 1600&times;900 or larger.</p>
        </div>

        @if($isEdit)
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6 space-y-2">
                <h3 class="text-lg font-semibold">Assigned content</h3>
                <dl class="text-sm space-y-1">
                    <div class="flex justify-between"><dt class="text-muted-foreground">Trails &amp; lakes</dt><dd class="font-medium">{{ $town->trails()->count() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Businesses</dt><dd class="font-medium">{{ $town->businesses()->count() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Facilities</dt><dd class="font-medium">{{ $town->facilities()->count() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted-foreground">Tours</dt><dd class="font-medium">{{ $town->tours()->count() }}</dd></div>
                </dl>
                <p class="text-xs text-muted-foreground pt-2">
                    Assign content by editing each record, or run <code>php artisan towns:assign</code> to
                    fill in anything still unassigned.
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>
<script>
(function () {
    const token = @json(config('services.mapbox.access_token'));
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const radiusInput = document.getElementById('radius_km');

    if (!token || !window.mapboxgl) {
        return;
    }

    mapboxgl.accessToken = token;

    const startLat = parseFloat(latInput.value) || {{ (float) setting('map_default_lat') }};
    const startLng = parseFloat(lngInput.value) || {{ (float) setting('map_default_lng') }};

    const map = new mapboxgl.Map({
        container: 'town-picker',
        style: 'mapbox://styles/mapbox/outdoors-v12',
        center: [startLng, startLat],
        zoom: 9
    });

    map.addControl(new mapboxgl.NavigationControl(), 'top-right');

    const marker = new mapboxgl.Marker({ draggable: true, color: '#E87B35' })
        .setLngLat([startLng, startLat])
        .addTo(map);

    // Draws the catchment as a 64-sided polygon so the admin can see exactly
    // which trailheads the radius will claim.
    function radiusCircle() {
        const center = marker.getLngLat();
        const km = parseFloat(radiusInput.value) || 40;
        const points = 64;
        const coords = [];

        for (let i = 0; i <= points; i++) {
            const angle = (i / points) * 2 * Math.PI;
            const dLat = (km / 111.32) * Math.sin(angle);
            const dLng = (km / (111.32 * Math.cos(center.lat * Math.PI / 180))) * Math.cos(angle);
            coords.push([center.lng + dLng, center.lat + dLat]);
        }

        return { type: 'Feature', geometry: { type: 'Polygon', coordinates: [coords] } };
    }

    function redraw() {
        const source = map.getSource('town-radius');
        if (source) {
            source.setData(radiusCircle());
        }
    }

    function syncInputs() {
        const { lat, lng } = marker.getLngLat();
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
        redraw();
    }

    map.on('load', function () {
        map.addSource('town-radius', { type: 'geojson', data: radiusCircle() });
        map.addLayer({
            id: 'town-radius-fill',
            type: 'fill',
            source: 'town-radius',
            paint: { 'fill-color': '#2C5F5D', 'fill-opacity': 0.12 }
        });
        map.addLayer({
            id: 'town-radius-line',
            type: 'line',
            source: 'town-radius',
            paint: { 'line-color': '#2C5F5D', 'line-width': 2, 'line-dasharray': [2, 2] }
        });
    });

    map.on('click', function (event) {
        marker.setLngLat(event.lngLat);
        syncInputs();
    });

    marker.on('dragend', syncInputs);
    radiusInput.addEventListener('input', redraw);

    [latInput, lngInput].forEach(function (input) {
        input.addEventListener('change', function () {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                marker.setLngLat([lng, lat]);
                map.flyTo({ center: [lng, lat] });
                redraw();
            }
        });
    });
})();
</script>
@endpush
