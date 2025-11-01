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
            return;
        }

        // Get highlight ID from data attribute
        this.highlightId = this.container.dataset.highlightId;

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
            return;
        }

        // Wait a moment for page to fully render
        setTimeout(() => {
            // Calculate offset (accounting for fixed headers if any)
            const headerOffset = this.getHeaderOffset();
            const elementPosition = targetItem.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

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

        }, 300);
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

// Initialize when script loads
const listingsList = new ListingsList();

// Export for potential external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ListingsList;
}
