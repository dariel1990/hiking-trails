{{--
    Shared map script for the tour page and its embeddable map.
    Expects $tour (stops eager loaded) and $mapboxToken.
    Optional $showPopups (bool): open a popup with the stop's details on marker click
    (always on for Christmas event tours).
    The map renders into #tour-map. Optional elements it wires up when present:
    #tour-layers-toggle / #tour-layers-dropdown / .tour-layer-card (style switcher),
    #tour-map-reset (reset view), .stop-card / #stop-card-{i} (stop list).
--}}
@php
// Roaming activities have no map pin, so marker numbers count only the other stops.
$stopNumber = 0;
$tourStopsData = $tour->stops->values()->map(fn ($s, $i) => [
    'index' => $i,
    'number' => $s->is_roaming ? null : ++$stopNumber,
    'label' => $s->displayName(),
    'coords' => $s->coordinates(),
    'time' => $s->estimated_visit_time,
    'note' => $s->driving_notes,
    'description' => $s->description,
    'icon' => $s->icon,
    'iconImage' => $s->icon_image_url,
    'image' => $s->image_url,
])->values();
@endphp

<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>
<script>
mapboxgl.accessToken = @json($mapboxToken);

const tourStops = @json($tourStopsData);
const drivingRoute = @json($tour->driving_route_coordinates);
const tourIconImage = @json($tour->icon_image_url);
// Guard against bad data (e.g. a stray non-emoji string saved to the tour's icon field)
// blowing out the small icon slot in map marker labels — cap to a few characters.
const tourIconEmoji = (@json($tour->tour_icon) || '📍').slice(0, 4);
const isChristmasEvent = @json($tour->isChristmasEvent());
const showStopPopups = @json(($showPopups ?? false) || $tour->isChristmasEvent());
const usesCooperativeGestures = @json($showPopups ?? false);

const ROUTE_COLOR = isChristmasEvent ? '#B3202A' : '#2C5F5D';

const map = new mapboxgl.Map({
    container: 'tour-map',
    style: 'mapbox://styles/mapbox/outdoors-v12',
    center: [-127.17, 54.78],
    zoom: 9,
    cooperativeGestures: usesCooperativeGestures,
});

map.addControl(new mapboxgl.NavigationControl(), 'top-right');

const activeMarkers = [];
const endpointMarkers = [];
let stopPopup = null;
let initialBounds = null;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

/**
 * A small white chevron drawn on a canvas, placed along the route line to
 * show the direction of travel. Points right; Mapbox rotates it to the line.
 */
function addRouteArrowImage() {
    if (map.hasImage('route-arrow')) { return; }
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
    map.addImage('route-arrow', ctx.getImageData(0, 0, size, size), { pixelRatio: 2 });
}

function addRouteLayers() {
    if (!drivingRoute || drivingRoute.length < 2) { return; }

    if (!map.getSource('tour-route')) {
        map.addSource('tour-route', {
            type: 'geojson',
            data: { type: 'Feature', geometry: { type: 'LineString', coordinates: drivingRoute } },
        });
    }

    if (!isChristmasEvent) {
        if (!map.getLayer('tour-route-line')) {
            map.addLayer({
                id: 'tour-route-line',
                type: 'line',
                source: 'tour-route',
                paint: { 'line-color': ROUTE_COLOR, 'line-width': 3, 'line-dasharray': [2, 1] },
            });
        }
        return;
    }

    // Christmas event route: white casing, solid red line, direction arrows.
    if (!map.getLayer('tour-route-casing')) {
        map.addLayer({
            id: 'tour-route-casing',
            type: 'line',
            source: 'tour-route',
            layout: { 'line-join': 'round', 'line-cap': 'round' },
            paint: { 'line-color': '#ffffff', 'line-width': 8, 'line-opacity': 0.9 },
        });
    }
    if (!map.getLayer('tour-route-line')) {
        map.addLayer({
            id: 'tour-route-line',
            type: 'line',
            source: 'tour-route',
            layout: { 'line-join': 'round', 'line-cap': 'round' },
            paint: { 'line-color': ROUTE_COLOR, 'line-width': 4 },
        });
    }
    addRouteArrowImage();
    if (!map.getLayer('tour-route-arrows')) {
        map.addLayer({
            id: 'tour-route-arrows',
            type: 'symbol',
            source: 'tour-route',
            layout: {
                'symbol-placement': 'line',
                'symbol-spacing': 70,
                'icon-image': 'route-arrow',
                'icon-size': 0.55,
                'icon-allow-overlap': true,
                'icon-ignore-placement': true,
                'icon-rotation-alignment': 'map',
            },
        });
    }
}

