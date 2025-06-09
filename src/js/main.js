// src/js/main.js
import '../scss/main.scss';
import 'leaflet/dist/leaflet.css'; // Import Leaflet CSS
import L from 'leaflet'; // Import Leaflet directly

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
    console.log('DOM loaded, checking for map container...');
    
    // Simple direct map initialization
    const mapContainer = document.getElementById('listing-map');
    if (mapContainer) {
        console.log('Found map container, initializing map...');
        
        // Initialize map with Crete center
        const map = L.map('listing-map').setView([35.2401, 24.8093], 10);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Add a marker
        L.marker([35.2401, 24.8093]).addTo(map)
            .bindPopup('Crete, Greece')
            .openPopup();
            
        console.log('Map initialized successfully!');
    } else {
        console.log('No map container found with ID "listing-map"');
    }
    
    // Initialize other modules if they exist
    try {
        if (typeof initializeMainMenu === 'function') initializeMainMenu();
        if (typeof initializeHeader === 'function') initializeHeader();
        if (typeof initializeHero === 'function') initializeHero();
    } catch (error) {
        console.log('Some modules not available:', error.message);
    }
});