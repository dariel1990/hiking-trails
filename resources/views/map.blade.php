@extends('layouts.public')

@section('title', 'Interactive Trail Map')

@section('content')
<div class="relative h-screen">
    <!-- Main Map Container -->
    <div id="main-map" class="absolute inset-0 z-10"></div>
    
    <!-- Map Controls Panel -->
    <div class="absolute top-4 left-4 z-30 bg-white rounded-lg shadow-lg p-4 space-y-4 w-64">
        <!-- Season Toggle -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Season View</label>
            <div class="flex space-x-2">
                <button id="summer-btn" class="season-btn active bg-green-500 text-white px-3 py-1 rounded text-sm" data-season="summer">
                    Summer
                </button>
                <button id="winter-btn" class="season-btn bg-gray-300 text-gray-700 px-3 py-1 rounded text-sm" data-season="winter">
                    Winter
                </button>
            </div>
        </div>

        <!-- Activity Type Filters -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Show Activities</label>
            <div class="space-y-2">
                <label class="flex items-center">
                    <input type="checkbox" class="activity-filter h-4 w-4 text-primary-600 border-gray-300 rounded" 
                           data-activity="hiking" checked>
                    <span class="ml-2 text-sm text-gray-700">Hiking Trails</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" class="activity-filter h-4 w-4 text-blue-600 border-gray-300 rounded" 
                           data-activity="fishing">
                    <span class="ml-2 text-sm text-gray-700">Fishing Spots</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" class="activity-filter h-4 w-4 text-orange-600 border-gray-300 rounded" 
                           data-activity="camping">
                    <span class="ml-2 text-sm text-gray-700">Camping Areas</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" class="activity-filter h-4 w-4 text-purple-600 border-gray-300 rounded" 
                           data-activity="viewpoint">
                    <span class="ml-2 text-sm text-gray-700">Viewpoints</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" class="activity-filter h-4 w-4 text-pink-600 border-gray-300 rounded" 
                        data-activity="highlights" checked>
                    <span class="ml-2 text-sm text-gray-700">Points of Interest</span>
                </label>
            </div>
        </div>

        <!-- Difficulty Filter -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Difficulty</label>
            <select id="difficulty-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option value="">All Levels</option>
                <option value="1">1 - Very Easy</option>
                <option value="2">2 - Easy</option>
                <option value="3">3 - Moderate</option>
                <option value="4">4 - Hard</option>
                <option value="5">5 - Very Hard</option>
            </select>
        </div>

        <!-- Distance Filter -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Distance</label>
            <select id="distance-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                <option value="">Any Distance</option>
                <option value="0-5">Under 5km</option>
                <option value="5-10">5-10km</option>
                <option value="10-20">10-20km</option>
                <option value="20+">Over 20km</option>
            </select>
        </div>

        <!-- Clear Filters -->
        <button id="clear-filters" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-md text-sm transition-colors">
            Clear All Filters
        </button>

        <!-- Legend -->
        <div class="border-t pt-4">
            <h4 class="font-medium text-gray-900 text-sm mb-2">Legend</h4>
            <div class="space-y-1 text-xs">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span>Hiking Trails</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                    <span>Fishing Spots</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-orange-500 rounded-full mr-2"></div>
                    <span>Camping Areas</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                    <span>Viewpoints</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-pink-500 rounded-full mr-2"></div>
                    <span>Points of Interest</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Trail Info Panel (Hidden by default) -->
    <div id="trail-info-panel" class="absolute top-4 right-4 z-30 bg-white rounded-lg shadow-lg w-80 hidden">
        <div id="trail-info-content" class="p-6">
            <!-- Dynamic content will be loaded here -->
        </div>
    </div>
</div>

