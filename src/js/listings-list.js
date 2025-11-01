/**
 * Listings List JavaScript
 * Handles scroll-to functionality and interactive features for the listings list
 * 
 * @package Geotour_Mobile_First
 */

// Import styles
import '../scss/listings-list.scss';

class ListingsList {
    constructor() {
        this.container = null;
        this.highlightId = null;
        this.init();
    }

    /**
     * Initialize the listings list functionality
     */
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    /**
     * Setup all functionality
     */
    setup() {
        this.container = document.querySelector('.listings-list');
        
        if (!this.container) {
            console.warn('Listings list container not found');
            return;
        }

        // Get highlight ID from data attribute
        this.highlightId = this.container.dataset.highlightId;

        console.log('Listings list initialized, highlight ID:', this.highlightId);

        // If there's a highlight ID, scroll to it
        if (this.highlightId && this.highlightId !== '0') {
            this.scrollToHighlightedItem();
        }

        // Setup smooth scrolling for pagination links
        this.setupSmoothScrolling();
    }

    /**
     * Scroll to the highlighted listing item
     */
    scrollToHighlightedItem() {
        const targetItem = document.getElementById(`listing-${this.highlightId}`);
        
        if (!targetItem) {
            console.warn(`Target listing #listing-${this.highlightId} not found`);
            return;
        }

        console.log('Scrolling to listing:', this.highlightId);

        // Use multiple timing strategies to ensure scroll happens
        const attemptScroll = () => {
            // Calculate offset (accounting for fixed headers if any)
            const headerOffset = this.getHeaderOffset();
            const elementPosition = targetItem.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

            console.log('Scroll position calculated:', offsetPosition);

            // Smooth scroll to position
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });

            // Add a temporary extra highlight effect
            targetItem.classList.add('listing-item--flash');
            
            setTimeout(() => {
                targetItem.classList.remove('listing-item--flash');
            }, 2000);
        };

        // Try scrolling after different delays to catch various load states
        setTimeout(attemptScroll, 100);  // Quick attempt
        setTimeout(attemptScroll, 300);  // After initial render
        setTimeout(attemptScroll, 600);  // After images might load
        
        // Also scroll on window load (for slower connections)
        if (document.readyState !== 'complete') {
            window.addEventListener('load', () => {
                setTimeout(attemptScroll, 100);
            }, { once: true });
        }
    }

    /**
     * Get the offset for fixed headers
     * @returns {number} Header offset in pixels
     */
    getHeaderOffset() {
        const header = document.querySelector('.site-header');
        
        if (header) {
            const headerStyles = window.getComputedStyle(header);
            if (headerStyles.position === 'fixed' || headerStyles.position === 'sticky') {
                return header.offsetHeight + 20; // Add 20px extra padding
            }
        }

        return 20; // Default offset
    }

    /**
     * Setup smooth scrolling behavior for pagination
     */
    setupSmoothScrolling() {
        const paginationLinks = document.querySelectorAll('.listings-pagination__link');
        
        paginationLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                // Let the browser handle the navigation
                // but scroll to top of listings after page load
                sessionStorage.setItem('scrollToListings', 'true');
            });
        });

        // Check if we need to scroll after pagination
        if (sessionStorage.getItem('scrollToListings') === 'true') {
            sessionStorage.removeItem('scrollToListings');
            
            setTimeout(() => {
                const listingsContainer = document.querySelector('.listings-list-container');
                if (listingsContainer) {
                    const headerOffset = this.getHeaderOffset();
                    const elementPosition = listingsContainer.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            }, 100);
        }
    }

    /**
     * Helper method to get URL parameter
     * @param {string} name Parameter name
     * @returns {string|null} Parameter value
     */
    static getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        const results = regex.exec(location.search);
        return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    /**
     * Helper method to update URL parameter without page reload
     * @param {string} key Parameter key
     * @param {string} value Parameter value
     */
    static updateUrlParameter(key, value) {
        const url = new URL(window.location);
        url.searchParams.set(key, value);
        window.history.pushState({}, '', url);
    }

    /**
     * Helper method to remove URL parameter
     * @param {string} key Parameter key
     */
    static removeUrlParameter(key) {
        const url = new URL(window.location);
        url.searchParams.delete(key);
        window.history.pushState({}, '', url);
    }
}

/**
 * Listings Filter Manager
 * Handles all filter and sort interactions
 */
