<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    mapboxgl.accessToken = '{{ config('services.mapbox.access_token') }}';

    const defaultLat = parseFloat(document.getElementById('latitude').value) || 54.7804;
    const defaultLng = parseFloat(document.getElementById('longitude').value) || -127.1698;

    const map = new mapboxgl.Map({
        container: 'coordinate-map',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [defaultLng, defaultLat],
        zoom: 14,
        attributionControl: false,
    });

    map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'bottom-right');

    const marker = new mapboxgl.Marker({ draggable: true })
        .setLngLat([defaultLng, defaultLat])
        .addTo(map);

    marker.on('dragend', function () {
        const pos = marker.getLngLat();
        updateCoordinates(pos.lat, pos.lng);
    });

    map.on('click', function (e) {
        marker.setLngLat([e.lngLat.lng, e.lngLat.lat]);
        updateCoordinates(e.lngLat.lat, e.lngLat.lng);
    });

    function updateCoordinates(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
    }

    // Map search
    const searchInput = document.getElementById('map-search-input');
    const clearBtn = document.getElementById('clear-search-btn');
    const loadingEl = document.getElementById('search-loading');
    const dropdown = document.getElementById('search-results-dropdown');
    let searchTimeout = null;

    searchInput.addEventListener('input', function () {
        const q = this.value.trim();
        if (q.length > 2) {
            clearBtn.classList.remove('hidden');
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => performSearch(q), 500);
        } else {
            clearBtn.classList.add('hidden');
            dropdown.classList.add('hidden');
        }
    });

    clearBtn.addEventListener('click', function () {
        searchInput.value = '';
        clearBtn.classList.add('hidden');
        dropdown.classList.add('hidden');
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    async function performSearch(query) {
        loadingEl.classList.remove('hidden');
        try {
            const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1`);
            const results = await res.json();
            loadingEl.classList.add('hidden');
            if (results.length > 0) {
                dropdown.innerHTML = results.map(r => `
                    <div class="search-result-item p-3 hover:bg-muted cursor-pointer border-b last:border-b-0"
                         data-lat="${r.lat}" data-lon="${r.lon}" data-name="${r.display_name}">
                        <div class="font-medium text-sm truncate">${r.display_name}</div>
                        <div class="text-xs text-muted-foreground">${r.type || 'Location'}</div>
                    </div>
                `).join('');
                dropdown.classList.remove('hidden');
                dropdown.querySelectorAll('.search-result-item').forEach(item => {
                    item.addEventListener('click', function () {
                        const lat = parseFloat(this.dataset.lat);
                        const lon = parseFloat(this.dataset.lon);
                        marker.setLngLat([lon, lat]);
                        updateCoordinates(lat, lon);
                        map.flyTo({ center: [lon, lat], zoom: 16 });
                        searchInput.value = this.dataset.name;
                        dropdown.classList.add('hidden');
                    });
                });
            } else {
                dropdown.innerHTML = '<div class="p-3 text-sm text-muted-foreground">No results found</div>';
                dropdown.classList.remove('hidden');
            }
        } catch (e) {
            loadingEl.classList.add('hidden');
        }
    }
});

function updateBusinessIcon() {
    const select = document.getElementById('business_type');
    const preview = document.getElementById('icon-preview');
    if (select.value) {
        preview.textContent = select.options[select.selectedIndex].text.split(' ')[0];
    } else {
        preview.textContent = '📍';
    }
}

function handlePhotoSelection(input) {
    const container = document.getElementById('photo-preview');
    container.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const div = document.createElement('div');
                div.className = 'relative aspect-square rounded-md overflow-hidden border';
                div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                container.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

function addVideoUrlField() {
    const container = document.getElementById('video-urls-container');
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = `
        <input type="url" name="video_urls[]"
            placeholder="https://youtube.com/watch?v=... or https://vimeo.com/..."
            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
        <button type="button" onclick="this.parentElement.remove()"
            class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-destructive hover:text-destructive-foreground h-10 w-10 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    container.appendChild(div);
}

// Init icon preview
updateBusinessIcon();
</script>

<style>
.search-result-item { transition: background-color 0.15s ease; }
.search-result-item:hover { background-color: hsl(var(--muted)); }
</style>
