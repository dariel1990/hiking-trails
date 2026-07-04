<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    mapboxgl.accessToken = '{{ config('services.mapbox.access_token') }}';

    const defaultLat = parseFloat(document.getElementById('latitude').value) || 54.7804;
    const defaultLng = parseFloat(document.getElementById('longitude').value) || -127.1698;

    window.coordinateMap = new mapboxgl.Map({
        container: 'coordinate-map',
        style: 'mapbox://styles/mapbox/standard',
        center: [defaultLng, defaultLat],
        zoom: 14,
        attributionControl: false,
    });
    const map = window.coordinateMap;

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

let businessTypeIcon = '📍';

function updateBusinessIcon() {
    const select = document.getElementById('business_type');
    if (select.value) {
        businessTypeIcon = select.options[select.selectedIndex].text.split(' ')[0];
    } else {
        businessTypeIcon = '📍';
    }
    refreshBusinessIconPreview();
}

function refreshBusinessIconPreview() {
    const preview = document.getElementById('icon-preview');
    const customIcon = document.getElementById('icon');
    const iconImagePath = document.getElementById('business-icon-image-path');
    if (!preview) { return; }

    if (iconImagePath && iconImagePath.value) {
        const url = document.getElementById('business-icon-image-preview-img')?.src;
        preview.innerHTML = url ? `<img src="${url}" class="w-6 h-6 object-contain">` : businessTypeIcon;
        return;
    }

    const custom = (customIcon?.value || '').trim();
    preview.textContent = custom || businessTypeIcon;
}

async function handlePhotoSelection(input) {
    const container = document.getElementById('photo-preview');
    if (!input.files || input.files.length === 0) {
        container.innerHTML = '';
        return;
    }

    // Large phone photos (30MB+) make the upload and the server-side processing
    // slow. Shrinking them in the browser before they ever hit the network fixes
    // both: the transfer is smaller, and the server has less work to do.
    const submitBtn = document.querySelector('button[type="submit"]');
    const originalSubmitText = submitBtn ? submitBtn.textContent : null;
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Compressing images…';
    }
    container.innerHTML = '<div class="col-span-full text-sm text-muted-foreground py-2">Compressing images…</div>';

    try {
        const files = Array.from(input.files);
        const compressed = await Promise.all(files.map(file => compressImageForUpload(file)));

        const dataTransfer = new DataTransfer();
        compressed.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;

        container.innerHTML = '';
        compressed.forEach(file => {
            const url = URL.createObjectURL(file);
            const div = document.createElement('div');
            div.className = 'relative aspect-square rounded-md overflow-hidden border';
            div.innerHTML = `<img src="${url}" class="w-full h-full object-cover">`;
            container.appendChild(div);
        });
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalSubmitText;
        }
    }
}

