<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>

<script>
let tourStopUid = 0;

/**
 * Build a stop object for the Alpine form with every field present, so all
 * stop types (trail, feature, facility, pinned, roaming) behave the same.
 * `uid` keys the x-for rows so a selected photo file moves with its stop.
 */
function makeTourStop(fields) {
    return {
        uid: ++tourStopUid,
        trail_id: null,
        feature_id: null,
        facility_id: null,
        latitude: null,
        longitude: null,
        item_type: 'trail',
        feature_type: null,
        facility_type_label: null,
        name: '',
        start_coordinates: null,
        stop_label: '',
        estimated_visit_time: '',
        driving_notes: '',
        icon: '',
        icon_image: '',
        icon_image_url: null,
        description: '',
        existing_image: '',
        image_url: null,
        new_image_preview: null,
        remove_image: false,
        showDetails: false,
        showIconPicker: false,
        ...fields,
    };
}

function tourForm(availableTrails, availableFeatures, availableFacilities, existingStops) {
    return {
        availableTrails: availableTrails,
        availableFeatures: availableFeatures,
        availableFacilities: availableFacilities,
        stops: existingStops.map(s => makeTourStop({
            trail_id: s.trail_id,
            feature_id: s.feature_id || null,
            facility_id: s.facility_id || null,
            latitude: s.latitude ?? null,
            longitude: s.longitude ?? null,
            item_type: s.item_type || 'trail',
            feature_type: s.feature_type || null,
            facility_type_label: s.facility_type_label || null,
            name: s.name,
            start_coordinates: s.start_coordinates,
            stop_label: s.stop_label || '',
            estimated_visit_time: s.estimated_visit_time || '',
            driving_notes: s.driving_notes || '',
            icon: s.icon || '',
            icon_image: s.icon_image || '',
            icon_image_url: s.icon_image_url || null,
            description: s.description || '',
            existing_image: s.existing_image || '',
            image_url: s.image_url || null,
            showDetails: Boolean(s.icon || s.icon_image || s.description || s.existing_image),
        })),
        search: '',
        iconPresets: ['🐴', '🎅', '📸', '🤶', '🎵', '🎶', '🎨', '🔥', '🍿', '🛍️', '🎄', '☕', '🍪', '🎁', '⛸️', '🚚'],
        iconGallery: [],
        iconGalleryStatus: '',
        iconGalleryLoaded: false,

        init() {
            window.tourRouteEditor.attachForm(this);
            this.$watch('stops', () => window.tourRouteEditor.renderStops());
        },

        /** Pinned and roaming stops are custom activities that need their own name. */
        isCustomStop(stop) {
            return stop.item_type === 'pinned' || stop.item_type === 'roaming';
        },

        /**
         * Add a stop pinned at a clicked map location (e.g. a parade street corner).
         */
        addPinnedStop(lat, lng) {
            const roundedLat = Number(lat.toFixed(7));
            const roundedLng = Number(lng.toFixed(7));
            this.stops.push(makeTourStop({
                latitude: roundedLat,
                longitude: roundedLng,
                item_type: 'pinned',
                name: 'Pinned on map',
                start_coordinates: [roundedLat, roundedLng],
            }));
        },

        /** Add an activity with no fixed spot (listed on the page, no map pin). */
        addRoamingStop() {
            this.stops.push(makeTourStop({ item_type: 'roaming', name: 'Roaming activity' }));
        },

        movePinnedStop(index, lat, lng) {
            const stop = this.stops[index];
            if (!stop || stop.item_type !== 'pinned') { return; }
            stop.latitude = Number(lat.toFixed(7));
            stop.longitude = Number(lng.toFixed(7));
            stop.start_coordinates = [stop.latitude, stop.longitude];
        },

        get filteredItems() {
            if (this.search.length < 2) { return []; }
            const q = this.search.toLowerCase();
            const existingTrailIds = this.stops.filter(s => s.item_type === 'trail').map(s => s.trail_id);
            const existingFeatureIds = this.stops.filter(s => s.feature_id).map(s => s.feature_id);
            const existingFacilityIds = this.stops.filter(s => s.facility_id).map(s => s.facility_id);

            const trails = this.availableTrails
                .filter(t => t.name.toLowerCase().includes(q) && !existingTrailIds.includes(t.id))
                .slice(0, 5);

            const features = this.availableFeatures
                .filter(f => (f.name.toLowerCase().includes(q) || (f.trail_name || '').toLowerCase().includes(q)) && !existingFeatureIds.includes(f.id))
                .slice(0, 5);

            const facilities = this.availableFacilities
                .filter(f => (f.name.toLowerCase().includes(q) || (f.facility_type_label || '').toLowerCase().includes(q)) && !existingFacilityIds.includes(f.id))
                .slice(0, 5);

            return [...trails, ...features, ...facilities].slice(0, 8);
        },

        addStop(item) {
            if (item.item_type === 'feature') {
                this.stops.push(makeTourStop({
                    trail_id: item.trail_id,
                    feature_id: item.id,
                    item_type: 'feature',
                    feature_type: item.feature_type,
                    name: item.name,
                    start_coordinates: item.coordinates,
                }));
            } else if (item.item_type === 'facility') {
                this.stops.push(makeTourStop({
                    facility_id: item.id,
                    item_type: 'facility',
                    facility_type_label: item.facility_type_label,
                    name: item.name,
                    start_coordinates: item.start_coordinates,
                }));
            } else {
                this.stops.push(makeTourStop({
                    trail_id: item.id,
                    item_type: 'trail',
                    name: item.name,
                    start_coordinates: item.start_coordinates,
                }));
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

        // ── Stop icon (emoji, or an image from the shared icon gallery) ──────

        setStopEmoji(stop, emoji) {
            stop.icon = emoji;
            stop.icon_image = '';
            stop.icon_image_url = null;
        },

        clearStopIcon(stop) {
            stop.icon = '';
            stop.icon_image = '';
            stop.icon_image_url = null;
        },

        toggleIconPicker(stop) {
            stop.showIconPicker = !stop.showIconPicker;
            if (stop.showIconPicker) { this.loadIconGallery(); }
        },

        async loadIconGallery() {
            if (this.iconGalleryLoaded) { return; }
            this.iconGalleryStatus = 'Loading icons…';
            try {
                const res = await fetch('{{ route("admin.trails.feature-icons") }}', { headers: { 'Accept': 'application/json' } });
                this.iconGallery = await res.json();
                this.iconGalleryLoaded = true;
                this.iconGalleryStatus = this.iconGallery.length ? '' : 'No icons uploaded yet.';
            } catch {
                this.iconGalleryStatus = 'Could not load icons.';
            }
        },

        chooseStopIconImage(stop, icon) {
            stop.icon_image = icon.path;
            stop.icon_image_url = icon.url;
            stop.showIconPicker = false;
        },

        async uploadStopIcon(stop, event) {
            const file = event.target.files && event.target.files[0];
            if (!file) { return; }
            this.iconGalleryStatus = 'Uploading…';

            const fd = new FormData();
            fd.append('icon', file);
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            try {
                const res = await fetch('{{ route("admin.trails.feature-icons.upload") }}', {
                    method: 'POST',
                    body: fd,
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (!res.ok || !data.path) { throw new Error('upload failed'); }
                this.iconGallery = [data, ...this.iconGallery];
                this.iconGalleryStatus = '';
                this.chooseStopIconImage(stop, data);
            } catch {
                this.iconGalleryStatus = 'Upload failed (images up to 2 MB).';
            }
            event.target.value = '';
        },

        // ── Stop photo ───────────────────────────────────────────────────────

        previewStopImage(stop, event) {
            const file = event.target.files && event.target.files[0];
            if (stop.new_image_preview) { URL.revokeObjectURL(stop.new_image_preview); }
            stop.new_image_preview = file ? URL.createObjectURL(file) : null;
            if (file) { stop.remove_image = false; }
        },

        removeStopImage(stop, button) {
            const fileInput = button.closest('[data-stop-row]')?.querySelector(`input[type="file"][name$="[image]"]`);
            if (fileInput) { fileInput.value = ''; }
            if (stop.new_image_preview) { URL.revokeObjectURL(stop.new_image_preview); }
            stop.new_image_preview = null;
            stop.remove_image = true;
        },
    };
}

async function deleteTourCoverImage(url, button) {
    if (!confirm('Delete the cover image for this tour?')) { return; }
    button.disabled = true;

    try {
        const res = await fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();

        if (!data.success) {
            alert('Failed to delete cover image.');
            button.disabled = false;
            return;
        }

        document.getElementById('current-cover-image')?.remove();
    } catch {
        alert('Failed to delete cover image.');
        button.disabled = false;
    }
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

// Drag-and-drop support for a file input's dropzone label/container
function enableDropzone(dropzoneEl, inputEl, onDrop) {
    if (!dropzoneEl || !inputEl) { return; }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzoneEl.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzoneEl.classList.add('border-green-500', 'bg-green-50');
        });
    });

    ['dragleave', 'dragend', 'drop'].forEach(eventName => {
        dropzoneEl.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzoneEl.classList.remove('border-green-500', 'bg-green-50');
        });
    });

    dropzoneEl.addEventListener('drop', (e) => {
        const files = e.dataTransfer && e.dataTransfer.files;
        if (!files || files.length === 0) { return; }
        onDrop(files);
    });
}

