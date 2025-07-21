// ==========================================================================
// BIG MAP UI - MAIN CONTROLLER
// ==========================================================================
// Orchestrates all big map components and handles overall functionality
// Refactored for better organization and maintainability

import { BigMapDataHandler } from './data-handler.js';
import { BigMapMarkers } from './markers.js';
import { BigMapSidebar } from './sidebar.js';
import { BigMapLoadingStates } from './loading.js';
import { BigMapRoutePreview } from './route-preview.js';

export class BigMapUI {
    constructor() {
        this.map = null;
        this.searchTerm = '';
        this.focusListingId = null; // NEW: Track which listing to focus after repopulation
        
        // Initialize handlers
        this.dataHandler = new BigMapDataHandler();
        this.markersHandler = new BigMapMarkers();
        this.sidebarHandler = new BigMapSidebar();
        this.loadingStates = new BigMapLoadingStates();
        this.routePreview = new BigMapRoutePreview('5b3ce3597851110001cf624858bc89595fdf4f0eb8df2464c0e8e135'); // Using your API key
        
        // Get URL parameters on initialization
        this.urlParams = new URLSearchParams(window.location.search);
        this.searchTerm = this.urlParams.get('search') || '';
        
        this.init();
    }
    
    init() {
        // Check if we're on the big map page
        if (!document.querySelector('.big-map-container')) {
            return;
        }
        
        // Check if Leaflet is available
        if (typeof L === 'undefined') {
            console.error('Leaflet not found - make sure the geotour-crete-maps plugin is active');
            this.loadingStates.showError('Map library not loaded. Please contact site administrator.');
            return;
        }
        
        this.sidebarHandler.initialize();
        this.setupEventListeners();
        this.initializeMap();
        this.loadInitialData();
        
        // Set up event handlers for the route toolbar
        this.setupRouteToolbarEvents();
        
        // Initialize route preview when map is available
        if (this.map) {
            this.routePreview.init(this.map);
        }
    }
    
    setupEventListeners() {
        // Map controls
        document.getElementById('locate-me')?.addEventListener('click', () => {
            this.markersHandler.locateUser(this.map);
        });
        
        document.getElementById('fit-bounds')?.addEventListener('click', () => {
            this.markersHandler.fitBounds(this.map);
        });
        
        document.getElementById('reset-view')?.addEventListener('click', () => {
            this.resetView();
        });
        
        // Window resize handler for map
        window.addEventListener('resize', () => {
            if (this.map) {
                setTimeout(() => {
                    this.map.invalidateSize();
                }, 100);
            }
        });
        
        // Route change event listener
        document.addEventListener('routeChanged', (e) => {
            this.handleRouteChange(e.detail);
        });
    }
    
    setupRouteToolbarEvents() {
        // Clear route button
        document.getElementById('clear-route')?.addEventListener('click', () => {
            if (confirm('Are you sure you want to clear all route stops?')) {
                this.clearRoute();
            }
        });
        
        // Other toolbar buttons (placeholders for now)
        document.getElementById('preview-route')?.addEventListener('click', () => {
            this.previewRoute();
        });
        
        document.getElementById('export-google-maps')?.addEventListener('click', () => {
            this.exportToGoogleMaps();
        });
        
        document.getElementById('export-geojson')?.addEventListener('click', () => {
            this.exportToGeoJSON();
        });
        
        document.getElementById('copy-shareable-link')?.addEventListener('click', () => {
            this.copyShareableLink();
        });
    }
    
    initializeMap() {
        const mapContainer = document.getElementById('big-map');
        if (!mapContainer) return;
        
        // Initialize Leaflet map
        this.map = L.map('big-map', {
            center: window.geotourBigMap.defaultCenter,
            zoom: window.geotourBigMap.defaultZoom,
            zoomControl: false // We'll add custom controls
        });
        
        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(this.map);
        
        // Add custom zoom control
        L.control.zoom({
            position: 'topright'
        }).addTo(this.map);
        
        // Add map move events for bounding box filtering
        this.map.on('moveend', () => this.onMapMoveEnd());
        this.map.on('zoomend', () => this.onMapMoveEnd());
        
        // Add map click event to hide sidebar on mobile
        this.map.on('click', () => {
            if (this.sidebarHandler.getIsMobile() && this.sidebarHandler.getSidebarVisibility()) {
                this.sidebarHandler.hideSidebarOnMobile();
            }
        });
    }
    
