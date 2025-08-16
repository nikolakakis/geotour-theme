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
import { BigMapToolbar } from './toolbar-functions.js';

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
        
        // Move the toolbar initialization here but don't initialize yet
        // (we'll do that after the map is ready)
        this.toolbar = null;

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
        
        // After map is initialized, initialize the toolbar
        this.toolbar = new BigMapToolbar(
            this.map, 
            this.dataHandler, 
            this.routePreview,
            this.loadingStates
        );
        this.toolbar.init();
    }
    
    async loadInitialData() {
        this.loadingStates.showLoading(true);
        
        try {
            const currentZoom = this.map ? this.map.getZoom() : window.geotourBigMap.defaultZoom;
            const data = await this.dataHandler.fetchListings(null, currentZoom);
            this.updateMapAndSidebar(data);
            
            // Initialize toolbar visibility on first load
            const listings = Array.isArray(data) ? data : data.listings || [];
            this.toolbar.updateToolbar(listings);
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
    
    updateMapAndSidebar(data) {
        // Handle both old format (array) and new format (object with listings/supplementary)
        const listings = Array.isArray(data) ? data : data.listings || [];
        
        // Update map markers (both listings and supplementary)
        this.markersHandler.updateMap(this.map, data);
        
        // Add click handlers to main listing markers only
        const markers = this.markersHandler.getCurrentMarkers();
        markers.forEach((marker, index) => {
            if (listings[index]) {
                this.markersHandler.addMarkerClickHandler(marker, listings[index].id, (listingId) => {
                    this.handleMarkerClick(listingId);
                });
            }
        });
        
        // Update sidebar (only with main listings, not supplementary data)
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
        this.toolbar.updateToolbar(listings);
        
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
            const currentZoom = this.map ? this.map.getZoom() : window.geotourBigMap.defaultZoom;
            const data = await this.dataHandler.fetchListings(null, currentZoom);
            
            // Update map and sidebar
            this.updateMapAndSidebar(data);
            
            // Update toolbar
            const listings = Array.isArray(data) ? data : data.listings || [];
            this.toolbar.updateToolbar(listings);
            
            // If requested, zoom to route extent
            if (options.shouldZoomToRoute) {
                this.toolbar.zoomToRouteExtent(listings);
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
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new BigMapUI();
});