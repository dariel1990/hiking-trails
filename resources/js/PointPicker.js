/**
 * PointPicker - Single point location picker for fishing lakes
 * Provides a simple draggable marker interface for selecting a single coordinate
 */
export class PointPicker {
    constructor(map, options = {}) {
        this.map = map;
        this.marker = null;
        this.enabled = false;
        this.onPointSet = options.onPointSet || (() => {});
        
        // Hidden inputs for coordinates
        this.latInput = options.latInput || document.getElementById('point_latitude');
        this.lngInput = options.lngInput || document.getElementById('point_longitude');
        
        // Initialize if coordinates exist
        this.init();
    }
    
    init() {
        // Check if we have existing coordinates
        const lat = this.latInput?.value;
        const lng = this.lngInput?.value;
        
        if (lat && lng) {
            this.setPoint([parseFloat(lat), parseFloat(lng)], false);
        }
    }
    
    enable() {
        this.enabled = true;
        this.map.getContainer().style.cursor = 'crosshair';
        
        // Add click listener
        this.map.on('click', this.handleMapClick);
        
        // If no marker exists, show helper message
        if (!this.marker) {
            console.log('Point picker enabled - click on map to set location');
        }
    }
    
    disable() {
        this.enabled = false;
        this.map.getContainer().style.cursor = '';
        this.map.off('click', this.handleMapClick);
    }
    
    handleMapClick = (e) => {
        if (!this.enabled) return;
        
        const { lat, lng } = e.latlng;
        this.setPoint([lat, lng]);
    }
    
    setPoint(latLng, userSet = true) {
        const [lat, lng] = latLng;
        
        // Remove existing marker
        if (this.marker) {
            this.map.removeLayer(this.marker);
        }
        
        // Create draggable marker
        this.marker = L.marker([lat, lng], {
            draggable: true,
            icon: L.divIcon({
                className: 'fishing-lake-marker',
                html: `
                    <div style="
                        width: 40px;
                        height: 40px;
                        background: #3B82F6;
                        border: 3px solid white;
                        border-radius: 50%;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 20px;
                        cursor: move;
                    ">🐟</div>
                `,
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            })
        }).addTo(this.map);
        
        // Update inputs
        this.updateInputs(lat, lng);
        
        // Handle drag
        this.marker.on('dragend', (e) => {
            const { lat, lng } = e.target.getLatLng();
            this.updateInputs(lat, lng);
            this.onPointSet({ lat, lng });
        });
        
        // Pan to marker if user just set it
        if (userSet) {
            this.map.setView([lat, lng], Math.max(this.map.getZoom(), 13));
            this.onPointSet({ lat, lng });
        }
    }
    
    updateInputs(lat, lng) {
        if (this.latInput) {
            this.latInput.value = lat.toFixed(6);
        }
        if (this.lngInput) {
            this.lngInput.value = lng.toFixed(6);
        }
    }
    
    getPoint() {
        if (!this.marker) return null;
        
        const { lat, lng } = this.marker.getLatLng();
        return { lat, lng };
    }
    
    clear() {
        if (this.marker) {
            this.map.removeLayer(this.marker);
            this.marker = null;
        }
        
        if (this.latInput) this.latInput.value = '';
        if (this.lngInput) this.lngInput.value = '';
        
        this.disable();
    }
    
    destroy() {
        this.clear();
        this.map.off('click', this.handleMapClick);
    }
}
