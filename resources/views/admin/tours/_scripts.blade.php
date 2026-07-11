<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>

<script>
function tourForm(availableTrails, availableFeatures, existingStops) {
    return {
        availableTrails: availableTrails,
        availableFeatures: availableFeatures,
        stops: existingStops.map(s => ({
            trail_id: s.trail_id,
            feature_id: s.feature_id || null,
            item_type: s.item_type || 'trail',
            feature_type: s.feature_type || null,
            name: s.name,
            start_coordinates: s.start_coordinates,
            stop_label: s.stop_label || '',
            estimated_visit_time: s.estimated_visit_time || '',
            driving_notes: s.driving_notes || '',
        })),
        search: '',

        get filteredItems() {
            if (this.search.length < 2) { return []; }
            const q = this.search.toLowerCase();
            const existingTrailIds = this.stops.filter(s => !s.feature_id).map(s => s.trail_id);
            const existingFeatureIds = this.stops.filter(s => s.feature_id).map(s => s.feature_id);

            const trails = this.availableTrails
                .filter(t => t.name.toLowerCase().includes(q) && !existingTrailIds.includes(t.id))
                .slice(0, 5);

            const features = this.availableFeatures
                .filter(f => (f.name.toLowerCase().includes(q) || (f.trail_name || '').toLowerCase().includes(q)) && !existingFeatureIds.includes(f.id))
                .slice(0, 5);

            return [...trails, ...features].slice(0, 8);
        },

        addStop(item) {
            if (item.item_type === 'feature') {
                this.stops.push({
                    trail_id: item.trail_id,
                    feature_id: item.id,
                    item_type: 'feature',
                    feature_type: item.feature_type,
                    name: item.name,
                    start_coordinates: item.coordinates,
                    stop_label: '',
                    estimated_visit_time: '',
                    driving_notes: '',
                });
            } else {
                this.stops.push({
                    trail_id: item.id,
                    feature_id: null,
                    item_type: 'trail',
                    feature_type: null,
                    name: item.name,
                    start_coordinates: item.start_coordinates,
                    stop_label: '',
                    estimated_visit_time: '',
                    driving_notes: '',
                });
            }
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

// ── Tour icon image gallery ──────────────────────────────────────────────────

function initTourIconGallery() {
    const gallery = document.getElementById('tour-icon-gallery');
    if (!gallery) { return; }

    fetch('{{ route("admin.trails.feature-icons") }}')
        .then(r => r.json())
        .then(icons => {
            if (!icons.length) {
                gallery.innerHTML = '<span class="text-xs text-muted-foreground italic">No icons uploaded yet.</span>';
                return;
            }
            const current = document.getElementById('tour-icon-image-path').value;
            gallery.innerHTML = icons.map(ic => `
                <div class="relative group" data-icon-wrapper="${ic.path}">
                    <button type="button" data-path="${ic.path}" data-url="${ic.url}"
                        class="tour-icon-thumb w-10 h-10 rounded-md border-2 ${ic.path === current ? 'border-primary' : 'border-transparent'} hover:border-primary overflow-hidden bg-white flex items-center justify-center p-0.5 transition-colors"
                        title="${ic.path.split('/').pop()}">
                        <img src="${ic.url}" class="w-full h-full object-contain" alt="">
                    </button>
                    <button type="button" data-delete-path="${ic.path}"
                        class="tour-icon-delete absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] leading-none flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-opacity shadow"
                        title="Delete this custom icon">✕</button>
                </div>`).join('');

            gallery.querySelectorAll('.tour-icon-thumb').forEach(btn => {
                btn.addEventListener('click', () => selectTourIcon(btn.dataset.path, btn.dataset.url));
            });
            gallery.querySelectorAll('.tour-icon-delete').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    deleteTourIcon(btn.dataset.deletePath, btn.closest('[data-icon-wrapper]'));
                });
            });

            // Restore preview if editing with existing icon_image
            if (current) {
                const match = icons.find(ic => ic.path === current);
                if (match) { selectTourIcon(match.path, match.url); }
            }
        })
        .catch(() => {
            gallery.innerHTML = '<span class="text-xs text-muted-foreground italic">Could not load icons.</span>';
        });
}

