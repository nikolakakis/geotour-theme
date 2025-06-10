// src/js/modules/maps/main.js
import L from 'leaflet';
import 'leaflet.vectorgrid'; // Import the vectorGrid plugin
import { vectorTileLayerStyles } from './vector-styles'; // Import the styles

// Store initialized maps to prevent re-initialization
const initializedMaps = new Set();

// Fix for default markers in Leaflet with Vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

export function initializeGeotourMap(mapElementId, mapData = {}) {
    if (!document.getElementById(mapElementId) || initializedMaps.has(mapElementId)) {
        return null;
    }

    const mapElement = document.getElementById(mapElementId);
    
    // Default center (Crete, Greece)
    const defaultCenter = [35.2401, 24.8093];
    const defaultZoom = 9; // Adjusted default zoom for better initial view with vector tiles
    
    // Determine map center and zoom
    let center = defaultCenter;
    let zoom = defaultZoom;
    
    if (mapData.coordinates) {
        center = mapData.coordinates;
        zoom = mapData.zoomLevel || 13;
    }
    
    // Initialize the map
    const map = L.map(mapElementId).setView(center, zoom);

    // MapTiler API Key and URL
    const MAPTILER_API_KEY = 'VrfcfMogFgrPX2raBcBO'; // Your provided API key
    const MAPTILER_URL = `https://api.maptiler.com/tiles/v3/{z}/{x}/{y}.pbf?key=${MAPTILER_API_KEY}`;

    const vectorTileOptions = {
        vectorTileLayerStyles: vectorTileLayerStyles,
        interactive: true, // Set to true if you want to interact with features
        getFeatureId: function(f) {
            // Attempt to get an ID, MapTiler v3 might use 'id' or '_id' or have none for some layers
            return f.properties.id || f.properties._id || f.id; 
        },
        maxNativeZoom: 14, // MapTiler vector tiles typically go up to z14
        attribution: '<a href="https://www.maptiler.com/copyright/" target="_blank">&copy; MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>',
    };

    const vectorGridLayer = L.vectorGrid.protobuf(MAPTILER_URL, vectorTileOptions);
    vectorGridLayer.addTo(map);

    // Set map background color via CSS on the map container itself
    // Ensure your SCSS for .geotour-map-container or specific map ID has:
    // background-color: #0d1a26; /* Dark blue-grey, or your preferred dark bg */

    // Add marker if coordinates are provided
    if (mapData.coordinates) {
        const marker = L.marker(mapData.coordinates).addTo(map);
        
        if (mapData.popupText) {
            marker.bindPopup(mapData.popupText).openPopup();
        }
    }
    
    initializedMaps.add(mapElementId);
    console.log(`Geotour vector map initialized on element: ${mapElementId}`);
    return map;
}

// Initialize maps based on data attributes (fallback if geotourMapData is not available)
export function initializeMapFromDataAttributes(mapElementId) {
    const mapElement = document.getElementById(mapElementId);
    if (!mapElement) return null;
    
    const lat = parseFloat(mapElement.dataset.lat);
    const lng = parseFloat(mapElement.dataset.lng);
    const title = mapElement.dataset.title;
    const permalink = mapElement.dataset.permalink;
    
    if (!isNaN(lat) && !isNaN(lng)) {
        const mapData = {
            coordinates: [lat, lng],
            popupText: `<h5>${title}</h5><p><a href="${permalink}">View Details</a></p>`,
            zoomLevel: 15
        };
        
        return initializeGeotourMap(mapElementId, mapData);
    }
    
    return null;
}

// Main initialization function
export function initializeAllMaps() {
    console.log('Initializing all maps...');
    
    // Initialize single listing map
    const singleMapElement = document.getElementById('listing-map');
    if (singleMapElement) {
        console.log('Found single listing map element');
        
        // Try to use geotourMapData first
        if (typeof geotourMapData !== 'undefined' && geotourMapData.single) {
            console.log('Using geotourMapData for single map');
            initializeGeotourMap('listing-map', geotourMapData.single);
        } else {
            console.log('Using data attributes for single map');
            // Fallback to data attributes
            initializeMapFromDataAttributes('listing-map');
        }
    }

    // Initialize archive map
    const archiveMapElement = document.getElementById('archive-map');
    if (archiveMapElement) {
        console.log('Found archive map element');
        
        if (typeof geotourMapData !== 'undefined' && geotourMapData.archive) {
            console.log('Using geotourMapData for archive map');
            initializeGeotourMap('archive-map', geotourMapData.archive);
        } else {
            console.log('Initializing default archive map');
            // Initialize with default view for now
            initializeGeotourMap('archive-map', {});
        }
    }
    
    console.log('Map initialization complete');
}
