<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>

<script>
function tourForm(availableTrails, existingStops) {
    return {
        availableTrails: availableTrails,
        stops: existingStops.map(s => ({
            trail_id: s.trail_id,
            name: s.name,
            start_coordinates: s.start_coordinates,
            stop_label: s.stop_label || '',
            estimated_visit_time: s.estimated_visit_time || '',
            driving_notes: s.driving_notes || '',
        })),
        search: '',

        get filteredTrails() {
            if (this.search.length < 2) { return []; }
            const q = this.search.toLowerCase();
            const existingIds = this.stops.map(s => s.trail_id);
            return this.availableTrails.filter(t =>
                t.name.toLowerCase().includes(q) && !existingIds.includes(t.id)
            ).slice(0, 8);
        },

        addStop(trail) {
            this.stops.push({
                trail_id: trail.id,
                name: trail.name,
                start_coordinates: trail.start_coordinates,
                stop_label: '',
                estimated_visit_time: '',
                driving_notes: '',
            });
            this.search = '';
        },

        removeStop(index) {
            this.stops.splice(index, 1);
        },

        moveUp(index) {
            if (index === 0) { return; }
            const temp = this.stops[index];
            this.stops[index] = this.stops[index - 1];
            this.stops[index - 1] = temp;
            this.stops = [...this.stops];
        },

        moveDown(index) {
            if (index === this.stops.length - 1) { return; }
            const temp = this.stops[index];
            this.stops[index] = this.stops[index + 1];
            this.stops[index + 1] = temp;
            this.stops = [...this.stops];
        },
    };
}

function previewCoverImage(input) {
    if (!input.files || !input.files[0]) { return; }
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('cover-preview').classList.remove('hidden');
        document.getElementById('cover-preview-img').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

// Compute multi-stop driving route via /api/tour-route
async function computeTourRoute() {
    const alpineEl = document.querySelector('[x-data]');
    if (!alpineEl || !window.Alpine) {
        alert('Page not fully loaded. Please try again.');
        return;
    }
    const data = window.Alpine.$data(alpineEl);
    const stops = data.stops;

    if (stops.length < 2) {
        alert('Add at least 2 stops before computing a route.');
        return;
    }

    // Build waypoints from stop start_coordinates [lat, lng]
    const waypoints = stops
        .filter(s => s.start_coordinates && s.start_coordinates.length >= 2)
        .map(s => [s.start_coordinates[0], s.start_coordinates[1]]);

    if (waypoints.length < 2) {
        alert('Some stops are missing GPS coordinates. Update them in the trail editor first.');
        return;
    }

    const btn = document.getElementById('compute-route-btn');
    const status = document.getElementById('route-status');
    btn.disabled = true;
    btn.textContent = 'Computing...';
    status.classList.remove('hidden');
    status.textContent = 'Contacting route service...';

    try {
        const res = await fetch('/api/tour-route', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ waypoints }),
        });

        const result = await res.json();

        if (!res.ok || result.error) {
            status.textContent = 'Error: ' + (result.error || 'Route calculation failed.');
            return;
        }

        // Store in hidden textarea
        document.getElementById('driving_route_coordinates').value = JSON.stringify(result.coordinates);

        // Fill total_driving_km
        const kmInput = document.getElementById('total_driving_km');
        if (kmInput && result.total_km) {
            kmInput.value = result.total_km;
        }

        status.textContent = `Route computed: ${result.total_km} km total driving distance.`;

        // Render preview map
        renderRoutePreview(result.coordinates, stops);

    } catch (err) {
        status.textContent = 'Failed to reach route service. Check your internet connection.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>Recompute Route`;
    }
}

function renderRoutePreview(coordinates, stops) {
    const mapEl = document.getElementById('route-map');
    mapEl.classList.remove('hidden');

    if (window._routePreviewMap) {
        window._routePreviewMap.remove();
    }

    mapboxgl.accessToken = '{{ config('services.mapbox.access_token') }}';
    const map = new mapboxgl.Map({
        container: 'route-map',
        style: 'mapbox://styles/mapbox/outdoors-v12',
        center: coordinates[Math.floor(coordinates.length / 2)],
        zoom: 9,
    });

    window._routePreviewMap = map;

    map.on('load', () => {
        map.addSource('preview-route', {
            type: 'geojson',
            data: {
                type: 'Feature',
                geometry: { type: 'LineString', coordinates: coordinates },
            },
        });

        map.addLayer({
            id: 'preview-route-line',
            type: 'line',
            source: 'preview-route',
            paint: { 'line-color': '#3b82f6', 'line-width': 3 },
        });

        // Add numbered markers for each stop
        stops.forEach((stop, i) => {
            if (!stop.start_coordinates || stop.start_coordinates.length < 2) { return; }
            const el = document.createElement('div');
            el.className = 'flex h-6 w-6 items-center justify-center rounded-full bg-black text-white text-xs font-bold';
            el.textContent = i + 1;
            new mapboxgl.Marker({ element: el })
                .setLngLat([stop.start_coordinates[1], stop.start_coordinates[0]])
                .addTo(map);
        });

        // Fit bounds to route
        const bounds = coordinates.reduce((b, c) => b.extend(c), new mapboxgl.LngLatBounds(coordinates[0], coordinates[0]));
        map.fitBounds(bounds, { padding: 40 });
    });
}
</script>
