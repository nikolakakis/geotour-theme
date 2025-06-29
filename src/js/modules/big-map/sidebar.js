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
}
