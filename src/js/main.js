// src/js/main.js
import '../scss/main.scss';
import 'leaflet/dist/leaflet.css'; // Import Leaflet CSS
import L from 'leaflet'; // Import Leaflet directly

// Import modules
import { initializeMainMenu } from './modules/navigation/main.js';
import { initializeHeader } from './modules/header/main.js';
import { initializeHero } from './modules/hero/main.js';
import { initializeAllMaps } from './modules/maps/main.js'; // Corrected import
import { initializeGallery } from './modules/gallery/main.js'; // Gallery module

// Fix Leaflet default icons
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

// Your other JavaScript code goes here
console.log('Geotour Mobile First theme loaded.');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing modules...');
    
    // Initialize modules
    initializeMainMenu();
    initializeHeader();
    initializeHero();
    initializeAllMaps(); // Corrected function call
    initializeGallery(); // Initialize gallery functionality
    
    // The direct map initialization previously here has been moved to initializeMaps in modules/maps/main.js
    // Ensure that initializeMaps handles the logic for finding 'listing-map' and setting up the map.
    // If 'listing-map' is not always present, initializeMaps should handle that gracefully.

    console.log('Modules initialized.');
});