    async loadInitialData() {
        this.loadingStates.showLoading(true);
        
        try {
            const data = await this.dataHandler.fetchListings();
            this.updateMapAndSidebar(data);
            
            // Initialize toolbar visibility on first load
            this.updateRouteToolbar(data);
        } catch (error) {
            console.error('Error loading initial data:', error);
            this.loadingStates.showError(window.geotourBigMap.strings.loadingError);
        } finally {
            this.loadingStates.hideLoading();
        }
    }
    
    async onMapMoveEnd() {
        if (this.loadingStates.getLoadingState()) return;
        
        try {
            await this.dataHandler.onMapMoveEnd(this.map, (data) => {
                this.loadingStates.showLoading(true);
                this.updateMapAndSidebar(data);
                this.loadingStates.hideLoading();
            });
        } catch (error) {
            console.error('Error loading data for current view:', error);
            this.loadingStates.showError('Error loading listings. Please try again.');
            this.loadingStates.hideLoading();
        }
    }
    
    updateMapAndSidebar(listings) {
        // Update map markers
        this.markersHandler.updateMap(this.map, listings);
        
        // Add click handlers to markers
        const markers = this.markersHandler.getCurrentMarkers();
        markers.forEach((marker, index) => {
            if (listings[index]) {
                this.markersHandler.addMarkerClickHandler(marker, listings[index].id, (listingId) => {
                    this.handleMarkerClick(listingId);
                });
            }
        });
        
        // Update sidebar
        this.sidebarHandler.updateSidebar(listings);
        
        // Add handlers to listing items
        listings.forEach(listing => {
            const item = document.querySelector(`[data-listing-id="${listing.id}"]`);
            if (item) {
                this.sidebarHandler.addListingItemHandlers(
                    item, 
                    listing,
                    (listing) => this.handleListingClick(listing),
                    (filterType, filterValue) => this.handleFilterClick(filterType, filterValue)
                );
            }
        });
        
        // Update route toolbar visibility
        this.updateRouteToolbar(listings);
        
        // NEW: After repopulation, open popup if focusListingId is set
        if (this.focusListingId) {
            const focusListing = listings.find(l => l.id == this.focusListingId);
            if (focusListing) {
                this.markersHandler.panToListing(this.map, focusListing);
            }
            this.focusListingId = null;
        }
    }
    
    handleMarkerClick(listingId) {
        this.sidebarHandler.highlightListing(listingId);
        
        // Hide sidebar on mobile when marker is clicked
        if (this.sidebarHandler.getIsMobile()) {
            this.sidebarHandler.hideSidebarOnMobile();
        }
    }
    
    handleListingClick(listing) {
        this.sidebarHandler.highlightListing(listing.id);
        this.focusListingId = listing.id; // NEW: Remember to open this popup after repopulation
        this.markersHandler.panToListing(this.map, listing);
    }
    
    handleFilterClick(filterType, filterValue) {
        this.sidebarHandler.applyFilter(filterType, filterValue);
    }
    
    resetView() {
        this.map.setView(window.geotourBigMap.defaultCenter, window.geotourBigMap.defaultZoom);
    }
    
    async handleRouteChange(options = {}) {
        try {
            this.loadingStates.showLoading(true);
            
            // Fetch fresh data with updated route
            const data = await this.dataHandler.fetchListings();
            
            // Update map and sidebar
            this.updateMapAndSidebar(data);
            
            // Update toolbar
            this.updateRouteToolbar(data);
            
            // If requested, zoom to route extent
            if (options.shouldZoomToRoute) {
                this.zoomToRouteExtent(data);
            }
            
        } catch (error) {
            console.error('Error refreshing route data:', error);
            this.loadingStates.showError('Error updating route. Please try again.');
        } finally {
            this.loadingStates.hideLoading();
        }
    }
    
