// Simple Cesium loader without complex imports
class Trail3DViewer {
    constructor(containerId, trail) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        this.trail = trail;
        this.viewer = null;
        this.fullscreenBtn = null;
        this.loadCesium().then(() => {
            this.init();
        });
    }

    async loadCesium() {
        // Load Cesium from CDN if not already loaded (using older stable version)
        if (typeof Cesium === 'undefined') {
            await this.loadScript('https://cesium.com/downloads/cesiumjs/releases/1.95/Build/Cesium/Cesium.js');
        }
        
        // Set access token - REPLACE WITH YOUR ACTUAL TOKEN
        Cesium.Ion.defaultAccessToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiJiYTAyNDVhYS1lYTM4LTQ2ZWEtOWE3Ny1hYTc2ZGUyNzk2ODgiLCJpZCI6MzQwMTI5LCJpYXQiOjE3NTc1MTIxNzV9.E-sku2e3WtQ-Nx9tNPDwqPcBgAXsZ23Ov0aeIlB0GZY';
    }

    loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    init() {
        try {
            this.viewer = new Cesium.Viewer(this.containerId, {
                terrainProvider: Cesium.createWorldTerrain(),
                homeButton: false,
                sceneModePicker: false,
                baseLayerPicker: false,
                navigationHelpButton: false,
                animation: false,
                timeline: false,
                fullscreenButton: false,
                geocoder: false,
                infoBox: false,
                selectionIndicator: false
            });

            // Force canvas to fill container
            const canvas = this.viewer.canvas;
            canvas.style.width = '100%';
            canvas.style.height = '100%';
            
            // Force resize
            this.viewer.resize();

            if (this.viewer.cesiumWidget.creditContainer) {
                this.viewer.cesiumWidget.creditContainer.style.display = "none";
            }

            this.addTrailRoute();
            this.addTrailMarkers();
            this.flyToTrail();
            this.addCustomFullscreenButton();
            
        } catch (error) {
            console.error('Error initializing Cesium viewer:', error);
            this.initWithBasicTerrain();
        }
    }

    initWithBasicTerrain() {
        try {
            // Fallback initialization with basic terrain
            this.viewer = new Cesium.Viewer(this.containerId, {
                terrainProvider: new Cesium.EllipsoidTerrainProvider(),
                homeButton: false,
                sceneModePicker: false,
                baseLayerPicker: false,
                navigationHelpButton: false,
                animation: false,
                timeline: false,
                fullscreenButton: true,
                geocoder: false,
                infoBox: false,
                selectionIndicator: false
            });

            // Disable credits
            if (this.viewer.cesiumWidget.creditContainer) {
                this.viewer.cesiumWidget.creditContainer.style.display = "none";
            }

            this.addTrailRoute();
            this.addTrailMarkers();
            this.flyToTrail();
            this.addCustomFullscreenButton();
            
        } catch (error) {
            console.error('Failed to initialize even basic Cesium viewer:', error);
            throw error;
        }
    }

    addCustomFullscreenButton() {
        // Wait for viewer to be fully initialized
        setTimeout(() => {
            // Find the Cesium toolbar container
            const cesiumToolbar = this.container.querySelector('.cesium-viewer-toolbar');
            
            if (!cesiumToolbar) {
                console.warn('Cesium toolbar not found');
                return;
            }
            
            this.fullscreenBtn = document.createElement('button');
            this.fullscreenBtn.className = 'cesium-button cesium-toolbar-button';
            this.fullscreenBtn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M1.5 1a.5.5 0 0 0-.5.5v4a.5.5 0 0 1-1 0v-4A1.5 1.5 0 0 1 1.5 0h4a.5.5 0 0 1 0 1h-4zM10 .5a.5.5 0 0 1 .5-.5h4A1.5 1.5 0 0 1 16 1.5v4a.5.5 0 0 1-1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 1-.5-.5zM.5 10a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 0 14.5v-4a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a.5.5 0 0 1 0-1h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 1 .5-.5z"/>
                </svg>
            `;
            this.fullscreenBtn.title = 'Toggle Fullscreen';
            
            this.fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());
            
            // Add to toolbar
            cesiumToolbar.appendChild(this.fullscreenBtn);
            
            // Listen for fullscreen changes
            document.addEventListener('fullscreenchange', () => this.updateFullscreenButton());
            document.addEventListener('webkitfullscreenchange', () => this.updateFullscreenButton());
        }, 500);
    }

    toggleFullscreen() {
        if (!document.fullscreenElement && !document.webkitFullscreenElement) {
            if (this.container.requestFullscreen) {
                this.container.requestFullscreen();
            } else if (this.container.webkitRequestFullscreen) {
                this.container.webkitRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
        }
    }

    updateFullscreenButton() {
        if (!this.fullscreenBtn) return;
        
        const isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
        
        if (isFullscreen) {
            this.fullscreenBtn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M5.5 0a.5.5 0 0 1 .5.5v4A1.5 1.5 0 0 1 4.5 6h-4a.5.5 0 0 1 0-1h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 1 .5-.5zm5 0a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 10 4.5v-4a.5.5 0 0 1 .5-.5zM0 10.5a.5.5 0 0 1 .5-.5h4A1.5 1.5 0 0 1 6 11.5v4a.5.5 0 0 1-1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 1-.5-.5zm10 1a1.5 1.5 0 0 1 1.5-1.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 0-.5.5v4a.5.5 0 0 1-1 0v-4z"/>
                </svg>
            `;
            this.fullscreenBtn.title = 'Exit Fullscreen';
        } else {
            this.fullscreenBtn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M1.5 1a.5.5 0 0 0-.5.5v4a.5.5 0 0 1-1 0v-4A1.5 1.5 0 0 1 1.5 0h4a.5.5 0 0 1 0 1h-4zM10 .5a.5.5 0 0 1 .5-.5h4A1.5 1.5 0 0 1 16 1.5v4a.5.5 0 0 1-1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 1-.5-.5zM.5 10a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 0 14.5v-4a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a.5.5 0 0 1 0-1h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 1 .5-.5z"/>
                </svg>
            `;
            this.fullscreenBtn.title = 'Toggle Fullscreen';
        }
    }

    addTrailRoute() {
        if (!this.trail.route_coordinates || this.trail.route_coordinates.length === 0) {
            return;
        }

        // Convert coordinates to Cesium format
        const positions = this.trail.route_coordinates.map(coord => 
            Cesium.Cartesian3.fromDegrees(coord[1], coord[0])
        );

        // Add glowing trail line
        this.viewer.entities.add({
            name: 'Trail Route',
            polyline: {
                positions: positions,
                width: 8,
                clampToGround: true,
                material: new Cesium.PolylineGlowMaterialProperty({
                    glowPower: 0.3,
                    taperPower: 0.8,
                    color: Cesium.Color.LIME
                })
            }
        });
    }

    addTrailMarkers() {
        // Start marker
        this.viewer.entities.add({
            name: 'Trail Start',
            position: Cesium.Cartesian3.fromDegrees(
                this.trail.start_coordinates[1], 
                this.trail.start_coordinates[0],
                10 // Height offset
            ),
            billboard: {
                image: this.createMarkerCanvas('START', '#10B981'),
                verticalOrigin: Cesium.VerticalOrigin.BOTTOM,
                heightReference: Cesium.HeightReference.CLAMP_TO_GROUND,
                scale: 1.0
            }
        });

        // End marker if different
        if (this.trail.end_coordinates && 
            JSON.stringify(this.trail.start_coordinates) !== JSON.stringify(this.trail.end_coordinates)) {
            
            this.viewer.entities.add({
                name: 'Trail End',
                position: Cesium.Cartesian3.fromDegrees(
                    this.trail.end_coordinates[1], 
                    this.trail.end_coordinates[0],
                    10
                ),
                billboard: {
                    image: this.createMarkerCanvas('END', '#EF4444'),
                    verticalOrigin: Cesium.VerticalOrigin.BOTTOM,
                    heightReference: Cesium.HeightReference.CLAMP_TO_GROUND,
                    scale: 1.0
                }
            });
        }
    }

    createMarkerCanvas(text, color) {
        const canvas = document.createElement('canvas');
        canvas.width = 120;
        canvas.height = 40;
        const ctx = canvas.getContext('2d');
        
        // Draw rounded rectangle background
        ctx.beginPath();
        if (ctx.roundRect) {
            ctx.roundRect(0, 0, 120, 40, 20);
        } else {
            // Fallback for browsers without roundRect
            this.drawRoundedRect(ctx, 0, 0, 120, 40, 20);
        }
        ctx.fillStyle = color;
        ctx.fill();
        ctx.strokeStyle = '#FFFFFF';
        ctx.lineWidth = 2;
        ctx.stroke();
        
        // Draw text
        ctx.fillStyle = '#FFFFFF';
        ctx.font = 'bold 14px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(text, 60, 26);
        
        return canvas;
    }

    drawRoundedRect(ctx, x, y, width, height, radius) {
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.lineTo(x + width - radius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
        ctx.lineTo(x + width, y + height - radius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
        ctx.lineTo(x + radius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
        ctx.lineTo(x, y + radius);
        ctx.quadraticCurveTo(x, y, x + radius, y);
        ctx.closePath();
    }

    flyToTrail() {
        // Give the viewer a moment to fully initialize
        setTimeout(() => {
            if (this.trail.route_coordinates && this.trail.route_coordinates.length > 0) {
                // Calculate bounds from coordinates
                let west = 999, east = -999, north = -999, south = 999;
                this.trail.route_coordinates.forEach(coord => {
                    west = Math.min(west, coord[1]);
                    east = Math.max(east, coord[1]);
                    south = Math.min(south, coord[0]);
                    north = Math.max(north, coord[0]);
                });
                
                // Fly to the calculated bounds with padding
                this.viewer.camera.flyTo({
                    destination: Cesium.Rectangle.fromDegrees(west - 0.01, south - 0.01, east + 0.01, north + 0.01),
                    duration: 2.0
                });
            } else {
                // Single point view with better positioning
                this.viewer.camera.flyTo({
                    destination: Cesium.Cartesian3.fromDegrees(
                        this.trail.start_coordinates[1], 
                        this.trail.start_coordinates[0], 
                        3000
                    ),
                    orientation: {
                        heading: 0,
                        pitch: Cesium.Math.toRadians(-45),
                        roll: 0.0
                    },
                    duration: 2.0
                });
            }
        }, 500);
    }

    destroy() {
        if (this.viewer) {
            this.viewer.destroy();
            this.viewer = null;
        }
    }
}

// Polyfill for roundRect if not available (this is now handled in createMarkerCanvas)
if (!CanvasRenderingContext2D.prototype.roundRect) {
    CanvasRenderingContext2D.prototype.roundRect = function (x, y, w, h, r) {
        if (w < 2 * r) r = w / 2;
        if (h < 2 * r) r = h / 2;
        this.beginPath();
        this.moveTo(x+r, y);
        this.arcTo(x+w, y,   x+w, y+h, r);
        this.arcTo(x+w, y+h, x,   y+h, r);
        this.arcTo(x,   y+h, x,   y,   r);
        this.arcTo(x,   y,   x+w, y,   r);
        this.closePath();
        return this;
    };
}

// Make available globally
window.Trail3DViewer = Trail3DViewer;