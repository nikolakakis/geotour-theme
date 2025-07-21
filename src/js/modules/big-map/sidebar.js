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

        // Add a class if the listing is part of a route
        if (listing.route_order) {
            item.classList.add('is-route-listing');
        }
        
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
        
        // Route section for sidebar
        let routeSection = '';
        if (listing.route_order) {
            routeSection = `
                <div class="listing-route-info">
                    <span class="route-stop-number clickable" data-listing-id="${listing.id}" data-current-order="${listing.route_order}" title="Click to change order">Stop #${listing.route_order}</span>
                    <button class="route-action-btn remove-from-route" data-listing-id="${listing.id}" title="Remove from route">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19,13H5V11H19V13Z"/>
                        </svg>
                    </button>
                </div>
            `;
        } else {
            routeSection = `
                <div class="listing-route-info">
                    <button class="route-action-btn add-to-route" data-listing-id="${listing.id}" title="Add to route">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                        </svg>
                    </button>
                </div>
            `;
        }
        
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
                ${routeSection}
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
        
        // Add click events for route action buttons
        item.querySelectorAll('.route-action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent item click
                const listingId = btn.dataset.listingId;
                if (btn.classList.contains('add-to-route')) {
                    this.addToRoute(listingId);
                } else if (btn.classList.contains('remove-from-route')) {
                    this.removeFromRoute(listingId);
                }
            });
        });
        
        // Add click event for route stop number reordering
        item.querySelectorAll('.route-stop-number.clickable').forEach(stopNumber => {
            stopNumber.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent item click
                const listingId = stopNumber.dataset.listingId;
                const currentOrder = parseInt(stopNumber.dataset.currentOrder);
                this.showReorderDialog(listingId, currentOrder);
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
        // Create a mapping of current positions to listing IDs
        const orderMap = {};
        routeIds.forEach((id, index) => {
            orderMap[index + 1] = id;
        });
        
        // Remove the item being moved
        delete orderMap[currentOrder];
        
        // Create new order array
        const newOrderArray = [];
        
        // Fill positions, shifting as needed
        for (let i = 1; i <= routeIds.length; i++) {
            if (i === newOrder) {
                // Insert the moved item at new position
                newOrderArray[i - 1] = listingId;
            } else if (currentOrder < newOrder) {
                // Moving forward: shift items back to fill the gap
                if (i > currentOrder && i <= newOrder) {
                    newOrderArray[i - 1] = orderMap[i];
                } else if (i > newOrder) {
                    newOrderArray[i - 1] = orderMap[i];
                } else {
                    newOrderArray[i - 1] = orderMap[i];
                }
            } else {
                // Moving backward: shift items forward to make room
                if (i >= newOrder && i < currentOrder) {
                    newOrderArray[i - 1] = orderMap[i];
                } else if (i < newOrder) {
                    newOrderArray[i - 1] = orderMap[i];
                } else {
                    newOrderArray[i - 1] = orderMap[i];
                }
            }
        }
        
        // Rebuild the route array with correct ordering
        const reorderedIds = [];
        for (let i = 1; i <= routeIds.length; i++) {
            if (i === newOrder) {
                reorderedIds.push(listingId);
            } else {
                // Find what should go in position i
                let idForPosition = null;
                if (currentOrder < newOrder) {
                    // Moving forward
                    if (i < currentOrder) {
                        idForPosition = orderMap[i];
                    } else if (i > currentOrder && i <= newOrder) {
                        idForPosition = orderMap[i];
                    } else if (i > newOrder) {
                        idForPosition = orderMap[i];
                    }
                } else {
                    // Moving backward  
                    if (i < newOrder) {
                        idForPosition = orderMap[i];
                    } else if (i >= newOrder && i < currentOrder) {
                        idForPosition = orderMap[i];
                    } else if (i > currentOrder) {
                        idForPosition = orderMap[i];
                    }
                }
                
                if (idForPosition) {
                    reorderedIds.push(idForPosition);
                }
            }
        }
        
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