    zoomToRouteExtent(listings) {
        // Filter listings that are part of the route
        const routeListings = listings.filter(listing => listing.route_order);
        
        if (routeListings.length === 0) {
            // No route listings, return to default view
            this.resetView();
            return;
        }
        
        if (routeListings.length === 1) {
            // Single route listing, center on it
            const listing = routeListings[0];
            this.map.setView([listing.latitude, listing.longitude], 14);
            return;
        }
        
        // Multiple route listings, fit bounds to include all
        const routeMarkers = this.markersHandler.getCurrentMarkers().filter((marker, index) => {
            return listings[index] && listings[index].route_order;
        });
        
        if (routeMarkers.length > 0) {
            const group = new L.featureGroup(routeMarkers);
            this.map.fitBounds(group.getBounds().pad(0.1));
        }
    }
    
    updateRouteToolbar(listings) {
        // Get toolbar elements
        const toolbar = document.getElementById('route-planner-toolbar');
        const routeCount = document.getElementById('route-count');
        
        if (!toolbar || !routeCount) {
            console.error('Route toolbar elements not found');
            return;
        }
        
        // Filter route listings
        const routeListings = listings.filter(listing => listing.route_order);
        const routePointsCount = routeListings.length;
        
        console.log('Updating toolbar visibility:', { routePointsCount, routeListings });
        
        if (routePointsCount > 0) {
            // Show toolbar by removing hidden class
            toolbar.classList.remove('hidden');
            
            // Update count text
            const countText = routePointsCount === 1 
                ? `${routePointsCount} stop selected`
                : `${routePointsCount} stops selected`;
            routeCount.textContent = countText;
            
            console.log('Toolbar should now be visible');
        } else {
            // Hide toolbar by adding hidden class
            toolbar.classList.add('hidden');
            console.log('Toolbar hidden - no route points');
        }
    }
    
    // Route toolbar functionality methods
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
    
    // Placeholder methods for future implementation
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
        
        console.log('Drawing route between', routeListings.length, 'stops');
        
        // Show loading state
        this.loadingStates.showLoading(true);
        
        // Preview the route
        this.routePreview.drawRoute(routeListings)
            .then(result => {
                if (result.success) {
                    // Show route metadata in the preview info section
                    const previewInfo = document.getElementById('route-preview-info');
                    if (previewInfo) {
                        let metadataHTML = `<strong>Distance:</strong> ${result.distanceFormatted}<br>`;
                        
                        if (result.drivingCarUsed) {
                            metadataHTML += `<strong>Duration:</strong> ${result.durationFormatted}<br>`;
                        }
                        
                        metadataHTML += `<strong>Transport:</strong> ${result.profilesFormatted}`;
                        
                        previewInfo.innerHTML = metadataHTML;
                        previewInfo.classList.remove('hidden');
                    }
                } else {
                    alert(result.message || 'Failed to draw route. Please try again.');
                    // Hide preview info if there was an error
                    document.getElementById('route-preview-info')?.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error previewing route:', error);
                alert('An error occurred while drawing the route. Please try again.');
                document.getElementById('route-preview-info')?.classList.add('hidden');
            })
            .finally(() => {
                this.loadingStates.hideLoading();
            });
    }
    
    exportToGoogleMaps() {
        // TODO: Implement Google Maps export
        console.log('Export to Google Maps clicked - functionality to be implemented');
    }
    
    exportToGeoJSON() {
        // TODO: Implement GeoJSON export
        console.log('Export to GeoJSON clicked - functionality to be implemented');
    }
    
    copyShareableLink() {
        // TODO: Implement shareable link copy
        console.log('Copy shareable link clicked - functionality to be implemented');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new BigMapUI();
});