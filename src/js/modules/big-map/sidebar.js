// ==========================================================================
// BIG MAP SIDEBAR HANDLER
// ==========================================================================
// Handles sidebar functionality, listing display, and responsive behavior

export class BigMapSidebar {
    constructor() {
        this.sidebar = null;
        this.isMobile = window.innerWidth <= 1024;
        this.sidebarVisible = !this.isMobile;
    }
    
    initialize() {
        this.sidebar = document.getElementById('map-sidebar');
        this.setupSidebarState();
        this.setupEventListeners();
        this.setupSearchEventListeners();
        this.addSearchToFilters();
        
        // Window resize handler
        window.addEventListener('resize', () => {
            const wasMobile = this.isMobile;
            this.isMobile = window.innerWidth <= 1024;
            
            // Reset sidebar state if screen size category changed
            if (wasMobile !== this.isMobile) {
                this.setupSidebarState();
            }
        });
    }
    
    setupSidebarState() {
        const container = document.querySelector('.big-map-container');
        const sidebar = this.sidebar;
        const floatingBtn = document.getElementById('floating-sidebar-toggle');
        
        if (this.isMobile) {
            // Mobile: hidden by default
            sidebar.classList.remove('open');
            container.classList.remove('sidebar-open');
            if (floatingBtn) floatingBtn.style.display = 'block';
        } else {
            // Desktop: visible by default
            sidebar.classList.remove('hidden');
            if (floatingBtn) floatingBtn.style.display = 'none';
        }
    }
    
    setupEventListeners() {
        // Sidebar toggle for all screen sizes
        const toggleBtn = document.getElementById('sidebar-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggleSidebar());
        }
        
        // Floating sidebar toggle button
        const floatingToggleBtn = document.getElementById('floating-sidebar-toggle');
        if (floatingToggleBtn) {
            floatingToggleBtn.addEventListener('click', () => this.toggleSidebar());
        }
        
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
    
    updateSidebar(listings) {
        const container = document.getElementById('listings-container');
        const countElement = document.getElementById('results-count');
        
        if (!container || !countElement) return;
        
        // Update count
        const count = listings.length;
        countElement.textContent = window.geotourBigMap.strings.resultsFound.replace('{count}', count);
        
        // Update search input if it exists
        this.updateSearchInput();
        
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
        
        return item;
    }
    
    addListingItemHandlers(item, listing, onItemClick, onFilterClick) {
        // Add click event for the item itself
        item.addEventListener('click', (e) => {
            // Don't trigger if clicking on a meta tag
            if (!e.target.classList.contains('meta-tag')) {
                onItemClick(listing);
                
                // Hide sidebar on mobile after clicking a listing
                if (this.isMobile) {
                    this.hideSidebarOnMobile();
                }
            }
        });
        
        // Add click events for meta tags (categories, regions, tags)
        item.querySelectorAll('.meta-tag[data-filter-type]').forEach(tag => {
            tag.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent item click
                const filterType = tag.dataset.filterType;
                const filterValue = tag.dataset.filterValue;
                onFilterClick(filterType, filterValue);
            });
        });
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
    
    toggleSidebar() {
        const container = document.querySelector('.big-map-container');
        const sidebar = this.sidebar;
        const floatingBtn = document.getElementById('floating-sidebar-toggle');
        
        if (this.isMobile) {
            // Mobile behavior: use open/close classes
            const isOpen = sidebar.classList.contains('open');
            if (isOpen) {
                sidebar.classList.remove('open');
                container.classList.remove('sidebar-open');
                this.sidebarVisible = false;
            } else {
                sidebar.classList.add('open');
                container.classList.add('sidebar-open');
                this.sidebarVisible = true;
            }
        } else {
            // Desktop behavior: use hidden class
            const isHidden = sidebar.classList.contains('hidden');
            if (isHidden) {
                sidebar.classList.remove('hidden');
                this.sidebarVisible = true;
                if (floatingBtn) floatingBtn.style.display = 'none';
            } else {
                sidebar.classList.add('hidden');
                this.sidebarVisible = false;
                if (floatingBtn) floatingBtn.style.display = 'block';
            }
        }
    }
    
    hideSidebarOnMobile() {
        if (!this.isMobile) return;
        
        const container = document.querySelector('.big-map-container');
        const sidebar = this.sidebar;
        
        sidebar.classList.remove('open');
        container.classList.remove('sidebar-open');
        this.sidebarVisible = false;
    }
    
    applyFilter(filterType, filterValue) {
        // Build new URL with filter
        const url = new URL(window.location);
        
        // Add or update the filter parameter
        url.searchParams.set(filterType, filterValue);
        
        // Navigate to the filtered URL
        window.location.href = url.toString();
    }
    
    clearAllFilters() {
        // Navigate to clean listing page
        window.location.href = '/listing';
    }
    
    getSidebarVisibility() {
        return this.sidebarVisible;
    }
    
    getIsMobile() {
        return this.isMobile;
    }
    
    // Search functionality methods
    updateSearchInput() {
        const searchInput = document.getElementById('map-search-input');
        if (searchInput) {
            const currentSearch = new URLSearchParams(window.location.search).get('search') || '';
            if (searchInput.value !== currentSearch) {
                searchInput.value = currentSearch;
            }
        }
    }
    
    createSearchInput() {
        const currentSearch = new URLSearchParams(window.location.search).get('search') || '';
        
        const searchContainer = document.createElement('div');
        searchContainer.className = 'map-search-box';
        searchContainer.innerHTML = `
            <input type="text" id="map-search-input" placeholder="${window.geotourBigMap.strings.searchPlaceholder || 'Search listings...'}" value="${currentSearch}">
            <button type="button" id="map-search-apply" title="${window.geotourBigMap.strings.applySearch || 'Apply search'}">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                    <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/>
                </svg>
            </button>
        `;
        
        return searchContainer;
    }
    
    setupSearchEventListeners() {
        // Apply search
        document.addEventListener('click', (e) => {
            if (e.target.closest('#map-search-apply')) {
                const input = document.getElementById('map-search-input');
                if (input) {
                    this.applySearch(input.value.trim());
                }
            }
        });
        
        // Enter key support
        document.addEventListener('keypress', (e) => {
            if (e.target.id === 'map-search-input' && e.key === 'Enter') {
                this.applySearch(e.target.value.trim());
            }
        });
    }
    
    applySearch(searchTerm) {
        const url = new URL(window.location);
        
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        
        window.location.href = url.toString();
    }
    
    addSearchToFilters() {
        // Always add search input since it's now always visible
        // Find or create the active-filters section
        let activeFiltersSection = document.querySelector('.active-filters');
        if (!activeFiltersSection) {
            // Create the active-filters section if it doesn't exist
            activeFiltersSection = document.createElement('div');
            activeFiltersSection.className = 'active-filters';
            activeFiltersSection.innerHTML = `
                <div class="filters-header">
                    <span>${window.geotourBigMap.strings.filteredBy}</span>
                    <a href="/listing" class="clear-filters">${window.geotourBigMap.strings.clearFilters}</a>
                </div>
            `;
            
            // Insert before results-header
            const resultsHeader = document.querySelector('.results-header');
            if (resultsHeader) {
                resultsHeader.parentNode.insertBefore(activeFiltersSection, resultsHeader);
            }
        }
        
        // Add search input to the active filters if it doesn't exist
        const filtersHeader = activeFiltersSection.querySelector('.filters-header');
        if (filtersHeader && !document.getElementById('map-search-input')) {
            const searchContainer = this.createSearchInput();
            filtersHeader.insertAdjacentElement('afterend', searchContainer);
        }
    }
}
