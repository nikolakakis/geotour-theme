/**
 * Improve Google Maps accessibility
 * Add accessible names to map control buttons
 */
function improveMapAccessibility() {
    // Wait for Google Maps to load
    const mapContainer = document.querySelector('.geotour-map-container');
    if (!mapContainer) return;
    
    // Use MutationObserver to detect when map controls are added
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                // Look for map control buttons
                const mapButtons = mapContainer.querySelectorAll('div[role="button"]');
                mapButtons.forEach(function(button, index) {
                    if (!button.getAttribute('aria-label') && !button.getAttribute('title')) {
                        // Add accessible names based on common Google Maps controls
                        const buttonText = button.textContent || button.innerText;
                        if (buttonText) {
                            button.setAttribute('aria-label', buttonText);
                        } else {
                            // Fallback labels for common map controls
                            const commonLabels = [
                                'Zoom in',
                                'Zoom out', 
                                'Toggle fullscreen',
                                'Show street view',
                                'Map type control',
                                'Pan map',
                                'Reset map view',
                                'Show location'
                            ];
                            if (commonLabels[index]) {
                                button.setAttribute('aria-label', commonLabels[index]);
                            } else {
                                button.setAttribute('aria-label', `Map control ${index + 1}`);
                            }
                        }
                    }
                });
                
                // Also handle map images and other interactive elements
                const mapImages = mapContainer.querySelectorAll('img:not([alt])');
                mapImages.forEach(function(img) {
                    img.setAttribute('alt', 'Map tile');
                });
            }
        });
    });
    
    // Start observing
    observer.observe(mapContainer, {
        childList: true,
        subtree: true
    });
    
    // Also run immediately in case controls are already loaded
    setTimeout(function() {
        const mapButtons = mapContainer.querySelectorAll('div[role="button"]');
        mapButtons.forEach(function(button, index) {
            if (!button.getAttribute('aria-label') && !button.getAttribute('title')) {
                button.setAttribute('aria-label', `Map control ${index + 1}`);
            }
        });
    }, 1000);
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', improveMapAccessibility);

// Also run when page is fully loaded (for async maps)
window.addEventListener('load', function() {
    setTimeout(improveMapAccessibility, 2000);
});