function compressImageForUpload(file, maxDimension = 1920, quality = 0.85) {
    return new Promise((resolve) => {
        if (!file.type.startsWith('image/') || file.type === 'image/svg+xml' || file.type === 'image/gif') {
            resolve(file);
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        const img = new Image();

        img.onload = () => {
            URL.revokeObjectURL(objectUrl);

            const { width, height } = img;
            const needsResize = width > maxDimension || height > maxDimension;
            const needsCompression = file.size > 1.5 * 1024 * 1024;

            if (!needsResize && !needsCompression) {
                resolve(file);
                return;
            }

            const scale = Math.min(1, maxDimension / Math.max(width, height));
            const targetWidth = Math.max(1, Math.round(width * scale));
            const targetHeight = Math.max(1, Math.round(height * scale));

            const canvas = document.createElement('canvas');
            canvas.width = targetWidth;
            canvas.height = targetHeight;
            canvas.getContext('2d').drawImage(img, 0, 0, targetWidth, targetHeight);

            canvas.toBlob((blob) => {
                if (!blob || blob.size >= file.size) {
                    resolve(file);
                    return;
                }
                const newName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
                resolve(new File([blob], newName, { type: 'image/jpeg', lastModified: Date.now() }));
            }, 'image/jpeg', quality);
        };

        img.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            resolve(file);
        };

        img.src = objectUrl;
    });
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

document.getElementById('icon')?.addEventListener('input', refreshBusinessIconPreview);

// ── Business icon image gallery ──────────────────────────────────────────────

function initBusinessIconGallery() {
    const gallery = document.getElementById('business-icon-gallery');
    if (!gallery) { return; }

    fetch('{{ route("admin.businesses.icons") }}', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(icons => {
        if (!icons.length) {
            gallery.innerHTML = '<span class="text-xs text-muted-foreground italic self-center">No custom icons yet</span>';
            return;
        }

        const current = document.getElementById('business-icon-image-path').value;
        gallery.innerHTML = icons.map(ic => `
            <div class="relative group" data-icon-wrapper="${ic.path}">
                <button type="button" data-path="${ic.path}" data-url="${ic.url}"
                    class="business-icon-thumb w-10 h-10 rounded-md border-2 ${ic.path === current ? 'border-primary' : 'border-transparent'} hover:border-primary overflow-hidden bg-white flex items-center justify-center p-0.5 transition-colors"
                    title="${ic.path.split('/').pop()}">
                    <img src="${ic.url}" class="w-full h-full object-contain" alt="">
                </button>
                <button type="button" data-delete-path="${ic.path}"
                    class="business-icon-delete absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] leading-none flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-opacity shadow"
                    title="Delete this custom icon">✕</button>
            </div>
        `).join('');

        gallery.querySelectorAll('.business-icon-thumb').forEach(btn => {
            btn.addEventListener('click', () => selectBusinessIcon(btn.dataset.path, btn.dataset.url));
        });
        gallery.querySelectorAll('.business-icon-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                deleteBusinessIcon(btn.dataset.deletePath, btn.closest('[data-icon-wrapper]'));
            });
        });

        if (current) {
            const match = icons.find(ic => ic.path === current);
            if (match) { selectBusinessIcon(match.path, match.url); }
        }
    })
    .catch(() => {
        gallery.innerHTML = '<span class="text-xs text-red-400 italic self-center">Failed to load icons</span>';
    });
}

function selectBusinessIcon(path, url) {
    document.getElementById('business-icon-image-path').value = path;

    const preview = document.getElementById('business-icon-image-preview');
    const previewImg = document.getElementById('business-icon-image-preview-img');
    const previewName = document.getElementById('business-icon-image-name');
    if (preview && previewImg) {
        previewImg.src = url;
        if (previewName) { previewName.textContent = path.split('/').pop(); }
        preview.classList.remove('hidden');
        preview.classList.add('flex');
    }

    document.querySelectorAll('#business-icon-gallery .business-icon-thumb').forEach(btn => {
        btn.classList.toggle('border-primary', btn.dataset.path === path);
        btn.classList.toggle('border-transparent', btn.dataset.path !== path);
    });

    refreshBusinessIconPreview();
}

function clearBusinessIcon() {
    document.getElementById('business-icon-image-path').value = '';

    const preview = document.getElementById('business-icon-image-preview');
    if (preview) {
        preview.classList.add('hidden');
        preview.classList.remove('flex');
    }

    document.querySelectorAll('#business-icon-gallery .business-icon-thumb').forEach(btn => {
        btn.classList.remove('border-primary');
        btn.classList.add('border-transparent');
    });

    refreshBusinessIconPreview();
}

function addToBusinessIconGallery(path, url) {
    const gallery = document.getElementById('business-icon-gallery');
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
    btn.className = 'business-icon-thumb w-10 h-10 rounded-md border-2 border-transparent hover:border-primary overflow-hidden bg-white flex items-center justify-center p-0.5 transition-colors';
    btn.title = path.split('/').pop();
    btn.addEventListener('click', () => selectBusinessIcon(path, url));
    btn.innerHTML = `<img src="${url}" class="w-full h-full object-contain" alt="">`;

    const deleteBtn = document.createElement('button');
    deleteBtn.type = 'button';
    deleteBtn.dataset.deletePath = path;
    deleteBtn.className = 'business-icon-delete absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] leading-none flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-opacity shadow';
    deleteBtn.title = 'Delete this custom icon';
    deleteBtn.textContent = '✕';
    deleteBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        deleteBusinessIcon(path, wrapper);
    });

    wrapper.appendChild(btn);
    wrapper.appendChild(deleteBtn);
    gallery.prepend(wrapper);
}