class ListingsFilterManager {
    constructor() {
        this.filterHeader = null;
        this.filtersPanel = null;
        this.categoryButtons = [];
        this.selectedCategories = new Set();
        this.init();
    }

    /**
     * Initialize filter functionality
     */
    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    /**
     * Setup all filter controls
     */
    setup() {
        this.filterHeader = document.querySelector('.listings-filter-header');
        
        if (!this.filterHeader) {
            return;
        }

        this.filtersPanel = document.getElementById('listings-filters-panel');
        
        // Start with filters collapsed (hidden) by default
        if (this.filtersPanel) {
            this.filtersPanel.setAttribute('hidden', '');
        }
        
        // Setup all controls
        this.setupFilterToggle();
        this.setupCategoryFilters();
        this.setupRegionFilter();
        this.setupSortSelect();
        this.setupSearchBox();
        this.setupActionButtons();
        this.updateActiveFilters();
        
        console.log('Listings filters initialized');
    }

    /**
     * Setup mobile filter toggle
     */
    setupFilterToggle() {
        const toggleBtn = this.filterHeader.querySelector('.listings-filter-toggle');
        
        if (!toggleBtn || !this.filtersPanel) {
            return;
        }

        toggleBtn.addEventListener('click', () => {
            const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
            
            toggleBtn.setAttribute('aria-expanded', !isExpanded);
            this.filtersPanel.hidden = isExpanded;
            
            if (!isExpanded) {
                this.filtersPanel.classList.add('is-open');
            } else {
                this.filtersPanel.classList.remove('is-open');
            }
        });
    }

    /**
     * Setup category icon filters
     */
    setupCategoryFilters() {
        this.categoryButtons = Array.from(
            this.filterHeader.querySelectorAll('.listings-filter-category')
        );

        // Load selected categories from URL
        const urlCategories = ListingsList.getUrlParameter('listing_category');
        if (urlCategories) {
            urlCategories.split(',').forEach(slug => {
                this.selectedCategories.add(slug);
            });
        }

        // Add click handlers
        this.categoryButtons.forEach(button => {
            const slug = button.dataset.slug;
            
            // Set initial state
            if (this.selectedCategories.has(slug)) {
                button.classList.add('is-active');
                button.setAttribute('aria-pressed', 'true');
            }

            button.addEventListener('click', () => {
                this.toggleCategory(slug, button);
            });
        });
    }

    /**
     * Toggle category selection
     * @param {string} slug Category slug
     * @param {HTMLElement} button Button element
     */
    toggleCategory(slug, button) {
        if (this.selectedCategories.has(slug)) {
            this.selectedCategories.delete(slug);
            button.classList.remove('is-active');
            button.setAttribute('aria-pressed', 'false');
        } else {
            this.selectedCategories.add(slug);
            button.classList.add('is-active');
            button.setAttribute('aria-pressed', 'true');
        }

        this.updateActiveFilters();
    }

    /**
     * Setup region filter
     */
    setupRegionFilter() {
        const regionSelect = document.getElementById('listing-region-select');
        
        if (!regionSelect) {
            return;
        }

        regionSelect.addEventListener('change', () => {
            this.updateActiveFilters();
        });
    }

    /**
     * Setup sort select
     */
    setupSortSelect() {
        const sortSelect = document.getElementById('listing-sort-select');
        
        if (!sortSelect) {
            return;
        }

        sortSelect.addEventListener('change', () => {
            // Apply sorting immediately
            this.applyFilters();
        });
    }

