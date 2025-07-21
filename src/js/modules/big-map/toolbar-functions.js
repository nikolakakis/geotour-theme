/**
 * Big Map Route Toolbar Functions
 * 
 * Handles all toolbar-related functionality for the route planner
 */

export class BigMapToolbar {
    constructor(map, dataHandler, routePreview, loadingStates) {
        this.map = map;
        this.dataHandler = dataHandler;
        this.routePreview = routePreview;
        this.loadingStates = loadingStates;
        
        this.toolbar = document.getElementById('route-planner-toolbar');
        this.routeCount = document.getElementById('route-count');
        this.previewInfo = document.getElementById('route-preview-info');
    }
    
    init() {
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        // Clear route button
        document.getElementById('clear-route')?.addEventListener('click', () => {
            if (confirm('Are you sure you want to clear all route stops?')) {
                this.clearRoute();
            }
        });
        
        // Route preview button
        document.getElementById('preview-route')?.addEventListener('click', () => {
            this.previewRoute();
        });
        
        // Export to Google Maps button
        document.getElementById('export-google-maps')?.addEventListener('click', () => {
            this.exportToGoogleMaps();
        });
        
        // Export to GeoJSON button
        document.getElementById('export-geojson')?.addEventListener('click', () => {
            this.exportToGeoJSON();
        });
        
        // Copy shareable link button
        document.getElementById('copy-shareable-link')?.addEventListener('click', () => {
            this.copyShareableLink();
        });
        
        // Zoom to route button (NEW)
        document.getElementById('zoom-to-route')?.addEventListener('click', () => {
            this.zoomToRoute();
        });
    }
    
    // Update toolbar visibility and stats
    updateToolbar(listings) {
        if (!this.toolbar || !this.routeCount) {
            console.error('Route toolbar elements not found');
            return;
        }
        
        // Filter route listings
        const routeListings = listings.filter(listing => listing.route_order);
        const routePointsCount = routeListings.length;
        
        if (routePointsCount > 0) {
            // Show toolbar
            this.toolbar.classList.remove('hidden');
            
            // Update count text
            const countText = routePointsCount === 1 
                ? `${routePointsCount} stop selected`
                : `${routePointsCount} stops selected`;
            this.routeCount.textContent = countText;
        } else {
            // Hide toolbar
            this.toolbar.classList.add('hidden');
            // Clear preview info
            if (this.previewInfo) {
                this.previewInfo.classList.add('hidden');
            }
        }
    }
    
    // Clear the route
    clearRoute() {
        const url = new URL(window.location);
        url.searchParams.delete('route_listings');
        
        // Update URL without reload
        window.history.replaceState({}, '', url.toString());
        
        // Update global params
        window.geotourBigMap.urlParams.route_listings = '';
        
        // Trigger AJAX refresh
        const refreshEvent = new CustomEvent('routeChanged', {
            detail: {
                shouldZoomToRoute: false // Don't zoom when clearing
            }
        });
        document.dispatchEvent(refreshEvent);
    }
    
    // Preview the route on the map
    previewRoute() {
        // Get all listings with route_order property
        const allListings = this.dataHandler.getCurrentListings();
        
        if (!allListings || allListings.length === 0) {
            alert('No listings data available.');
            return;
        }
        
        // Filter for route listings only and sort them by route_order
        const routeListings = allListings
            .filter(listing => listing.route_order)
            .sort((a, b) => parseInt(a.route_order) - parseInt(b.route_order));
        
        if (routeListings.length < 2) {
            alert('You need at least 2 stops to preview a route.');
            return;
        }
        
        // Show loading state
        this.loadingStates.showLoading(true);
        
        // Preview the route
        this.routePreview.drawRoute(routeListings)
            .then(result => {
                if (result.success) {
                    // Show route metadata in the preview info section
                    if (this.previewInfo) {
                        let metadataHTML = `<strong>Distance:</strong> ${result.distanceFormatted}<br>`;
                        
                        if (result.drivingCarUsed) {
                            metadataHTML += `<strong>Duration:</strong> ${result.durationFormatted}<br>`;
                        }
                        
                        metadataHTML += `<strong>Transport:</strong> ${result.profilesFormatted}`;
                        
                        this.previewInfo.innerHTML = metadataHTML;
                        this.previewInfo.classList.remove('hidden');
                    }
                } else {
                    alert(result.message || 'Failed to draw route. Please try again.');
                    // Hide preview info if there was an error
                    this.previewInfo?.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error previewing route:', error);
                alert('An error occurred while drawing the route. Please try again.');
                this.previewInfo?.classList.add('hidden');
            })
            .finally(() => {
                this.loadingStates.hideLoading();
            });
    }
    
    // Export to Google Maps
    exportToGoogleMaps() {
        // Get route listings sorted by route_order
        const allListings = this.dataHandler.getCurrentListings();
        const routeListings = allListings
            .filter(listing => listing.route_order)
            .sort((a, b) => parseInt(a.route_order) - parseInt(b.route_order));
        
        if (routeListings.length < 2) {
            alert('You need at least 2 stops to create a Google Maps route.');
            return;
        }
        
        // Build Google Maps URL
        let googleMapsUrl = 'https://www.google.com/maps/dir/';
        
        // Add each stop as a waypoint (in lat,lng format)
        routeListings.forEach(listing => {
            const lat = parseFloat(listing.latitude);
            const lng = parseFloat(listing.longitude);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                googleMapsUrl += `${lat},${lng}/`;
            }
        });
        
        // Open in new tab
        window.open(googleMapsUrl, '_blank');
    }
    
