// ==========================================================================
// BIG MAP LOADING STATES
// ==========================================================================
// Handles loading indicators and error states

export class BigMapLoadingStates {
    constructor() {
        this.isLoading = false;
    }
    
    showLoading(mapOnly = false) {
        this.isLoading = true;
        
        // Show map loading indicator
        const mapIndicator = document.getElementById('map-loading-indicator');
        if (mapIndicator) {
            mapIndicator.classList.add('active');
        }
        
        // Show sidebar loading indicator if not map-only
        if (!mapOnly) {
            const sidebarIndicator = document.getElementById('sidebar-loading-indicator');
            if (sidebarIndicator) {
                sidebarIndicator.classList.add('active');
            }
            
            // Update text elements
            const countElement = document.getElementById('results-count');
            if (countElement) {
                countElement.textContent = window.geotourBigMap.strings.loadingListings;
            }
            
            const container = document.getElementById('listings-container');
            if (container) {
                container.classList.add('loading');
            }
        }
    }
    
    hideLoading() {
        this.isLoading = false;
        
        // Hide map loading indicator
        const mapIndicator = document.getElementById('map-loading-indicator');
        if (mapIndicator) {
            mapIndicator.classList.remove('active');
        }
        
        // Hide sidebar loading indicator
        const sidebarIndicator = document.getElementById('sidebar-loading-indicator');
        if (sidebarIndicator) {
            sidebarIndicator.classList.remove('active');
        }
        
        // Remove loading class from container
        const container = document.getElementById('listings-container');
        if (container) {
            container.classList.remove('loading');
        }
    }
    
    showError(message) {
        const container = document.getElementById('listings-container');
        if (container) {
            container.innerHTML = `<div class="error-message">${message}</div>`;
        }
    }
    
    getLoadingState() {
        return this.isLoading;
    }
}