function addEndpointMarkers() {
    endpointMarkers.forEach((m) => m.remove());
    endpointMarkers.length = 0;
    if (!isChristmasEvent || !drivingRoute || drivingRoute.length < 2) { return; }

    const endpoints = [
        { lngLat: drivingRoute[0], icon: '🎅', text: 'Start', anchor: 'bottom-right' },
        { lngLat: drivingRoute[drivingRoute.length - 1], icon: '🏁', text: 'Finish', anchor: 'top-left' },
    ];
    endpoints.forEach((point) => {
        const el = document.createElement('div');
        el.className = 'route-endpoint';
        el.innerHTML = `<span class="route-endpoint-icon">${point.icon}</span>${point.text}`;
        endpointMarkers.push(
            new mapboxgl.Marker({ element: el, anchor: point.anchor, offset: [0, 0] })
                .setLngLat(point.lngLat)
                .addTo(map)
        );
    });
}

function addRouteAndMarkers() {
    addRouteLayers();
    addEndpointMarkers();

    activeMarkers.forEach(m => m.remove());
    activeMarkers.length = 0;

    const bounds = new mapboxgl.LngLatBounds();
    let hasCoords = false;

    tourStops.forEach((stop, i) => {
        if (!stop.coords || stop.coords.length < 2) { return; }

        const [lat, lng] = stop.coords;
        const lngLat = [lng, lat];

        const el = document.createElement('div');
        el.className = 'tour-marker';
        el.id = 'tour-marker-' + i;
        el.title = stop.label;
        const iconHtml = tourIconImage
            ? `<img src="${escapeHtml(tourIconImage)}" alt="" class="tour-marker-icon">`
            : `<span class="tour-marker-icon tour-marker-icon-emoji">${escapeHtml(tourIconEmoji)}</span>`;

        // A stop with its own icon (e.g. 🐴 wagon rides) shows it in the badge
        // instead of its number, and drops the tour icon from its label.
        let badgeHtml = String(stop.number ?? i + 1);
        if (stop.iconImage) {
            badgeHtml = `<img src="${escapeHtml(stop.iconImage)}" alt="" class="tour-marker-badge-img">`;
            el.classList.add('has-icon');
        } else if (stop.icon) {
            badgeHtml = `<span class="tour-marker-badge-emoji">${escapeHtml(stop.icon)}</span>`;
            el.classList.add('has-icon');
        }
        const labelIconHtml = el.classList.contains('has-icon') ? '' : iconHtml;

        el.innerHTML = `
            <div class="tour-marker-badge">${badgeHtml}</div>
            <div class="tour-marker-label">${labelIconHtml}<span>${escapeHtml(stop.label)}</span></div>
        `;
        el.addEventListener('click', (e) => {
            e.stopPropagation();
            focusStop(i, stop.coords);
        });

        const marker = new mapboxgl.Marker({ element: el, anchor: 'center' })
            .setLngLat(lngLat)
            .addTo(map);

        activeMarkers.push(marker);
        bounds.extend(lngLat);
        hasCoords = true;
    });

    // Event routes (or routes without located stops) frame the whole route too.
    if (drivingRoute && drivingRoute.length > 1 && (isChristmasEvent || !hasCoords)) {
        drivingRoute.forEach((c) => bounds.extend(c));
        hasCoords = true;
    }

    if (hasCoords) {
        initialBounds = bounds;
        fitToTour(0);
    }
    layoutLabels();
}

function fitToTour(duration = 600) {
    if (!initialBounds) { return; }
    const isSmallScreen = window.innerWidth < 768;
    const padding = isSmallScreen
        ? { top: 40, bottom: 30, left: 30, right: 50 }
        : { top: 60, bottom: 50, left: 50, right: 70 };
    map.fitBounds(initialBounds, { padding, maxZoom: 17, duration });
}

/**
 * Place each stop's name on the left or right of its badge. A stop whose
 * nearest neighbour sits to its right puts its name on the left (and vice
 * versa), so side-by-side stops spread their names outwards. If the preferred
 * side would collide with another label, the other side is used, then the name
 * is nudged up/down as a last resort. Stops whose badges overlap at the current
 * zoom also split left/right. Only when every spot is blocked is the label
 * hidden; hover/select still reveals it. The selected stop is placed first.
 */