async function deleteBusinessIcon(path, wrapperEl) {
    if (!confirm('Delete this custom icon? This cannot be undone.')) { return; }

    const requestDelete = async (force = false) => {
        const res = await fetch('{{ route("admin.businesses.icons.delete") }}', {
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
            const proceed = confirm(`This icon is currently used by ${data.in_use} item(s). Deleting it will revert them to their business type's stock icon. Delete anyway?`);
            if (!proceed) { return; }
            data = await requestDelete(true);
        }

        if (!data.deleted) { return; }

        if (wrapperEl) { wrapperEl.remove(); }

        if (document.getElementById('business-icon-image-path').value === path) {
            clearBusinessIcon();
        }

        const gallery = document.getElementById('business-icon-gallery');
        if (gallery && !gallery.querySelector('.business-icon-thumb')) {
            gallery.innerHTML = '<span class="text-xs text-muted-foreground italic self-center">No custom icons yet</span>';
        }
    } catch {
        alert('Failed to delete icon.');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    initBusinessIconGallery();

    const iconUploadInput = document.getElementById('business-icon-image-input');
    if (iconUploadInput) {
        iconUploadInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) { return; }
            const statusEl = document.getElementById('business-icon-upload-status');
            if (statusEl) { statusEl.textContent = 'Uploading…'; }

            const fd = new FormData();
            fd.append('icon', file);
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            try {
                const res = await fetch('{{ route("admin.businesses.icons.upload") }}', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.path && data.url) {
                    selectBusinessIcon(data.path, data.url);
                    addToBusinessIconGallery(data.path, data.url);
                    if (statusEl) { statusEl.textContent = 'Uploaded!'; }
                    setTimeout(() => { if (statusEl) { statusEl.textContent = ''; } }, 2000);
                }
            } catch {
                if (statusEl) { statusEl.textContent = 'Upload failed.'; }
            }
            iconUploadInput.value = '';
        });
    }

    const iconClearBtn = document.getElementById('business-icon-image-clear');
    if (iconClearBtn) {
        iconClearBtn.addEventListener('click', clearBusinessIcon);
    }
});

const adminMapStyles = {
    'standard':  'mapbox://styles/mapbox/standard',
    'satellite': 'mapbox://styles/mapbox/satellite-streets-v12',
    'terrain':   'mapbox://styles/mapbox/outdoors-v12',
    'outdoors':  'mapbox://styles/mapbox/navigation-day-v1',
};

function switchMapStyle(mapType) {
    if (!window.coordinateMap) { return; }
    window.coordinateMap.setStyle(adminMapStyles[mapType] || adminMapStyles['standard']);
    document.querySelectorAll('.admin-layer-card').forEach(function (b) {
        b.classList.toggle('active', b.dataset.mapType === mapType);
    });
    document.getElementById('admin-layers-dropdown').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('admin-layers-toggle');
    var dropdown = document.getElementById('admin-layers-dropdown');
    if (toggle && dropdown) {
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', function (e) {
            if (!dropdown.contains(e.target) && !toggle.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
        document.querySelectorAll('.admin-layer-card').forEach(function (btn) {
            btn.addEventListener('click', function () {
                switchMapStyle(this.dataset.mapType);
            });
        });
    }
});
</script>

<style>
.search-result-item { transition: background-color 0.15s ease; }
.search-result-item:hover { background-color: hsl(var(--muted)); }

.admin-layer-card {
    position: relative; cursor: pointer; border-radius: 0.5rem;
    overflow: hidden; border: 2px solid transparent;
    display: flex; flex-direction: column; align-items: center;
    transition: all 0.2s; background: none; padding: 0;
}
.admin-layer-card:hover { border-color: #93C5FD; }
.admin-layer-card.active { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
.admin-layer-preview { width: 100%; height: 70px; border-radius: 0.375rem; overflow: hidden; }
.admin-layer-preview img { width: 100%; height: 100%; object-fit: cover; display: block; }
.admin-layer-label { display: block; font-size: 0.75rem; font-weight: 500; color: #374151; text-align: center; margin-top: 0.5rem; padding: 0 0.25rem 0.375rem; }
.admin-layer-check { position: absolute; top: 4px; right: 4px; width: 20px; height: 20px; color: white; background: #2563EB; border-radius: 50%; padding: 2px; display: none; }
.admin-layer-card.active .admin-layer-check { display: block; }
</style>
