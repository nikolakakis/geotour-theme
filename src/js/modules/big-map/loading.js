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
            container.innerHTML = `
                <div class="error-message">
                    <svg viewBox="0 0 24 24" fill="currentColor" style="width: 48px; height: 48px; margin-bottom: 1rem; color: #ef4444;">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                    <p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">${message}</p>
                    <p style="font-size: 0.9rem; color: #64748b;">The page will refresh automatically...</p>
                </div>
            `;
        }
        
        // Also update the count element
        const countElement = document.getElementById('results-count');
        if (countElement) {
            countElement.textContent = 'Error loading data';
        }
    }
    
    getLoadingState() {
        return this.isLoading;
    }
}
