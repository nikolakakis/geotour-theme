// ==========================================================================
// BIG MAP UI - MAIN CONTROLLER
// ==========================================================================
// Orchestrates all big map components and handles overall functionality
// Refactored for better organization and maintainability

import { BigMapDataHandler } from './data-handler.js';
import { BigMapMarkers } from './markers.js';
import { BigMapSidebar } from './sidebar.js';
import { BigMapLoadingStates } from './loading.js';

export class BigMapUI {
    constructor() {
        this.map = null;
        this.searchTerm = '';
        
        // Initialize handlers
        this.dataHandler = new BigMapDataHandler();
        this.markersHandler = new BigMapMarkers();
        this.sidebarHandler = new BigMapSidebar();
        this.loadingStates = new BigMapLoadingStates();
        
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
        this.markersHandler.panToListing(this.map, listing);
    }
    
    handleFilterClick(filterType, filterValue) {
        this.sidebarHandler.applyFilter(filterType, filterValue);
    }
    
    resetView() {
        this.map.setView(window.geotourBigMap.defaultCenter, window.geotourBigMap.defaultZoom);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new BigMapUI();
});