function layoutLabels() {
    const badgeRadius = 17;
    const badgeCore = 6;
    const tuck = 9;
    const gap = 3;
    const neighbourRange = 160;
    const container = map.getContainer();
    const viewWidth = container.clientWidth;
    const viewHeight = container.clientHeight;
    const intersects = (a, b) => a.x1 < b.x2 && a.x2 > b.x1 && a.y1 < b.y2 && a.y2 > b.y1;

    const items = activeMarkers.map((marker) => {
        const el = marker.getElement();
        const p = map.project(marker.getLngLat());
        const label = el.querySelector('.tour-marker-label');
        return {
            el, p, label,
            width: label ? label.offsetWidth : 0,
            height: label ? label.offsetHeight : 0,
            // Only the badge's core blocks labels, so a name may brush past a
            // neighbouring badge's edge instead of being pushed away from it.
            badge: { x1: p.x - badgeCore, x2: p.x + badgeCore, y1: p.y - badgeCore, y2: p.y + badgeCore },
        };
    });

    const distance = (a, b) => Math.hypot(a.p.x - b.p.x, a.p.y - b.p.y);
    // Badges this close practically sit on top of each other at the current zoom.
    const overlapRange = badgeRadius * 2;

    const nearestNeighbour = (it) => {
        let nearest = null;
        let nearestDist = neighbourRange;
        items.forEach((other) => {
            if (other === it) { return; }
            const dist = distance(it, other);
            if (dist < nearestDist) { nearest = other; nearestDist = dist; }
        });
        return nearest;
    };

    const taken = items.map((it) => ({ rect: it.badge, owner: it }));
    const ordered = [...items].sort((a, b) => b.el.classList.contains('active') - a.el.classList.contains('active'));

    ordered.forEach((it) => {
        if (!it.label) { return; }
        const w = it.width;
        const h = it.height;
        const nudge = h / 2 + 3;

        const neighbour = nearestNeighbour(it);
        const isStacked = neighbour && distance(it, neighbour) < overlapRange;

        // Spread neighbouring stops outwards: the western one puts its name on
        // the left, the eastern one on the right. For stops stacked almost on top
        // of each other, the upper one breaks a tie by taking the left.
        const neighbourIsEast = neighbour && (
            neighbour.p.x > it.p.x + 1
            || (isStacked && Math.abs(neighbour.p.x - it.p.x) <= 1 && neighbour.p.y > it.p.y)
        );
        const sides = neighbourIsEast ? ['left', 'right'] : ['right', 'left'];
        // Nudging up/down is only a last resort when neither side has room.
        const nudges = [0, -nudge, nudge, -nudge * 2, nudge * 2];

        // Offsets of the label's top-left corner from the badge centre.
        const makeCandidate = (side, dy) => {
            const x = side === 'right' ? tuck : -tuck - w;
            const y = -h / 2 + dy;
            const box = { x1: it.p.x + x, x2: it.p.x + x + w, y1: it.p.y + y, y2: it.p.y + y + h };
            return {
                name: side, x, y, nudged: dy !== 0, box,
                rect: { x1: box.x1 - gap, x2: box.x2 + gap, y1: box.y1 - gap, y2: box.y2 + gap },
            };
        };
        // Stacked stops keep their own side (nudging if needed) so the two
        // names never cross over. If their side has no room (e.g. map edge),
        // they share the neighbour's side, nudged away from it (upper one up,
        // lower one down). Other stops try both sides before nudging.
        const awayFromNeighbour = isStacked && it.p.y > neighbour.p.y ? 1 : -1;
        const sharedSideNudges = [awayFromNeighbour * nudge, awayFromNeighbour * nudge * 2];
        const candidates = isStacked
            ? [
                ...nudges.map((dy) => makeCandidate(sides[0], dy)),
                ...sharedSideNudges.map((dy) => makeCandidate(sides[1], dy)),
            ]
            : nudges.flatMap((dy) => sides.map((side) => makeCandidate(side, dy)));

        // Labels keep a small gap from other labels but may sit right up against
        // a badge's core. Badges sitting under this one are ignored entirely.
        const isFree = (c) => taken.every((t) => {
            if (t.owner === it || (t.owner && distance(it, t.owner) < overlapRange)) { return true; }
            return !intersects(t.owner ? c.box : c.rect, t.rect);
        });
        const isInView = (rect) => rect.x1 >= 0 && rect.x2 <= viewWidth && rect.y1 >= 0 && rect.y2 <= viewHeight;

        const choice = candidates.find((c) => isFree(c) && isInView(c.box))
            ?? candidates.find((c) => isFree(c))
            ?? null;

        it.el.classList.toggle('label-hidden', choice === null);
        const placed = choice ?? candidates[0];
        it.el.classList.toggle('label-left', placed.name === 'left');
        it.el.classList.toggle('label-detached', placed.nudged);

        it.label.style.left = (badgeRadius + placed.x) + 'px';
        it.label.style.top = (badgeRadius + placed.y) + 'px';
        it.label.style.right = 'auto';
        it.label.style.transform = 'none';

        if (choice) { taken.push({ rect: choice.rect, owner: null }); }
    });
}

