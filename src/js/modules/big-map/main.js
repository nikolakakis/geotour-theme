// Big Map UI JavaScript Module
// Handles the full-screen map with sidebar and AJAX listing loading

export class BigMapUI {
    constructor() {
        this.map = null;
        this.markers = {};
        this.currentMarkers = [];
        this.sidebar = null;
        this.isLoading = false;
        this.lastBounds = null;
        this.boundsTimeout = null;
        this.searchTerm = ''; // Add search term property
        
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
        
        this.sidebar = document.getElementById('map-sidebar');
        this.setupEventListeners();
        this.initializeMap();
        this.loadInitialData();
        this.initializeSearch(); // Add search initialization
    }
    
    setupEventListeners() {
        // Sidebar toggle for mobile
        const toggleBtn = document.getElementById('sidebar-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggleSidebar());
        }
        
        // Mobile sidebar toggle from map controls
        const mapControls = document.querySelector('.map-controls');
        if (mapControls) {
            mapControls.addEventListener('click', (e) => {
                if (e.target === mapControls || e.target.matches('.map-controls::before')) {
                    this.toggleSidebar();
                }
            });
        }
        
        // Map controls
        document.getElementById('locate-me')?.addEventListener('click', () => this.locateUser());
        document.getElementById('fit-bounds')?.addEventListener('click', () => this.fitBounds());
        document.getElementById('reset-view')?.addEventListener('click', () => this.resetView());
        
        // Filter controls
        document.querySelectorAll('.remove-filter').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const url = button.getAttribute('href');
                if (url) {
                    window.location.href = url;
                }
            });
        });
        
        document.querySelectorAll('.clear-filters').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.clearAllFilters();
            });
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
    }
    
    async loadInitialData() {
        this.showLoading(true);
        
        try {
            const data = await this.fetchListings();
            this.updateMap(data);
            this.updateSidebar(data);
        } catch (error) {
            console.error('Error loading initial data:', error);
            this.showError(window.geotourBigMap.strings.loadingError);
        } finally {
            this.hideLoading();
        }
    }
    
    async fetchListings(bbox = null) {
        const params = new URLSearchParams();
        
        // Add bounding box if provided
        if (bbox) {
            params.append('bbox', bbox);
        }
        
        // Add URL parameter filters if they exist
        if (window.geotourBigMap.urlParams.category) {
            params.append('listing_category', window.geotourBigMap.urlParams.category);
        }
        if (window.geotourBigMap.urlParams.region) {
            params.append('listing_region', window.geotourBigMap.urlParams.region);
        }
        if (window.geotourBigMap.urlParams.tag) {
            params.append('listing_tag', window.geotourBigMap.urlParams.tag);
        }
        
        const url = `${window.geotourBigMap.apiUrl}?${params.toString()}`;
        console.log('Fetching listings with filters:', params.toString());
        
        const response = await fetch(url, {
            headers: {
                'X-WP-Nonce': window.geotourBigMap.nonce
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        return data;
    }
    
    updateMap(listings) {
        // Clear existing markers
        this.currentMarkers.forEach(marker => {
            this.map.removeLayer(marker);
        });
        this.currentMarkers = [];
        
        // Add new markers
        listings.forEach(listing => {
            if (listing.latitude && listing.longitude) {
                const marker = this.createMarker(listing);
                this.currentMarkers.push(marker);
                marker.addTo(this.map);
            }
        });
        
        // Fit bounds if we have markers
        if (this.currentMarkers.length > 0) {
            const group = new L.featureGroup(this.currentMarkers);
            this.map.fitBounds(group.getBounds().pad(0.1));
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
        
        // Add click event to highlight in sidebar
        marker.on('click', () => this.highlightListing(listing.id));
        
        return marker;
    }
    
    createPopupContent(listing) {
        const categories = listing.categories.map(cat => cat.name).join(', ');
        const regions = listing.regions.map(reg => reg.name).join(', ');
        
        return `
            <div class="map-popup">
                <h4>${listing.title}</h4>
                ${listing.featured_image_medium ? `<img src="${listing.featured_image_medium}" alt="${listing.title}" class="popup-image">` : ''}
                <p>${listing.meta_description || listing.excerpt}</p>
                ${categories ? `<div class="popup-meta"><strong>Categories:</strong> ${categories}</div>` : ''}
                ${regions ? `<div class="popup-meta"><strong>Regions:</strong> ${regions}</div>` : ''}
                <a href="${listing.permalink}" class="popup-link">View Details</a>
            </div>
        `;
    }
    
    updateSidebar(listings) {
        const container = document.getElementById('listings-container');
        const countElement = document.getElementById('results-count');
        
        if (!container || !countElement) return;
        
        // Update count
        const count = listings.length;
        countElement.textContent = window.geotourBigMap.strings.resultsFound.replace('{count}', count);
        
        // Clear container
        container.innerHTML = '';
        
        if (count === 0) {
            container.innerHTML = `<div class="no-results">${window.geotourBigMap.strings.noResults}</div>`;
            return;
        }
        
        // Add listings
        listings.forEach(listing => {
            const item = this.createListingItem(listing);
            container.appendChild(item);
        });
    }
    
    createListingItem(listing) {
        const item = document.createElement('div');
        item.className = 'listing-item';
        item.dataset.listingId = listing.id;
        
        const categories = listing.categories.map(cat => 
            `<span class="meta-tag meta-category" data-filter-type="listing-category" data-filter-value="${cat.slug}">${cat.name}</span>`
        ).join('');
        
        const regions = listing.regions.map(reg => 
            `<span class="meta-tag meta-region" data-filter-type="listing-region" data-filter-value="${reg.slug}">${reg.name}</span>`
        ).join('');
        
        const tags = listing.tags.map(tag => 
            `<span class="meta-tag meta-tag-item" data-filter-type="listing-tag" data-filter-value="${tag.slug}">${tag.name}</span>`
        ).join('');
        
        const thumbnailHtml = listing.featured_image 
            ? `<img src="${listing.featured_image}" alt="${listing.title}" class="listing-thumbnail">`
            : `<div class="listing-thumbnail"></div>`;
        
        item.innerHTML = `
            ${thumbnailHtml}
            <div class="listing-content">
                <div class="listing-title">${listing.title}</div>
                <div class="listing-description">${listing.meta_description || listing.excerpt}</div>
                <div class="listing-meta">
                    ${categories}
                    ${regions}
                    ${tags}
                </div>
            </div>
        `;
        
        // Add click event for the item itself
        item.addEventListener('click', (e) => {
            // Don't trigger if clicking on a meta tag
            if (!e.target.classList.contains('meta-tag')) {
                this.highlightListing(listing.id);
                this.panToListing(listing);
            }
        });
        
        // Add click events for meta tags (categories, regions, tags)
        item.querySelectorAll('.meta-tag[data-filter-type]').forEach(tag => {
            tag.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent item click
                const filterType = tag.dataset.filterType;
                const filterValue = tag.dataset.filterValue;
                this.applyFilter(filterType, filterValue);
            });
        });
        
        return item;
    }
    
    highlightListing(listingId) {
        // Remove previous highlights
        document.querySelectorAll('.listing-item.active').forEach(item => {
            item.classList.remove('active');
        });
        
        // Add highlight to current item
        const item = document.querySelector(`[data-listing-id="${listingId}"]`);
        if (item) {
            item.classList.add('active');
            item.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    panToListing(listing) {
        if (this.map && listing.latitude && listing.longitude) {
            // Pan to the listing location
            this.map.setView([listing.latitude, listing.longitude], Math.max(this.map.getZoom(), 14));
            
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
    
    
    async onMapMoveEnd() {
        if (this.isLoading) return;
        
        // Debounce the bounding box updates
        if (this.boundsTimeout) {
            clearTimeout(this.boundsTimeout);
        }
        
        this.boundsTimeout = setTimeout(async () => {
            // Get current bounding box
            const bounds = this.map.getBounds();
            const bbox = `${bounds.getWest().toFixed(6)},${bounds.getSouth().toFixed(6)},${bounds.getEast().toFixed(6)},${bounds.getNorth().toFixed(6)}`;
            
            // Check if bounds have changed significantly (prevent duplicate calls)
            if (this.lastBounds && this.lastBounds === bbox) {
                return;
            }
            
            this.lastBounds = bbox;
            console.log('Map bounds changed, fetching new data for bbox:', bbox);
            
            try {
                this.showSidebarLoading();
                const data = await this.fetchListings(bbox);
                this.updateSidebar(data);
            } catch (error) {
                console.error('Error loading data for current view:', error);
            } finally {
                this.hideSidebarLoading();
            }
        }, 1000); // Increased debounce to 1 second to reduce API calls
    }
    
    toggleSidebar() {
        const container = document.querySelector('.big-map-container');
        if (container) {
            container.classList.toggle('sidebar-open');
        }
    }
    
    locateUser() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by this browser.');
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                this.map.setView([latitude, longitude], 14);
                
                // Add user marker
                L.marker([latitude, longitude], {
                    icon: L.divIcon({
                        className: 'user-location-marker',
                        html: '<div class="user-marker">📍</div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    })
                }).addTo(this.map);
            },
            (error) => {
                console.error('Error getting location:', error);
                alert('Could not get your location. Please check your browser permissions.');
            }
        );
    }
    
    fitBounds() {
        if (this.currentMarkers.length > 0) {
            const group = new L.featureGroup(this.currentMarkers);
            this.map.fitBounds(group.getBounds().pad(0.1));
        }
    }
    
    resetView() {
        this.map.setView(window.geotourBigMap.defaultCenter, window.geotourBigMap.defaultZoom);
    }
    
    applyFilter(filterType, filterValue) {
        // Build new URL with filter
        const url = new URL(window.location);
        
        // Add or update the filter parameter
        url.searchParams.set(filterType, filterValue);
        
        // Navigate to the filtered URL
        window.location.href = url.toString();
    }
    
    removeFilter(filterType) {
        // Build new URL without specific filter
        const url = new URL(window.location);
        url.searchParams.delete(filterType);
        
        // Navigate to the URL without this filter
        window.location.href = url.toString();
    }
    
    clearAllFilters() {
        // Navigate to clean listing page
        window.location.href = '/listing';
    }
    
    showLoading(mapOnly = false) {
        this.isLoading = true;
        
        if (!mapOnly) {
            const overlay = document.getElementById('map-loading-overlay');
            if (overlay) {
                overlay.classList.remove('hidden');
            }
        }
        
        const container = document.getElementById('listings-container');
        if (container) {
            container.classList.add('loading');
            container.innerHTML = '';
        }
        
        const countElement = document.getElementById('results-count');
        if (countElement) {
            countElement.textContent = window.geotourBigMap.strings.loadingListings;
        }
    }
    
    hideLoading() {
        this.isLoading = false;
        
        const overlay = document.getElementById('map-loading-overlay');
        if (overlay) {
            overlay.classList.add('hidden');
        }
        
        const container = document.getElementById('listings-container');
        if (container) {
            container.classList.remove('loading');
        }
    }
    
    showSidebarLoading() {
        const overlay = document.getElementById('sidebar-loading-overlay');
        if (overlay) {
            overlay.classList.add('active');
        }
    }
    
    hideSidebarLoading() {
        const overlay = document.getElementById('sidebar-loading-overlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
    }
    
    showError(message) {
        const container = document.getElementById('listings-container');
        if (container) {
            container.innerHTML = `<div class="error-message">${message}</div>`;
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new BigMapUI();
});