function selectTourIcon(path, url) {
    document.getElementById('tour-icon-image-path').value = path;

    const preview = document.getElementById('tour-icon-image-preview');
    const previewImg = document.getElementById('tour-icon-image-preview-img');
    const previewName = document.getElementById('tour-icon-image-name');
    if (preview && previewImg) {
        previewImg.src = url;
        if (previewName) { previewName.textContent = path.split('/').pop(); }
        preview.classList.remove('hidden');
        preview.classList.add('flex');
    }

    document.querySelectorAll('#tour-icon-gallery .tour-icon-thumb').forEach(btn => {
        btn.classList.toggle('border-primary', btn.dataset.path === path);
        btn.classList.toggle('border-transparent', btn.dataset.path !== path);
    });
}

function clearTourIcon() {
    document.getElementById('tour-icon-image-path').value = '';

    const preview = document.getElementById('tour-icon-image-preview');
    if (preview) {
        preview.classList.add('hidden');
        preview.classList.remove('flex');
    }

    document.querySelectorAll('#tour-icon-gallery .tour-icon-thumb').forEach(btn => {
        btn.classList.remove('border-primary');
        btn.classList.add('border-transparent');
    });
}

function addToTourIconGallery(path, url) {
    const gallery = document.getElementById('tour-icon-gallery');
    if (!gallery) { return; }

    const placeholder = gallery.querySelector('span.italic');
    if (placeholder) { placeholder.remove(); }

    const wrapper = document.createElement('div');
    wrapper.className = 'relative group';
    wrapper.dataset.iconWrapper = path;

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.dataset.path = path;
    btn.dataset.url = url;
    btn.className = 'tour-icon-thumb w-10 h-10 rounded-md border-2 border-transparent hover:border-primary overflow-hidden bg-white flex items-center justify-center p-0.5 transition-colors';
    btn.title = path.split('/').pop();
    btn.addEventListener('click', () => selectTourIcon(path, url));
    btn.innerHTML = `<img src="${url}" class="w-full h-full object-contain" alt="">`;

    const deleteBtn = document.createElement('button');
    deleteBtn.type = 'button';
    deleteBtn.dataset.deletePath = path;
    deleteBtn.className = 'tour-icon-delete absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] leading-none flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-opacity shadow';
    deleteBtn.title = 'Delete this custom icon';
    deleteBtn.textContent = '✕';
    deleteBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        deleteTourIcon(path, wrapper);
    });

    wrapper.appendChild(btn);
    wrapper.appendChild(deleteBtn);
    gallery.prepend(wrapper);
}

async function deleteTourIcon(path, wrapperEl) {
    if (!confirm('Delete this custom icon? This cannot be undone.')) { return; }

    const requestDelete = async (force = false) => {
        const res = await fetch('{{ route("admin.trails.feature-icons.delete") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ path, force })
        });
        if (!res.ok) { throw new Error('Failed to delete icon'); }
        return res.json();
    };

    try {
        let data = await requestDelete();

        if (!data.deleted && data.in_use) {
            const proceed = confirm(`This icon is currently used by ${data.in_use} item(s). Deleting it will revert them to their tour type's stock icon. Delete anyway?`);
            if (!proceed) { return; }
            data = await requestDelete(true);
        }

        if (!data.deleted) { return; }

        if (wrapperEl) { wrapperEl.remove(); }

        if (document.getElementById('tour-icon-image-path').value === path) {
            clearTourIcon();
        }

        const gallery = document.getElementById('tour-icon-gallery');
        if (gallery && !gallery.querySelector('.tour-icon-thumb')) {
            gallery.innerHTML = '<span class="text-xs text-muted-foreground italic">No icons uploaded yet.</span>';
        }
    } catch {
        alert('Failed to delete icon.');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    initTourIconGallery();

    const uploadInput = document.getElementById('tour-icon-image-input');
    if (uploadInput) {
        uploadInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) { return; }
            const statusEl = document.getElementById('tour-icon-upload-status');
            if (statusEl) { statusEl.textContent = 'Uploading…'; }

            const fd = new FormData();
            fd.append('icon', file);
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            try {
                const res = await fetch('{{ route("admin.trails.feature-icons.upload") }}', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.path && data.url) {
                    selectTourIcon(data.path, data.url);
                    addToTourIconGallery(data.path, data.url);
                    if (statusEl) { statusEl.textContent = 'Uploaded!'; }
                    setTimeout(() => { if (statusEl) { statusEl.textContent = ''; } }, 2000);
                }
            } catch {
                if (statusEl) { statusEl.textContent = 'Upload failed.'; }
            }
            uploadInput.value = '';
        });
    }

    const clearBtn = document.getElementById('tour-icon-image-clear');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearTourIcon);
    }
});