map.on('load', addRouteAndMarkers);
map.on('moveend', layoutLabels);
map.on('resize', layoutLabels);

// Reset view
document.getElementById('tour-map-reset')?.addEventListener('click', () => {
    stopPopup?.remove();
    focusStop(-1, null);
    fitToTour();
});

// Map style switcher
const layersToggle = document.getElementById('tour-layers-toggle');
const layersDropdown = document.getElementById('tour-layers-dropdown');

if (layersToggle && layersDropdown) {
    layersToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        layersDropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
        if (!layersToggle.contains(e.target) && !layersDropdown.contains(e.target)) {
            layersDropdown.classList.add('hidden');
        }
    });

    document.querySelectorAll('.tour-layer-card').forEach(btn => {
        btn.addEventListener('click', () => {
            const style = btn.dataset.style;
            if (!style) { return; }
            document.querySelectorAll('.tour-layer-card').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            layersDropdown.classList.add('hidden');
            map.setStyle(style);
            map.once('styledata', addRouteAndMarkers);
        });
    });
}

function openStopPopup(index) {
    stopPopup?.remove();
    const stop = tourStops[index];
    if (!stop || !stop.coords) { return; }
    const [lat, lng] = stop.coords;
    const iconHtml = stop.iconImage
        ? `<img src="${escapeHtml(stop.iconImage)}" alt="" class="tour-popup-icon-img">`
        : (stop.icon ? `<span class="tour-popup-icon">${escapeHtml(stop.icon)}</span>` : `<span class="tour-popup-number">${stop.number ?? index + 1}</span>`);
    const directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
    const html = `
        ${stop.image ? `<img src="${escapeHtml(stop.image)}" alt="" class="tour-popup-photo">` : ''}
        <div class="tour-popup-head">${iconHtml}<h3>${escapeHtml(stop.label)}</h3></div>
        ${stop.time ? `<p class="tour-popup-time">${escapeHtml(stop.time)}</p>` : ''}
        ${stop.note ? `<p class="tour-popup-note">${escapeHtml(stop.note)}</p>` : ''}
        ${stop.description ? `<p>${escapeHtml(stop.description)}</p>` : ''}
        <a href="${directionsUrl}" target="_blank" rel="noopener" class="tour-popup-link">Get directions →</a>
    `;
    stopPopup = new mapboxgl.Popup({ className: 'tour-popup', offset: 22, maxWidth: '280px' })
        .setLngLat([lng, lat])
        .setHTML(html)
        .addTo(map);
}

function focusStop(index, coords) {
    const card = document.getElementById('stop-card-' + index);

    document.querySelectorAll('.stop-card').forEach((c) => {
        c.classList.toggle('active', c === card);
    });
    document.querySelectorAll('.tour-marker').forEach((m) => {
        const i = Number(m.id.replace('tour-marker-', ''));
        m.classList.toggle('active', i === index);
        // Mapbox positions this element directly (no per-marker wrapper), so
        // z-index must be set on it, not a parent, to beat sibling markers.
        m.style.zIndex = i === index ? '10' : '';
    });
    layoutLabels();

    // Scroll the active stop card into view
    if (card) { card.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }

    if (!coords || coords.length < 2) { return; }
    if (showStopPopups) { openStopPopup(index); }
    const [lat, lng] = coords;
    map.flyTo({ center: [lng, lat], zoom: Math.max(map.getZoom(), 16), duration: 800 });
}
</script>
