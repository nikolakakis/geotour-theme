// src/js/main.js
import '../scss/main.scss';

// Import modules
import { initializeMainMenu } from './modules/navigation/main.js';
import { initializeHeader } from './modules/header/main.js';
import { initializeHero } from './modules/hero/main.js';
import { initializeAllMaps } from './modules/maps/main.js';
import { initializeGallery } from './modules/gallery/main.js';
import { BigMapUI } from './modules/big-map/main.js';
import { HeroParallax } from './modules/hero/parallax.js';

// Check if Leaflet is available globally (from plugin)
if (typeof L !== 'undefined') {
    console.log('Leaflet found from plugin, version:', L.version);
    
    // Fix Leaflet default icons only if needed
    if (L.Icon && L.Icon.Default && L.Icon.Default.prototype._getIconUrl) {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
            iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
        });
    }
} else {
    console.warn('Leaflet not found - maps may not work properly');
}

console.log('Geotour Mobile First theme loaded.');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing modules...');
    
    // Initialize modules
    initializeMainMenu();
    initializeHeader();
    initializeHero();
    initializeAllMaps();
    initializeGallery(); // Initialize gallery functionality
    
    console.log('Modules initialized.');
});

// Add smooth scrolling behavior for scroll-to-content buttons
document.addEventListener('DOMContentLoaded', function() {
    const scrollButtons = document.querySelectorAll('.scroll-to-content-btn');
    
    scrollButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Initialize homepage parallax effect using the imported HeroParallax class
    if (typeof HeroParallax !== 'undefined') {
        new HeroParallax();
    }
});