    /**
     * Setup search box
     */
    setupSearchBox() {
        const searchInput = document.getElementById('listing-search-input');
        const searchButton = this.filterHeader.querySelector('.listings-filter-search__button');
        
        if (!searchInput) {
            return;
        }

        // Search on button click
        if (searchButton) {
            searchButton.addEventListener('click', () => {
                this.applyFilters();
            });
        }

        // Search on Enter key
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.applyFilters();
            }
        });
    }

    /**
     * Setup action buttons
     */
    setupActionButtons() {
        const applyBtn = this.filterHeader.querySelector('.listings-filter-apply');
        const clearBtn = this.filterHeader.querySelector('.listings-filter-clear');

        if (applyBtn) {
            applyBtn.addEventListener('click', () => {
                this.applyFilters();
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                this.clearAllFilters();
            });
        }
    }

    /**
     * Update active filters display
     */
    updateActiveFilters() {
        const activeFiltersEl = this.filterHeader.querySelector('.listings-active-filters');
        const tagsContainer = this.filterHeader.querySelector('.listings-active-filters__tags');
        
        if (!activeFiltersEl || !tagsContainer) {
            return;
        }

        // Clear existing tags
        tagsContainer.innerHTML = '';

        let hasFilters = false;

        // Add category tags
        this.selectedCategories.forEach(slug => {
            const button = this.categoryButtons.find(btn => btn.dataset.slug === slug);
            if (button) {
                const tag = this.createFilterTag(button.dataset.name, () => {
                    this.toggleCategory(slug, button);
                });
                tagsContainer.appendChild(tag);
                hasFilters = true;
            }
        });

        // Add region tag
        const regionSelect = document.getElementById('listing-region-select');
        if (regionSelect && regionSelect.value) {
            const selectedOption = regionSelect.options[regionSelect.selectedIndex];
            const tag = this.createFilterTag(selectedOption.text, () => {
                regionSelect.value = '';
                this.updateActiveFilters();
            });
            tagsContainer.appendChild(tag);
            hasFilters = true;
        }

        // Show/hide active filters section
        activeFiltersEl.hidden = !hasFilters;
    }

    /**
     * Create a filter tag element
     * @param {string} label Tag label
     * @param {Function} onRemove Callback when tag is removed
     * @returns {HTMLElement} Tag element
     */
    createFilterTag(label, onRemove) {
        const tag = document.createElement('button');
        tag.type = 'button';
        tag.className = 'listings-active-filter-tag';
        tag.innerHTML = `
            <span class="listings-active-filter-tag__label">${this.escapeHtml(label)}</span>
            <span class="listings-active-filter-tag__remove" aria-label="Remove filter">×</span>
        `;
        
        tag.addEventListener('click', () => {
            onRemove();
            this.updateActiveFilters();
        });

        return tag;
    }

    /**
     * Apply all filters and navigate
     */
    applyFilters() {
        const url = new URL(window.location.href);
        const searchParams = new URLSearchParams();

        // Get search query
        const searchInput = document.getElementById('listing-search-input');
        if (searchInput && searchInput.value.trim()) {
            searchParams.set('listing_search', searchInput.value.trim());
        }

        // Get categories
        if (this.selectedCategories.size > 0) {
            searchParams.set('listing_category', Array.from(this.selectedCategories).join(','));
        }

        // Get region
        const regionSelect = document.getElementById('listing-region-select');
        if (regionSelect && regionSelect.value) {
            searchParams.set('listing_region', regionSelect.value);
        }

        // Get sort
        const sortSelect = document.getElementById('listing-sort-select');
        if (sortSelect && sortSelect.value) {
            const sortValue = sortSelect.value;
            
            // Parse combined sort value
            if (sortValue.includes('-')) {
                const [orderby, order] = sortValue.split('-');
                searchParams.set('listing_orderby', orderby);
                searchParams.set('listing_order', order.toUpperCase());
            } else {
                searchParams.set('listing_orderby', sortValue);
                searchParams.set('listing_order', sortValue === 'title' ? 'ASC' : 'DESC');
            }
        }

        // Remove highlight_post and listing_page on new filter
        searchParams.delete('highlight_post');
        searchParams.delete('listing_page');

        // Navigate with new parameters
        window.location.search = searchParams.toString();
    }

    /**
     * Clear all filters
     */
    clearAllFilters() {
        // Clear categories
        this.selectedCategories.clear();
        this.categoryButtons.forEach(button => {
            button.classList.remove('is-active');
            button.setAttribute('aria-pressed', 'false');
        });

        // Clear region
        const regionSelect = document.getElementById('listing-region-select');
        if (regionSelect) {
            regionSelect.value = '';
        }

        // Clear search
        const searchInput = document.getElementById('listing-search-input');
        if (searchInput) {
            searchInput.value = '';
        }

        // Reset sort to default
        const sortSelect = document.getElementById('listing-sort-select');
        if (sortSelect) {
            sortSelect.value = 'date';
        }

        // Navigate to clean URL
        window.location.href = window.location.pathname;
    }

    /**
     * Escape HTML for safe insertion
     * @param {string} text Text to escape
     * @returns {string} Escaped text
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when script loads
const listingsList = new ListingsList();
const listingsFilter = new ListingsFilterManager();

// Export for potential external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ListingsList, ListingsFilterManager };
}