@push('scripts')
<script>
    class EnhancedTrailMap {
        constructor() {
            this.map = null;
            this.currentSeason = 'summer';
            this.activeFilters = ['hiking', 'highlights'];
            this.baseLayers = {};
            this.overlayLayers = {};
            this.routeLayer = null;  // Add this line
            this.allTrails = [];
            this.highlightedRoute = null;
            this.init();

            window.trailMap = this;
        }

        init() {
            // Initialize map
            this.map = L.map('main-map', {
                zoomControl: false
            }).setView([49.2827, -122.7927], 10);

            // Add zoom control to bottom right
            L.control.zoom({
                position: 'bottomright'
            }).addTo(this.map);

            // Base layers for seasonal switching
            this.baseLayers = {
                'summer': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }),
                'winter': L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                    attribution: 'Map data: © OpenStreetMap, SRTM | Map style: © OpenTopoMap'
                })
            };

            // Activity overlay layers
            this.overlayLayers = {
                'hiking': L.layerGroup(),
                'fishing': L.layerGroup(),
                'camping': L.layerGroup(),
                'viewpoint': L.layerGroup(),
                'highlights': L.layerGroup()
            };

            // Add default base layer
            this.baseLayers[this.currentSeason].addTo(this.map);

            this.setupEventListeners();
            this.loadTrails();
        }

        setupEventListeners() {
            // Season switching
            document.querySelectorAll('.season-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const season = e.target.dataset.season;
                    this.switchSeason(season);
                });
            });

            // Activity filtering
            document.querySelectorAll('.activity-filter').forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    this.updateFilters();
                });
            });

            // Other filters
            document.getElementById('difficulty-filter').addEventListener('change', () => {
                this.updateFilters();
            });

            document.getElementById('distance-filter').addEventListener('change', () => {
                this.updateFilters();
            });

            // Clear filters
            document.getElementById('clear-filters').addEventListener('click', () => {
                this.clearFilters();
            });
        }

        // Add this function to your EnhancedTrailMap class
        getDistanceColor(distance) {
            if (distance <= 5) return '#10B981';      // Green - Short trails
            if (distance <= 10) return '#F59E0B';     // Orange - Medium trails  
            if (distance <= 20) return '#EF4444';     // Red - Long trails
            return '#7C2D12';                         // Dark Red - Very long trails
        }

        addTrailRoute(trail) {
            if (trail.route_coordinates && trail.route_coordinates.length > 0) {
                const routeColor = this.getDistanceColor(trail.distance);
                
                const route = L.polyline(trail.route_coordinates, {
                    color: routeColor,
                    weight: 4,
                    opacity: 0.8,
                    dashArray: trail.status === 'seasonal' ? '10, 5' : null
                }).addTo(this.map);

                route.bindPopup(`
                    <b>${trail.name}</b><br>
                    ${trail.distance}km trail route<br>
                    <a href="/trails/${trail.id}">View Details</a>
                `);

                return route;
            }
            return null;
        }

        switchSeason(season) {
            // Update UI
            document.querySelectorAll('.season-btn').forEach(btn => {
                btn.classList.remove('active', 'bg-green-500', 'text-white');
                btn.classList.add('bg-gray-300', 'text-gray-700');
            });
            
            const activeBtn = document.querySelector(`[data-season="${season}"]`);
            activeBtn.classList.remove('bg-gray-300', 'text-gray-700');
            activeBtn.classList.add('active', 'bg-green-500', 'text-white');

            // Switch base layer
            this.map.removeLayer(this.baseLayers[this.currentSeason]);
            this.currentSeason = season;
            this.baseLayers[this.currentSeason].addTo(this.map);

            // Reload trails for new season
            this.loadTrails();
        }

        updateFilters() {
            // Get active activity filters
            this.activeFilters = Array.from(
                document.querySelectorAll('.activity-filter:checked')
            ).map(cb => cb.dataset.activity);

            this.applyFilters();
        }

        // In your applyFilters function, make sure you have these variable declarations at the top:
        applyFilters() {
            // Clear all overlay layers
            Object.values(this.overlayLayers).forEach(layer => {
                this.map.removeLayer(layer);
                layer.clearLayers();
            });

            // Add these variable declarations
            const difficultyFilter = document.getElementById('difficulty-filter').value;
            const distanceFilter = document.getElementById('distance-filter').value;

            // Clear existing routes
            if (this.routeLayer) {
                this.map.removeLayer(this.routeLayer);
            }
            this.routeLayer = L.layerGroup().addTo(this.map);

            // Filter and display trails
            this.allTrails.forEach(trail => {
                // Apply difficulty filter
                if (difficultyFilter && trail.difficulty != difficultyFilter) {
                    return;
                }

                // Apply distance filter
                if (distanceFilter && !this.matchesDistanceFilter(trail.distance, distanceFilter)) {
                    return;
                }

                // Apply seasonal recommendation filter
                if (trail.seasonal_info && !trail.seasonal_info.recommended) {
                    return;
                }

                // Add trail route (REMOVED DUPLICATE - keep only this one)
                const route = this.addTrailRoute(trail);
                if (route) {
                    this.routeLayer.addLayer(route);
                }

                // Add highlights if enabled
                if (this.activeFilters.includes('highlights')) {
                    this.createHighlightMarkers(trail);
                }

                // Add markers for active activity types
                trail.activities.forEach(activity => {
                    if (this.activeFilters.includes(activity.type)) {
                        const marker = this.createTrailMarker(trail, activity);
                        this.overlayLayers[activity.type].addLayer(marker);
                    }
                });
            });

            // Add filtered layers to map
            this.activeFilters.forEach(activityType => {
                this.overlayLayers[activityType].addTo(this.map);
            });
        }

        matchesDistanceFilter(distance, filter) {
            switch(filter) {
                case '0-5': return distance <= 5;
                case '5-10': return distance > 5 && distance <= 10;
                case '10-20': return distance > 10 && distance <= 20;
                case '20+': return distance > 20;
                default: return true;
            }
        }

        createTrailMarker(trail, activity) {
            const colors = {
                hiking: '#10B981',
                fishing: '#3B82F6',
                camping: '#F59E0B',
                viewpoint: '#8B5CF6'
            };

            const icon = L.divIcon({
                html: `<div style="background-color: ${colors[activity.type] || '#6B7280'};" class="w-6 h-6 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-xs font-bold">${activity.icon || '•'}</div>`,
                className: 'custom-trail-marker',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            const marker = L.marker(trail.coordinates, { icon })
                .bindPopup(this.createPopupContent(trail));

            // Show trail info panel on click
            marker.on('click', () => {
                this.showTrailInfo(trail);
            });

            return marker;
        }

        createHighlightMarkers(trail) {
            if (!trail.highlights || trail.highlights.length === 0) return;

            trail.highlights.forEach(highlight => {
                const icon = L.divIcon({
                    html: `<div style="background-color: ${highlight.color};" class="w-8 h-8 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-lg font-bold">${highlight.icon}</div>`,
                    className: 'custom-highlight-marker',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                const marker = L.marker(highlight.coordinates, { icon })
                    .bindPopup(this.createHighlightPopupContent(trail, highlight));

                // Add click event
                marker.on('click', () => {
                    this.showHighlightInfo(trail, highlight);
                });

                this.overlayLayers['highlights'].addLayer(marker);
            });
        }

        createHighlightPopupContent(trail, highlight) {
            return `
                <div class="max-w-xs">
                    <div class="flex items-center mb-2">
                        <div style="background-color: ${highlight.color};" class="w-8 h-8 rounded-full flex items-center justify-center text-white mr-2">
                            ${highlight.icon}
                        </div>
                        <h5 class="font-bold text-base">${highlight.name}</h5>
                    </div>
                    
                    <p class="text-xs text-gray-600 mb-2 capitalize">${highlight.type.replace('_', ' ')}</p>
                    
                    ${highlight.description ? `<p class="text-sm text-gray-700 mb-2">${highlight.description}</p>` : ''}
                    
                    <div class="text-xs text-gray-500 mb-2">
                        On trail: <a href="/trails/${trail.id}" class="text-primary-600 hover:underline">${trail.name}</a>
                    </div>
                    
                    <button onclick="window.trailMap.viewHighlight(${trail.id}, ${JSON.stringify(highlight.coordinates).replace(/"/g, '&quot;')})" 
                            class="bg-primary-500 text-white px-3 py-1 rounded text-xs hover:bg-primary-600 w-full">
                        Focus on Map
                    </button>
                </div>
            `;
        }

        showHighlightInfo(trail, highlight) {
            const panel = document.getElementById('trail-info-panel');
            const content = document.getElementById('trail-info-content');
            
            content.innerHTML = `
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center">
                        <div style="background-color: ${highlight.color};" class="w-10 h-10 rounded-full flex items-center justify-center text-white text-xl mr-3">
                            ${highlight.icon}
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">${highlight.name}</h3>
                            <p class="text-sm text-gray-600 capitalize">${highlight.type.replace('_', ' ')}</p>
                        </div>
                    </div>
                    <button onclick="this.closest('#trail-info-panel').classList.add('hidden')" 
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                ${highlight.description ? `
                    <div class="mb-4">
                        <p class="text-gray-700">${highlight.description}</p>
                    </div>
                ` : ''}
                
                <div class="border-t pt-4 mb-4">
                    <h4 class="font-semibold text-gray-900 mb-2">Located on Trail:</h4>
                    <div class="bg-gray-50 p-3 rounded">
                        <h5 class="font-medium text-gray-900">${trail.name}</h5>
                        <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                            <span>Distance: ${trail.distance}km</span>
                            <span>Difficulty: ${trail.difficulty}/5</span>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <a href="/trails/${trail.id}" 
                    class="block w-full bg-primary-600 hover:bg-primary-700 text-white text-center py-2 px-4 rounded-md font-medium transition-colors">
                        View Full Trail
                    </a>
                    <button onclick="window.trailMap.viewHighlight(${trail.id}, ${JSON.stringify(highlight.coordinates).replace(/"/g, '&quot;')})" 
                            class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-md font-medium transition-colors">
                        Center on Map
                    </button>
                </div>
            `;
            
            panel.classList.remove('hidden');
        }

        viewHighlight(trailId, coordinates) {
            this.map.closePopup();
            this.map.setView(coordinates, 16, {
                animate: true,
                duration: 1
            });
        }

        createPopupContent(trail) {
            const seasonalNote = trail.seasonal_info?.notes ? 
                `<div class="text-xs text-blue-600 mt-1">${trail.seasonal_info.notes}</div>` : '';

            const imageHtml = trail.preview_photo ? 
                `<img src="${trail.preview_photo}" alt="${trail.name}" class="w-full h-24 object-cover rounded mb-2">` : '';

            return `
                <div class="max-w-sm">
                    ${imageHtml}
                    <h5 class="font-bold text-lg mb-2">${trail.name}</h5>
                    <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                        <span><strong>Distance:</strong> ${trail.distance} km</span>
                        <span><strong>Difficulty:</strong> ${trail.difficulty}/5</span>
                        <span><strong>Elevation:</strong> ${trail.elevation_gain || 0}m</span>
                        <span><strong>Time:</strong> ${trail.estimated_time || 'N/A'}h</span>
                    </div>
                    ${seasonalNote}
                    <div class="mt-3">
                        <button onclick="window.trailMap.viewRoute(${trail.id})" class="bg-primary-500 text-white px-3 py-1 rounded text-sm hover:bg-primary-600 inline-block">
                            View Route
                        </button>
                    </div>
                </div>
            `;
        }

        viewRoute(trailId) {
            const trail = this.allTrails.find(t => t.id == trailId);
            if (!trail) return;

            // Close any open popups
            this.map.closePopup();

            // Clear existing route highlights
            if (this.highlightedRoute) {
                this.map.removeLayer(this.highlightedRoute);
            }

            // Check if trail has route coordinates
            if (!trail.route_coordinates || trail.route_coordinates.length === 0) {
                alert('Route data not available for this trail.');
                return;
            }

            // Create highlighted route
            this.highlightedRoute = L.polyline(trail.route_coordinates, {
                color: '#FF0000',
                weight: 6,
                opacity: 0.9,
                dashArray: '10, 5'
            }).addTo(this.map);

            // Zoom to route bounds with padding
            this.map.fitBounds(this.highlightedRoute.getBounds(), { 
                padding: [20, 20],
                maxZoom: 18
            });

            // Add route popup
            this.highlightedRoute.bindPopup(`
                <div class="text-center">
                    <b>${trail.name} Route</b><br>
                    <span class="text-sm">${trail.distance}km trail path</span><br>
                    <button onclick="window.trailMap.clearRoute()" class="mt-2 bg-gray-500 text-white px-2 py-1 rounded text-xs">
                        Clear Route
                    </button>
                </div>
            `).openPopup();
        }

        // Add method to clear highlighted route
        clearRoute() {
            if (this.highlightedRoute) {
                this.map.removeLayer(this.highlightedRoute);
                this.highlightedRoute = null;
            }
            this.map.closePopup();
        }

        showTrailInfo(trail) {
            const panel = document.getElementById('trail-info-panel');
            const content = document.getElementById('trail-info-content');
            
            content.innerHTML = `
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-gray-900">${trail.name}</h3>
                    <button onclick="this.closest('#trail-info-panel').classList.add('hidden')" 
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                ${trail.preview_photo ? `
                    <img src="${trail.preview_photo}" alt="${trail.name}" 
                         class="w-full h-40 object-cover rounded-lg mb-4">
                ` : `
                    <div class="w-full h-40 bg-gradient-to-br from-green-400 to-blue-600 rounded-lg mb-4 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                `}
                
                <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                    <div class="bg-blue-50 p-2 rounded text-center">
                        <div class="font-bold text-blue-600">${trail.distance}</div>
                        <div class="text-gray-600">km</div>
                    </div>
                    <div class="bg-green-50 p-2 rounded text-center">
                        <div class="font-bold text-green-600">${trail.elevation_gain || 0}</div>
                        <div class="text-gray-600">meters</div>
                    </div>
                    <div class="bg-yellow-50 p-2 rounded text-center">
                        <div class="font-bold text-yellow-600">${trail.estimated_time || 'N/A'}</div>
                        <div class="text-gray-600">hours</div>
                    </div>
                    <div class="bg-purple-50 p-2 rounded text-center">
                        <div class="font-bold text-purple-600">${trail.difficulty}</div>
                        <div class="text-gray-600">difficulty</div>
                    </div>
                </div>

                ${trail.seasonal_info?.notes ? `
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4">
                        <p class="text-sm text-blue-700">
                            <strong>Seasonal Note:</strong> ${trail.seasonal_info.notes}
                        </p>
                    </div>
                ` : ''}

                <div class="space-y-2 mb-4">
                    <div class="flex flex-wrap gap-1">
                        ${trail.activities.map(activity => 
                            `<span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">${activity.icon} ${activity.name}</span>`
                        ).join('')}
                    </div>
                </div>

                <a href="/trails/${trail.id}" 
                   class="block w-full bg-primary-600 hover:bg-primary-700 text-white text-center py-2 px-4 rounded-md font-medium transition-colors">
                    View Full Details
                </a>
            `;
            
            panel.classList.remove('hidden');
        }

        focusOnTrail(trail) {
            // Show trail info panel
            this.showTrailInfo(trail);
            
            // If trail has route, display it highlighted and fit to bounds
            if (trail.route_coordinates && trail.route_coordinates.length > 0) {
                // Clear existing highlighted route
                if (this.highlightedRoute) {
                    this.map.removeLayer(this.highlightedRoute);
                }
                
                // Create highlighted route
                this.highlightedRoute = L.polyline(trail.route_coordinates, {
                    color: '#FF0000',
                    weight: 6,
                    opacity: 0.9,
                    dashArray: '10, 5'
                }).addTo(this.map);
                
                // Fit map to route with maximum zoom
                this.map.fitBounds(this.highlightedRoute.getBounds(), { 
                    padding: [50, 50],
                    maxZoom: 18  // Maximum zoom level
                });
            } else {
                // If no route, just zoom to trail start coordinates at max zoom
                if (trail.coordinates) {
                    this.map.setView(trail.coordinates, 18, {  // Use zoom level 18 (max zoom)
                        animate: true,
                        duration: 1
                    });
                }
            }
            
            // Show highlights if available
            if (trail.highlights && trail.highlights.length > 0 && !this.activeFilters.includes('highlights')) {
                // Temporarily enable highlights filter
                const highlightsCheckbox = document.querySelector('.activity-filter[data-activity="highlights"]');
                if (highlightsCheckbox && !highlightsCheckbox.checked) {
                    highlightsCheckbox.checked = true;
                    this.updateFilters();
                }
            }
        }

        async loadTrails() {
            try {
                const params = new URLSearchParams({
                    season: this.currentSeason,
                    filters: this.activeFilters.join(',')
                });

                const response = await fetch(`/api/trails?${params}`);
                this.allTrails = await response.json();
                this.applyFilters();
                
                // Check if there's a trail parameter in URL and focus on it
                const urlParams = new URLSearchParams(window.location.search);
                const trailId = urlParams.get('trail');
                
                if (trailId) {
                    setTimeout(() => {
                        const trail = this.allTrails.find(t => t.id == trailId);
                        if (trail) {
                            this.focusOnTrail(trail);
                        }
                    }, 500);
                }
            } catch (error) {
                console.error('Error loading trails:', error);
            }
        }

        clearFilters() {
            // Reset checkboxes - keep hiking and highlights checked
            document.querySelectorAll('.activity-filter').forEach(cb => {
                cb.checked = cb.dataset.activity === 'hiking' || cb.dataset.activity === 'highlights';
            });

            // Reset selects
            document.getElementById('difficulty-filter').value = '';
            document.getElementById('distance-filter').value = '';

            // Update filters
            this.activeFilters = ['hiking', 'highlights'];
            this.applyFilters();
        }

         updateHighlightsCount() {
            const count = this.allTrails.reduce((total, trail) => {
                return total + (trail.highlights ? trail.highlights.length : 0);
            }, 0);
            
            // You can display this count somewhere in your UI
            console.log(`Total highlights: ${count}`);
        }
    }

    // Initialize map when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        const trailMap = new EnhancedTrailMap();
        
        // Check if there's a trail parameter in the URL
        const urlParams = new URLSearchParams(window.location.search);
        const trailId = urlParams.get('trail');
        
        if (trailId) {
            // Wait for trails to load, then focus on the specified trail
            setTimeout(() => {
                const trail = trailMap.allTrails.find(t => t.id == trailId);
                if (trail) {
                    trailMap.focusOnTrail(trail);
                }
            }, 1500);
        }
    });
</script>
@endpush
@endsection