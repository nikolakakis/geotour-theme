// ==========================================================================
// BIG MAP MARKERS HANDLER
// ==========================================================================
// Handles marker creation, management, and map interactions

export class BigMapMarkers {
    constructor() {
        this.currentMarkers = [];
        this.supplementaryMarkers = []; // New array for supplementary markers
        this.initialBoundsFit = false;
    }
    
    updateMap(map, data) {
        // Handle both old format (array) and new format (object with listings/supplementary)
        const listings = Array.isArray(data) ? data : data.listings || [];
        const supplementaryData = Array.isArray(data) ? [] : data.supplementary || [];
        
        // Clear existing markers
        this.currentMarkers.forEach(marker => {
            map.removeLayer(marker);
        });
        this.currentMarkers = [];

        // Clear existing supplementary markers
        this.supplementaryMarkers.forEach(marker => {
            map.removeLayer(marker);
        });
        this.supplementaryMarkers = [];
        
        // Add main listing markers
        listings.forEach(listing => {
            if (listing.latitude && listing.longitude) {
                const marker = this.createMarker(listing);
                this.currentMarkers.push(marker);
                marker.addTo(map);
            }
        });

        // Add supplementary markers (panoramas, people, etc.)
        supplementaryData.forEach(item => {
            if (item.latitude && item.longitude) {
                const marker = this.createSupplementaryMarker(item);
                this.supplementaryMarkers.push(marker);
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
            className: `custom-map-marker ${listing.route_order ? 'is-route-listing' : ''}`,
            html: `<img src="${listing.map_icon_url}" alt="${listing.title}" class="marker-pin" style="width: 32px; height: 32px;" onerror="this.src='${window.geotourBigMap.defaultIconUrl || '/wp-content/themes/geotour-theme/assets/map-pins/default.svg'}'">`,
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });
        
        const marker = L.marker([listing.latitude, listing.longitude], { icon });
        
        // Create popup content
        const popupContent = this.createPopupContent(listing);
        marker.bindPopup(popupContent);
        
        // Add event listeners for route buttons after popup opens
        marker.on('popupopen', (e) => {
            const popup = e.popup;
            const popupElement = popup.getElement();
            
            // Add click handlers for route action buttons
            const routeButtons = popupElement.querySelectorAll('.route-action-btn');
            routeButtons.forEach(btn => {
                btn.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    const listingId = btn.dataset.listingId;
                    if (btn.classList.contains('add-to-route')) {
                        this.addToRoute(listingId);
                    } else if (btn.classList.contains('remove-from-route')) {
                        this.removeFromRoute(listingId);
                    }
                });
            });
            
            // Add click handlers for route order reordering
            const routeOrders = popupElement.querySelectorAll('.route-order.clickable');
            routeOrders.forEach(orderSpan => {
                orderSpan.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    const listingId = orderSpan.dataset.listingId;
                    const currentOrder = parseInt(orderSpan.dataset.currentOrder);
                    this.showReorderDialog(listingId, currentOrder);
                });
            });
        });
        
        return marker;
    }

    createSupplementaryMarker(item) {
        // Create custom icon for supplementary items (panoramas, people, etc.)
        const iconClass = `supplementary-marker marker-${item.source_type}`;
        
        // Use specific icon for panoramas, fallback to API icon for others
        let iconUrl = item.map_icon_url;
        let iconSize = [24, 24]; // Default size for other supplementary items
        
        if (item.source_type === 'panorama') {
            iconUrl = '/wp-content/themes/geotour-theme/assets/graphics/map-pins/panoramas.svg';
            iconSize = [30, 30]; // Bigger size for panoramas to make them more visible
        }
        
        const icon = L.divIcon({
            className: iconClass,
            html: `<img src="${iconUrl}" alt="${item.title}" class="supplementary-pin" style="width: ${iconSize[0]}px; height: ${iconSize[1]}px;" onerror="this.src='${window.geotourBigMap.defaultIconUrl || '/wp-content/themes/geotour-theme/assets/map-pins/default.svg'}'">`,
            iconSize: iconSize,
            iconAnchor: [iconSize[0]/2, iconSize[1]],
            popupAnchor: [0, -iconSize[1]]
        });
        
        const marker = L.marker([item.latitude, item.longitude], { icon });
        
        // Create popup content for supplementary items
        const popupContent = this.createSupplementaryPopupContent(item);
        marker.bindPopup(popupContent);
        
        return marker;
    }

    createSupplementaryPopupContent(item) {
        // Create different popup content based on source type
        let typeLabel = '';
        let linkText = 'View Details';
        let extraInfo = '';
        
        switch(item.source_type) {
            case 'panorama':
                typeLabel = 'Virtual Tour';
                linkText = 'Visit'; // Special link text for panoramas
                break;
            case 'people':
                typeLabel = 'Historical Figure';
                if (item.acf_fields) {
                    if (item.acf_fields.event_date) {
                        extraInfo += `<p><strong>Period:</strong> ${item.acf_fields.event_date}</p>`;
                    }
                    if (item.acf_fields.event_role) {
                        extraInfo += `<p><strong>Role:</strong> ${item.acf_fields.event_role}</p>`;
                    }
                }
                break;
            case 'oldphotos':
                typeLabel = 'Historical Photo';
                break;
            case 'pois':
                typeLabel = 'Point of Interest';
                break;
            default:
                typeLabel = 'Information';
        }
        
        return `
            <div class="map-popup supplementary-popup ${item.source_type}-popup">
                <div class="popup-type-label">${typeLabel}</div>
                <h4>${item.title}</h4>
                ${item.image_url ? `<img src="${item.image_url}" alt="${item.title}" class="popup-image">` : ''}
                ${item.description ? `<p>${item.description}</p>` : ''}
                ${extraInfo}
                <a href="${item.item_url}" class="popup-link">${linkText}</a>
            </div>
        `;
    }
    
    createPopupContent(listing) {
        //const categories = listing.categories.map(cat => cat.name).join(', ');
        //const regions = listing.regions.map(reg => reg.name).join(', ');
        
        // Route order and action buttons
        let routeSection = '';
        if (listing.route_order) {
            routeSection = `
                <div class="popup-route-info">
                    <span class="route-order clickable" data-listing-id="${listing.id}" data-current-order="${listing.route_order}" title="Click to change order">Route Stop #${listing.route_order}</span>
                    <button class="route-action-btn remove-from-route" data-listing-id="${listing.id}" title="Remove from route">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19,13H5V11H19V13Z"/>
                        </svg>
                    </button>
                </div>
            `;
        } else {
            routeSection = `
                <div class="popup-route-info">
                    <button class="route-action-btn add-to-route" data-listing-id="${listing.id}" title="Add to route">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                        </svg>
                        Add to Route
                    </button>
                </div>
            `;
        }
        
        return `
            <div class="map-popup">
                <h4>${listing.title}</h4>
                ${listing.featured_image_medium ? `<img src="${listing.featured_image_medium}" alt="${listing.title}" class="popup-image">` : ''}
                <p>${listing.meta_description || listing.excerpt}</p>
                ${routeSection}
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
    
    // Route management methods
    addToRoute(listingId) {
        const url = new URL(window.location);
        const currentRoute = url.searchParams.get('route_listings') || '';
        const routeIds = currentRoute ? currentRoute.split(',') : [];
        
        // Add the new listing ID if not already present
        if (!routeIds.includes(listingId.toString())) {
            routeIds.push(listingId);
            url.searchParams.set('route_listings', routeIds.join(','));
            
            // Update URL without reload
            window.history.replaceState({}, '', url.toString());
            
            // Update global params
            window.geotourBigMap.urlParams.route_listings = routeIds.join(',');
            
            // Trigger AJAX refresh
            this.refreshMapData();
        }
    }
    
    removeFromRoute(listingId) {
        const url = new URL(window.location);
        const currentRoute = url.searchParams.get('route_listings') || '';
        const routeIds = currentRoute ? currentRoute.split(',') : [];
        
        // Remove the listing ID
        const filteredIds = routeIds.filter(id => id !== listingId.toString());
        
        if (filteredIds.length > 0) {
            url.searchParams.set('route_listings', filteredIds.join(','));
            window.geotourBigMap.urlParams.route_listings = filteredIds.join(',');
        } else {
            url.searchParams.delete('route_listings');
            window.geotourBigMap.urlParams.route_listings = '';
        }
        
        // Update URL without reload
        window.history.replaceState({}, '', url.toString());
        
        // Trigger AJAX refresh
        this.refreshMapData();
    }
    
    refreshMapData() {
        // Dispatch custom event to trigger map refresh
        const refreshEvent = new CustomEvent('routeChanged', {
            detail: {
                shouldZoomToRoute: true
            }
        });
        document.dispatchEvent(refreshEvent);
    }
    
    showReorderDialog(listingId, currentOrder) {
        // Get current route to determine max order
        const currentRoute = new URLSearchParams(window.location.search).get('route_listings') || '';
        const routeIds = currentRoute ? currentRoute.split(',') : [];
        const maxOrder = routeIds.length;
        
        const newOrder = prompt(
            `Change stop order for this location:\n\nCurrent position: ${currentOrder}\nTotal stops: ${maxOrder}\n\nEnter new position (1-${maxOrder}):`, 
            currentOrder
        );
        
        if (newOrder === null) return; // User cancelled
        
        const newOrderNum = parseInt(newOrder);
        if (isNaN(newOrderNum) || newOrderNum < 1 || newOrderNum > maxOrder || newOrderNum === currentOrder) {
            if (newOrderNum !== currentOrder) {
                alert('Please enter a valid position number between 1 and ' + maxOrder);
            }
            return;
        }
        
        this.reorderRoute(listingId, currentOrder, newOrderNum, routeIds);
    }
    
    reorderRoute(listingId, currentOrder, newOrder, routeIds) {
        // Simple approach: rebuild array by inserting at new position
        const finalIds = [...routeIds];
        
        // Remove the item from its current position
        const itemIndex = finalIds.findIndex(id => id === listingId);
        if (itemIndex !== -1) {
            finalIds.splice(itemIndex, 1);
        }
        
        // Insert at new position (convert to 0-based index)
        finalIds.splice(newOrder - 1, 0, listingId);
        
        // Update URL and refresh
        const url = new URL(window.location);
        url.searchParams.set('route_listings', finalIds.join(','));
        
        // Update URL without reload
        window.history.replaceState({}, '', url.toString());
        
        // Update global params
        window.geotourBigMap.urlParams.route_listings = finalIds.join(',');
        
        // Trigger AJAX refresh
        this.refreshMapData();
    }
}