['dragover', 'drop'].forEach(eventName => {
    window.addEventListener(eventName, (e) => {
        if (!e.target.closest('#cover-image-dropzone, #tour-icon-dropzone')) {
            e.preventDefault();
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const coverInput = document.getElementById('cover_image');
    enableDropzone(document.getElementById('cover-image-dropzone'), coverInput, (files) => {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(files[0]);
        coverInput.files = dataTransfer.files;
        previewCoverImage(coverInput);
    });
});

// Compute multi-stop driving route via /api/tour-route
async function computeTourRoute() {
    const alpineEl = document.getElementById('tour-form-root');
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

        // Show it in the route editor (which also stores it in the hidden textarea
        // and fills total_driving_km).
        window.tourRouteEditor.setRoute(result.coordinates);

        status.textContent = `Route computed: ${result.total_km} km total driving distance.`;

    } catch (err) {
        status.textContent = 'Failed to reach route service. Check your internet connection.';
    } finally {
        btn.disabled = false;
        btn.textContent = '🚗 Recompute route through stops';
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

async function uploadTourIcon(file) {
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

    const uploadInput = document.getElementById('tour-icon-image-input');
    if (uploadInput) { uploadInput.value = ''; }
}

document.addEventListener('DOMContentLoaded', function () {
    initTourIconGallery();

    const uploadInput = document.getElementById('tour-icon-image-input');
    if (uploadInput) {
        uploadInput.addEventListener('change', (e) => uploadTourIcon(e.target.files[0]));

        enableDropzone(document.getElementById('tour-icon-dropzone'), uploadInput, (files) => {
            uploadTourIcon(files[0]);
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
    christmas: '🎄',
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

// ── Route & Map editor ───────────────────────────────────────────────────────
// Lets an admin draw the route by clicking along streets, pin stops anywhere,
// snap a drawn line to real roads, and see stops + route on one map.
// Route coordinates are kept as [lng, lat] (Mapbox order), as stored in the DB.

window.tourRouteEditor = {
    map: null,
    form: null,
    mode: 'none',
    route: [],
    history: [],
    stopMarkers: [],
    isReady: false,
    maxSnapWaypoints: 50,

    attachForm(form) {
        this.form = form;
        this.renderStops();
    },

    init() {
        const container = document.getElementById('route-map');
        if (!container || typeof mapboxgl === 'undefined') { return; }

        const textarea = document.getElementById('driving_route_coordinates');
        try {
            const parsed = JSON.parse(textarea.value || '[]');
            this.route = Array.isArray(parsed) ? parsed : [];
        } catch {
            this.route = [];
        }

        mapboxgl.accessToken = @json(config('services.mapbox.access_token'));
        this.map = new mapboxgl.Map({
            container: 'route-map',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [-127.17, 54.78],
            zoom: 13,
        });
        this.map.addControl(new mapboxgl.NavigationControl(), 'top-right');

        this.map.on('load', () => {
            this.addArrowImage();
            this.map.addSource('editor-route', { type: 'geojson', data: this.routeGeoJson() });
            this.map.addLayer({
                id: 'editor-route-line',
                type: 'line',
                source: 'editor-route',
                filter: ['==', '$type', 'LineString'],
                layout: { 'line-join': 'round', 'line-cap': 'round' },
                paint: { 'line-color': '#B3202A', 'line-width': 4 },
            });
            this.map.addLayer({
                id: 'editor-route-arrows',
                type: 'symbol',
                source: 'editor-route',
                filter: ['==', '$type', 'LineString'],
                layout: {
                    'symbol-placement': 'line',
                    'symbol-spacing': 70,
                    'icon-image': 'editor-arrow',
                    'icon-size': 0.55,
                    'icon-allow-overlap': true,
                    'icon-rotation-alignment': 'map',
                },
            });
            this.map.addLayer({
                id: 'editor-route-points',
                type: 'circle',
                source: 'editor-route',
                filter: ['==', '$type', 'Point'],
                paint: {
                    'circle-radius': ['case', ['get', 'isEnd'], 6, 3.5],
                    'circle-color': ['case', ['get', 'isStart'], '#1E5B3A', '#ffffff'],
                    'circle-stroke-color': '#B3202A',
                    'circle-stroke-width': 2,
                },
            });

            this.isReady = true;
            this.renderStops();
            this.updateRouteUi(false);
            this.fitToContent();
        });

        this.map.on('click', (e) => this.handleMapClick(e));
    },

    addArrowImage() {
        const size = 48;
        const canvas = document.createElement('canvas');
        canvas.width = size;
        canvas.height = size;
        const ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 7;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.beginPath();
        ctx.moveTo(16, 12);
        ctx.lineTo(32, 24);
        ctx.lineTo(16, 36);
        ctx.stroke();
        this.map.addImage('editor-arrow', ctx.getImageData(0, 0, size, size), { pixelRatio: 2 });
    },

    /**
     * The line plus vertex points. Vertices are shown only for hand-drawn
     * routes (computed/snapped routes have hundreds of points).
     */
    routeGeoJson() {
        const features = [];
        if (this.route.length >= 2) {
            features.push({ type: 'Feature', geometry: { type: 'LineString', coordinates: this.route }, properties: {} });
        }
        const showVertices = this.route.length <= 150;
        this.route.forEach((coord, i) => {
            const isStart = i === 0;
            const isEnd = i === 0 || i === this.route.length - 1;
            if (!showVertices && !isEnd) { return; }
            features.push({ type: 'Feature', geometry: { type: 'Point', coordinates: coord }, properties: { isStart, isEnd } });
        });
        return { type: 'FeatureCollection', features };
    },

    toggleMode(mode) {
        this.mode = this.mode === mode ? 'none' : mode;

        document.getElementById('route-draw-btn')?.classList.toggle('ring-2', this.mode === 'draw');
        document.getElementById('route-draw-btn')?.classList.toggle('ring-red-500', this.mode === 'draw');
        document.getElementById('route-pin-btn')?.classList.toggle('ring-2', this.mode === 'pin');
        document.getElementById('route-pin-btn')?.classList.toggle('ring-red-500', this.mode === 'pin');

        const hint = document.getElementById('route-mode-hint');
        const hints = {
            draw: 'Drawing: click along the streets in parade order. Each click adds a point to the end of the route. Click "Draw route" again to stop.',
            pin: 'Pinning: click the map to add a stop there, then type its name in the Tour Stops list. Click "Pin stop on map" again to stop.',
        };
        if (hint) {
            hint.textContent = hints[this.mode] || '';
            hint.classList.toggle('hidden', this.mode === 'none');
        }

        if (this.map) {
            this.map.getCanvas().style.cursor = this.mode === 'none' ? '' : 'crosshair';
        }
    },

    handleMapClick(e) {
        if (this.mode === 'draw') {
            this.pushHistory();
            this.route = [...this.route, [Number(e.lngLat.lng.toFixed(6)), Number(e.lngLat.lat.toFixed(6))]];
            this.updateRouteUi();
        } else if (this.mode === 'pin' && this.form) {
            this.form.addPinnedStop(e.lngLat.lat, e.lngLat.lng);
        }
    },

    pushHistory() {
        this.history.push(this.route.map((c) => [...c]));
        if (this.history.length > 200) { this.history.shift(); }
    },

    undo() {
        if (!this.history.length) { return; }
        this.route = this.history.pop();
        this.updateRouteUi();
    },

    clear() {
        if (!this.route.length) { return; }
        if (!confirm('Clear the whole route line? (Stops are kept. You can undo this.)')) { return; }
        this.pushHistory();
        this.route = [];
        this.updateRouteUi();
    },

    /**
     * Replace the route (e.g. from the route service) and frame it.
     *
     * @param {Array<[number, number]>} coordinates [lng, lat] pairs
     */
    setRoute(coordinates) {
        this.pushHistory();
        this.route = coordinates.map((c) => [Number(c[0]), Number(c[1])]);
        this.updateRouteUi();
        this.fitToContent();
    },

    /** Re-trace the current line along real roads via /api/tour-route. */
    async snapToStreets() {
        if (this.route.length < 2) {
            alert('Draw at least two route points first.');
            return;
        }

        // The route service accepts a limited number of waypoints: keep the
        // first and last points and sample evenly in between.
        let waypoints = this.route;
        if (waypoints.length > this.maxSnapWaypoints) {
            const step = (waypoints.length - 1) / (this.maxSnapWaypoints - 1);
            waypoints = Array.from({ length: this.maxSnapWaypoints }, (_, i) => this.route[Math.round(i * step)]);
        }

        const btn = document.getElementById('route-snap-btn');
        const status = document.getElementById('route-status');
        btn.disabled = true;
        status.classList.remove('hidden');
        status.textContent = 'Snapping the route to streets…';

        try {
            const res = await fetch('/api/tour-route', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                // The endpoint takes [lat, lng] waypoints.
                body: JSON.stringify({ waypoints: waypoints.map((c) => [c[1], c[0]]) }),
            });
            const result = await res.json();

            if (!res.ok || result.error) {
                status.textContent = 'Could not snap to streets: ' + (result.error || 'route service error') + '. Your drawn line is unchanged.';
                return;
            }

            this.setRoute(result.coordinates);
            status.textContent = `Snapped to streets (${result.total_km} km). Check it follows the parade streets — use Undo to go back to your drawn line.`;
        } catch {
            status.textContent = 'Failed to reach the route service. Your drawn line is unchanged.';
        } finally {
            btn.disabled = false;
        }
    },

    routeLengthKm() {
        const toRad = (d) => d * Math.PI / 180;
        let km = 0;
        for (let i = 1; i < this.route.length; i++) {
            const [lng1, lat1] = this.route[i - 1];
            const [lng2, lat2] = this.route[i];
            const dLat = toRad(lat2 - lat1);
            const dLng = toRad(lng2 - lng1);
            const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
            km += 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }
        return km;
    },

    /**
     * Sync the map line, hidden form field, distance and button states.
     * The saved distance field is only overwritten after an actual edit.
     */
    updateRouteUi(isEdit = true) {
        const km = this.routeLengthKm();

        if (isEdit) {
            document.getElementById('driving_route_coordinates').value = JSON.stringify(this.route);
            const kmInput = document.getElementById('total_driving_km');
            if (kmInput) { kmInput.value = this.route.length >= 2 ? km.toFixed(1) : ''; }
        }

        document.getElementById('route-point-count').textContent = this.route.length;
        document.getElementById('route-km').textContent = km.toFixed(1);
        document.getElementById('route-undo-btn').disabled = this.history.length === 0;
        document.getElementById('route-clear-btn').disabled = this.route.length === 0;
        document.getElementById('route-snap-btn').disabled = this.route.length < 2;

        if (this.isReady) {
            this.map.getSource('editor-route').setData(this.routeGeoJson());
        }
    },

    /** Numbered markers for every stop; pinned stops can be dragged. */
    renderStops() {
        if (!this.isReady || !this.form) { return; }

        this.stopMarkers.forEach((m) => m.remove());
        this.stopMarkers = [];

        this.form.stops.forEach((stop, index) => {
            const coords = stop.start_coordinates;
            if (!coords || coords.length < 2) { return; }

            const isPinned = stop.item_type === 'pinned';
            const hasIcon = Boolean(stop.icon_image_url || stop.icon);
            const el = document.createElement('div');
            el.className = 'flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold border-2 shadow overflow-hidden';
            el.style.background = hasIcon ? '#ffffff' : (isPinned ? '#B3202A' : '#111827');
            el.style.borderColor = hasIcon ? '#B3202A' : '#ffffff';
            el.style.color = '#ffffff';
            el.style.cursor = isPinned ? 'grab' : 'default';
            if (stop.icon_image_url) {
                const img = document.createElement('img');
                img.src = stop.icon_image_url;
                img.alt = '';
                img.className = 'h-full w-full object-contain';
                img.draggable = false;
                el.appendChild(img);
            } else if (stop.icon) {
                el.style.fontSize = '15px';
                el.textContent = stop.icon;
            } else {
                el.textContent = index + 1;
            }
            el.title = (stop.stop_label || stop.name) + (isPinned ? ' — drag to move' : '');

            const marker = new mapboxgl.Marker({ element: el, draggable: isPinned })
                .setLngLat([coords[1], coords[0]])
                .addTo(this.map);

            if (isPinned) {
                marker.on('dragend', () => {
                    const { lat, lng } = marker.getLngLat();
                    this.form.movePinnedStop(index, lat, lng);
                });
            }

            this.stopMarkers.push(marker);
        });
    },

    fitToContent() {
        if (!this.isReady) { return; }
        const bounds = new mapboxgl.LngLatBounds();
        let hasContent = false;

        this.route.forEach((c) => { bounds.extend(c); hasContent = true; });
        (this.form?.stops || []).forEach((s) => {
            if (s.start_coordinates && s.start_coordinates.length >= 2) {
                bounds.extend([s.start_coordinates[1], s.start_coordinates[0]]);
                hasContent = true;
            }
        });

        if (hasContent) {
            this.map.fitBounds(bounds, { padding: 50, maxZoom: 16, duration: 0 });
        }
    },
};

document.addEventListener('DOMContentLoaded', () => window.tourRouteEditor.init());

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
