// ==========================================================================
// BIG MAP MARKERS HANDLER
// ==========================================================================
// Handles marker creation, management, and map interactions

export class BigMapMarkers {
    constructor() {
        this.currentMarkers = [];
        this.initialBoundsFit = false;
    }
    
    updateMap(map, listings) {
        // Clear existing markers
        this.currentMarkers.forEach(marker => {
            map.removeLayer(marker);
        });
        this.currentMarkers = [];
        
        // Add new markers
        listings.forEach(listing => {
            if (listing.latitude && listing.longitude) {
                const marker = this.createMarker(listing);
                this.currentMarkers.push(marker);
                marker.addTo(map);
            }
        });
        
        // Only fit bounds on initial load, not on every pan/zoom
        if (this.currentMarkers.length > 0 && !this.initialBoundsFit) {
            const group = new L.featureGroup(this.currentMarkers);
            map.fitBounds(group.getBounds().pad(0.1));
            this.initialBoundsFit = true;
        }
    }
    
    createMarker(listing) {
        // Create custom icon using the map icon URL from API
        const icon = L.divIcon({
            className: 'custom-map-marker',
            html: `<img src="${listing.map_icon_url}" alt="${listing.title}" class="marker-pin" style="width: 32px; height: 32px;" onerror="this.src='${window.geotourBigMap.defaultIconUrl || '/wp-content/themes/geotour-theme/assets/map-pins/default.svg'}'">`,
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });
        
        const marker = L.marker([listing.latitude, listing.longitude], { icon });
        
        // Create popup content
        const popupContent = this.createPopupContent(listing);
        marker.bindPopup(popupContent);
        
        return marker;
    }
    
    createPopupContent(listing) {
        //const categories = listing.categories.map(cat => cat.name).join(', ');
        //const regions = listing.regions.map(reg => reg.name).join(', ');
        
        return `
            <div class="map-popup">
                <h4>${listing.title}</h4>
                ${listing.featured_image_medium ? `<img src="${listing.featured_image_medium}" alt="${listing.title}" class="popup-image">` : ''}
                <p>${listing.meta_description || listing.excerpt}</p>                
          
                <a href="${listing.permalink}" class="popup-link">View Details</a>
            </div>
        `;
    }
    
    panToListing(map, listing) {
        if (map && listing.latitude && listing.longitude) {
            // Pan to the listing location
            map.setView([listing.latitude, listing.longitude], Math.max(map.getZoom(), 14));
            
            // Find and open the corresponding marker popup
            this.currentMarkers.forEach(marker => {
                const markerLatLng = marker.getLatLng();
                // Check if this marker matches the listing coordinates (with small tolerance for floating point precision)
                if (Math.abs(markerLatLng.lat - listing.latitude) < 0.00001 && 
                    Math.abs(markerLatLng.lng - listing.longitude) < 0.00001) {
                    
                    // Open the popup with a small delay to ensure map panning is complete
                    setTimeout(() => {
                        marker.openPopup();
                    }, 300);
                }
            });
        }
    }
    
    fitBounds(map) {
        if (this.currentMarkers.length > 0) {
            const group = new L.featureGroup(this.currentMarkers);
            map.fitBounds(group.getBounds().pad(0.1));
        }
    }
    
    locateUser(map) {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by this browser.');
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                map.setView([latitude, longitude], 14);
                
                // Add user marker
                L.marker([latitude, longitude], {
                    icon: L.divIcon({
                        className: 'user-location-marker',
                        html: '<div class="user-marker">📍</div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    })
                }).addTo(map);
            },
            (error) => {
                console.error('Error getting location:', error);
                alert('Could not get your location. Please check your browser permissions.');
            }
        );
    }
    
    addMarkerClickHandler(marker, listingId, callback) {
        marker.on('click', () => {
            callback(listingId);
        });
    }
    
    getCurrentMarkers() {
        return this.currentMarkers;
    }
}