// ── Tour type auto-icon ──────────────────────────────────────────────────────

const TOUR_TYPE_ICONS = {
    waterfalls: '💧',
    fishing: '🎣',
    heritage: '🏛️',
    scenic: '🌄',
};

function syncTourTypeIcon(type) {
    const iconInput = document.getElementById('tour_icon');
    const imagePathInput = document.getElementById('tour-icon-image-path');
    if (!iconInput) { return; }
    if (iconInput.value.trim() || (imagePathInput && imagePathInput.value.trim())) { return; }
    iconInput.value = TOUR_TYPE_ICONS[type] || '';
}

document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('tour_type');
    if (typeSelect && typeSelect.value) {
        syncTourTypeIcon(typeSelect.value);
    }
});

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

// ── Video link preview ───────────────────────────────────────────────────────

function getAdminVideoEmbedUrl(videoUrl) {
    const youtubeMatch = videoUrl.match(/(?:youtube\.com\/(?:watch\?.*v=|shorts\/|live\/|embed\/)|youtu\.be\/)([^"&?\/\s]{11})/i);
    if (youtubeMatch) {
        return `https://www.youtube.com/embed/${youtubeMatch[1]}`;
    }
    const vimeoMatch = videoUrl.match(/vimeo\.com\/(\d+)/);
    if (vimeoMatch) {
        return `https://player.vimeo.com/video/${vimeoMatch[1]}`;
    }
    return null;
}

function getAdminVideoThumbUrl(videoUrl) {
    const youtubeMatch = videoUrl.match(/(?:youtube\.com\/(?:watch\?.*v=|shorts\/|live\/|embed\/)|youtu\.be\/)([^"&?\/\s]{11})/i);
    if (youtubeMatch) {
        return `https://img.youtube.com/vi/${youtubeMatch[1]}/hqdefault.jpg`;
    }
    const vimeoMatch = videoUrl.match(/vimeo\.com\/(\d+)/);
    if (vimeoMatch) {
        return `https://vumbnail.com/${vimeoMatch[1]}.jpg`;
    }
    return null;
}

function updateTourVideoPreview(videoUrl) {
    const preview = document.getElementById('tour-video-preview');
    const img = document.getElementById('tour-video-preview-img');
    if (!preview || !img) { return; }

    const thumb = videoUrl ? getAdminVideoThumbUrl(videoUrl.trim()) : null;
    if (thumb) {
        img.src = thumb;
        img.classList.remove('hidden');
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
}

function playAdminVideo(videoUrl) {
    if (!videoUrl) { return; }
    const embedUrl = getAdminVideoEmbedUrl(videoUrl.trim());
    if (!embedUrl) { return; }

    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4';
    modal.innerHTML = `
        <div class="relative w-full max-w-4xl">
            <button onclick="this.closest('.fixed.inset-0').remove()"
                class="fixed top-4 right-4 text-white hover:text-gray-300 bg-gray-900 bg-opacity-75 rounded-full p-2 z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="relative bg-black rounded-lg overflow-hidden" style="padding-bottom: 56.25%;">
                <iframe src="${embedUrl}?autoplay=1"
                    class="absolute top-0 left-0 w-full h-full"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>
        </div>`;
    modal.addEventListener('click', (e) => {
        if (e.target === modal) { modal.remove(); }
    });
    document.body.appendChild(modal);
}
</script>
