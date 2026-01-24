// MapLibre 3D Trail Viewer
class Trail3DViewerMapLibre {
    constructor(containerId, trail, highlights = []) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        this.trail = trail;
        this.highlights = highlights;
        this.map = null;
        this.markers = [];
        this.activePopup = null;
        
        this.init();
    }

    init() {
        // Initialize MapLibre map
        this.map = new maplibregl.Map({
            container: this.containerId,
            style: {
                version: 8,
                sources: {
                    'osm-tiles': {
                        type: 'raster',
                        tiles: [
                            'https://tile.openstreetmap.org/{z}/{x}/{y}.png'
                        ],
                        tileSize: 256,
                        attribution: '© OpenStreetMap contributors'
                    },
                    'terrain-source': {
                        type: 'raster-dem',
                        url: 'https://demotiles.maplibre.org/terrain-tiles/tiles.json',
                        tileSize: 256
                    }
                },
                layers: [
                    {
                        id: 'osm-layer',
                        type: 'raster',
                        source: 'osm-tiles',
                        minzoom: 0,
                        maxzoom: 19
                    }
                ],
                terrain: {
                    source: 'terrain-source',
                    exaggeration: 1.5
                }
            },
            center: [this.trail.start_coordinates[1], this.trail.start_coordinates[0]],
            zoom: 13,
            pitch: 60,
            bearing: 0,
            antialias: true
        });

        // Add navigation controls
        this.map.addControl(new maplibregl.NavigationControl(), 'top-right');
        
        // Add fullscreen control
        this.map.addControl(new maplibregl.FullscreenControl(), 'top-right');

        // Wait for map to load
        this.map.on('load', () => {
            this.addTrailRoute();
            this.addTrailMarkers();
            this.addHighlightMarkers();
            this.flyToTrail();
        });
    }

    addTrailRoute() {
        if (!this.trail.route_coordinates || this.trail.route_coordinates.length === 0) {
            return;
        }

        // Convert coordinates to GeoJSON
        const routeGeoJSON = {
            type: 'Feature',
            properties: {},
            geometry: {
                type: 'LineString',
                coordinates: this.trail.route_coordinates.map(coord => [coord[1], coord[0]])
            }
        };

        // Add source
        this.map.addSource('trail-route', {
            type: 'geojson',
            data: routeGeoJSON
        });

        // Add layer with glow effect
        this.map.addLayer({
            id: 'trail-route-glow',
            type: 'line',
            source: 'trail-route',
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': '#10B981',
                'line-width': 8,
                'line-blur': 4,
                'line-opacity': 0.6
            }
        });

        // Add main line
        this.map.addLayer({
            id: 'trail-route-line',
            type: 'line',
            source: 'trail-route',
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': '#10B981',
                'line-width': 4
            }
        });
    }

    addTrailMarkers() {
        // Create start marker element
        const startEl = document.createElement('div');
        startEl.className = 'trail-marker-start';
        startEl.style.cssText = `
            background-color: #10B981;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            cursor: pointer;
        `;
        startEl.textContent = 'START';

        // Add start marker
        const startMarker = new maplibregl.Marker({ element: startEl })
            .setLngLat([this.trail.start_coordinates[1], this.trail.start_coordinates[0]])
            .setPopup(new maplibregl.Popup().setHTML(`
                <div style="font-weight: 600;">${this.trail.name}</div>
                <div style="font-size: 12px; color: #666;">Trail Start</div>
            `))
            .addTo(this.map);

        this.markers.push(startMarker);

        // Add end marker if different
        if (this.trail.end_coordinates && 
            JSON.stringify(this.trail.start_coordinates) !== JSON.stringify(this.trail.end_coordinates)) {
            
            const endEl = document.createElement('div');
            endEl.className = 'trail-marker-end';
            endEl.style.cssText = `
                background-color: #EF4444;
                color: white;
                padding: 8px 16px;
                border-radius: 20px;
                font-weight: bold;
                font-size: 12px;
                border: 2px solid white;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                cursor: pointer;
            `;
            endEl.textContent = 'END';

            const endMarker = new maplibregl.Marker({ element: endEl })
                .setLngLat([this.trail.end_coordinates[1], this.trail.end_coordinates[0]])
                .setPopup(new maplibregl.Popup().setHTML('<div style="font-weight: 600;">Trail End</div>'))
                .addTo(this.map);

            this.markers.push(endMarker);
        }
    }

    addHighlightMarkers() {
        if (!this.highlights || this.highlights.length === 0) {
            return;
        }

        this.highlights.forEach(highlight => {
            // Create marker element
            const el = document.createElement('div');
            el.className = 'highlight-marker';
            el.style.cssText = `
                width: 48px;
                height: 48px;
                background-color: ${highlight.color || '#6366f1'};
                border: 3px solid white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                transition: transform 0.2s;
            `;
            el.textContent = highlight.icon || '📍';
            
            // Hover effect
            el.addEventListener('mouseenter', () => {
                el.style.transform = 'scale(1.15)';
            });
            el.addEventListener('mouseleave', () => {
                el.style.transform = 'scale(1)';
            });

            // Create marker
            const marker = new maplibregl.Marker({ element: el })
                .setLngLat([highlight.coordinates[1], highlight.coordinates[0]])
                .addTo(this.map);

            // Add click handler
            el.addEventListener('click', () => {
                this.showHighlightPopup(highlight);
            });

            this.markers.push(marker);
        });
    }

    showHighlightPopup(highlight) {
        // Emit custom event for blade file to handle
        const event = new CustomEvent('highlightClicked', { 
            detail: highlight 
        });
        document.dispatchEvent(event);

        // Fly to highlight
        this.map.flyTo({
            center: [highlight.coordinates[1], highlight.coordinates[0]],
            zoom: 16,
            pitch: 60,
            duration: 2000,
            essential: true
        });
    }

    closeHighlightPopup() {
        // Emit close event
        const event = new CustomEvent('closeHighlightPopup');
        document.dispatchEvent(event);
    }

    focusHighlight(highlightName) {
        const highlight = this.highlights.find(h => h.name === highlightName);
        if (!highlight) return;
        
        this.map.flyTo({
            center: [highlight.coordinates[1], highlight.coordinates[0]],
            zoom: 16,
            pitch: 60,
            duration: 2000,
            essential: true
        });

        setTimeout(() => {
            this.showHighlightPopup(highlight);
        }, 2000);
    }

    flyToTrail() {
        if (this.trail.route_coordinates && this.trail.route_coordinates.length > 0) {
            // Calculate bounds
            const coordinates = this.trail.route_coordinates.map(coord => [coord[1], coord[0]]);
            const bounds = coordinates.reduce((bounds, coord) => {
                return bounds.extend(coord);
            }, new maplibregl.LngLatBounds(coordinates[0], coordinates[0]));

            this.map.fitBounds(bounds, {
                padding: { top: 50, bottom: 50, left: 50, right: 50 },
                pitch: 60,
                duration: 2000
            });
        } else {
            this.map.flyTo({
                center: [this.trail.start_coordinates[1], this.trail.start_coordinates[0]],
                zoom: 14,
                pitch: 60,
                duration: 2000
            });
        }
    }

    destroy() {
        if (this.map) {
            this.markers.forEach(marker => marker.remove());
            this.markers = [];
            this.map.remove();
            this.map = null;
        }
    }
}

// Make available globally
window.Trail3DViewerMapLibre = Trail3DViewerMapLibre;