    // Export to GeoJSON
    exportToGeoJSON() {
        // Get route listings sorted by route_order
        const allListings = this.dataHandler.getCurrentListings();
        const routeListings = allListings
            .filter(listing => listing.route_order)
            .sort((a, b) => parseInt(a.route_order) - parseInt(b.route_order));
        
        if (routeListings.length === 0) {
            alert('No route stops to export.');
            return;
        }
        
        // Create GeoJSON feature for each listing
        const features = routeListings.map(listing => {
            const lat = parseFloat(listing.latitude);
            const lng = parseFloat(listing.longitude);
            
            if (isNaN(lat) || isNaN(lng)) {
                console.error('Invalid coordinates for listing:', listing);
                return null;
            }
            
            return {
                type: 'Feature',
                geometry: {
                    type: 'Point',
                    coordinates: [lng, lat] // GeoJSON uses [longitude, latitude]
                },
                properties: {
                    id: listing.id,
                    title: listing.title,
                    route_order: listing.route_order,
                    permalink: listing.permalink,
                    categories: listing.categories,
                    regions: listing.regions,
                    featured_image: listing.featured_image,
                    excerpt: listing.excerpt || listing.meta_description
                }
            };
        }).filter(feature => feature !== null);
        
        // Create GeoJSON object
        const geoJSON = {
            type: 'FeatureCollection',
            features: features
        };
        
        // Convert to string and create download
        const geoJSONString = JSON.stringify(geoJSON, null, 2);
        const blob = new Blob([geoJSONString], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        // Create download link
        const link = document.createElement('a');
        link.href = url;
        link.download = 'route-stops.geojson';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up the URL object
        setTimeout(() => URL.revokeObjectURL(url), 100);
    }
    
    // Copy shareable link
    copyShareableLink() {
        // Get route listings
        const allListings = this.dataHandler.getCurrentListings();
        const routeListings = allListings.filter(listing => listing.route_order);
        
        if (routeListings.length === 0) {
            alert('No route stops to share.');
            return;
        }
        
        // Extract listing IDs in order
        const routeListingIds = routeListings
            .sort((a, b) => parseInt(a.route_order) - parseInt(b.route_order))
            .map(listing => listing.id);
        
        // Create URL with route_listings parameter
        const url = new URL(window.location.href);
        url.searchParams.set('route_listings', routeListingIds.join(','));
        const shareableUrl = url.toString();
        
        // Copy to clipboard
        this.copyToClipboard(shareableUrl)
            .then(() => {
                // Create and show success message
                this.showCopySuccess('Link copied to clipboard!');
            })
            .catch(err => {
                console.error('Failed to copy link:', err);
                alert('Failed to copy link. Please try again.');
            });
    }
    
    // NEW: Zoom to route extent
    zoomToRoute() {
        const allListings = this.dataHandler.getCurrentListings();
        const routeListings = allListings.filter(listing => listing.route_order);
        
        if (routeListings.length === 0) {
            alert('No route stops to zoom to.');
            return;
        }
        
        this.zoomToRouteExtent(allListings);
    }
    
    // Helper method to zoom to route extent
    zoomToRouteExtent(listings) {
        // Filter listings that are part of the route
        const routeListings = listings.filter(listing => listing.route_order);
        
        if (routeListings.length === 0) {
            return;
        }
        
        if (routeListings.length === 1) {
            // Single route listing, center on it
            const listing = routeListings[0];
            this.map.setView([listing.latitude, listing.longitude], 14);
            return;
        }
        
        // Create markers for bounds calculation
        const markers = routeListings.map(listing => {
            return L.marker([listing.latitude, listing.longitude]);
        });
        
        // Create a feature group and fit bounds
        if (markers.length > 0) {
            const group = L.featureGroup(markers);
            this.map.fitBounds(group.getBounds().pad(0.1));
        }
    }
    
    // Helper method for clipboard operations
    copyToClipboard(text) {
        // Try to use the modern Clipboard API first
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }
        
        // Fallback for older browsers
        return new Promise((resolve, reject) => {
            try {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                const successful = document.execCommand('copy');
                document.body.removeChild(textArea);
                
                if (successful) {
                    resolve();
                } else {
                    reject(new Error('Copying failed'));
                }
            } catch (err) {
                reject(err);
            }
        });
    }
    
    // Display a temporary success message
    showCopySuccess(message) {
        // Check if we already have a notification
        let notification = document.getElementById('copy-notification');
        
        // Create if it doesn't exist
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'copy-notification';
            Object.assign(notification.style, {
                position: 'fixed',
                bottom: '80px',
                left: '50%',
                transform: 'translateX(-50%)',
                backgroundColor: '#10b981',
                color: 'white',
                padding: '10px 20px',
                borderRadius: '8px',
                boxShadow: '0 2px 8px rgba(0,0,0,0.2)',
                zIndex: '9999',
                opacity: '0',
                transition: 'opacity 0.3s ease'
            });
            document.body.appendChild(notification);
        }
        
        // Set message and show
        notification.textContent = message;
        setTimeout(() => { notification.style.opacity = '1'; }, 10);
        
        // Hide after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}