// src/js/modules/maps/main.js
import L from 'leaflet';
// import 'leaflet.markercluster'; // We'll uncomment this when we implement clustering

// Store initialized maps to prevent re-initialization
const initializedMaps = new Set();

export function initializeGeotourMap(mapElementId, mapData) {
    if (!document.getElementById(mapElementId) || initializedMaps.has(mapElementId)) {
        // console.warn(`Map element with ID '${mapElementId}' not found or already initialized.`);
        return null;
    }

    const mapElement = document.getElementById(mapElementId);
    
    // Basic Leaflet map initialization
    // Centered on Crete by default, adjust as needed
    const map = L.map(mapElementId).setView([35.2401, 24.8093], 8);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Example: Add a marker if coordinates are provided
    if (mapData && mapData.coordinates) {
        L.marker(mapData.coordinates).addTo(map)
            .bindPopup(mapData.popupText || 'A default marker popup.')
            .openPopup();
        map.setView(mapData.coordinates, mapData.zoomLevel || 13);
    } else {
        // console.log(`No specific coordinates provided for map ${mapElementId}. Displaying default view.`);
    }
    
    initializedMaps.add(mapElementId);
    console.log(`Geotour map initialized on element: ${mapElementId}`);
    return map;
}

// Example of how you might initialize maps globally or specifically
export function initializeAllMaps() {
    // For a single listing map (expects a div with id="listing-map" and data in `geotourMapData.single`)
    const singleMapElement = document.getElementById('listing-map');
    if (singleMapElement && typeof geotourMapData !== 'undefined' && geotourMapData.single) {
        initializeGeotourMap('listing-map', geotourMapData.single);
    }

    // For an archive map (expects a div with id="archive-map" and data in `geotourMapData.archive`)
    const archiveMapElement = document.getElementById('archive-map');
    if (archiveMapElement && typeof geotourMapData !== 'undefined' && geotourMapData.archive) {
        // Here you would typically pass an array of listings for marker clustering
        initializeGeotourMap('archive-map', {
            // center: [35.2401, 24.8093], // Center of Crete
            // zoom: 8,
            // listings: geotourMapData.archive.listings // Array of listing data
        });
        // Add marker clustering logic here later
    }
}
