// src/js/modules/maps/main.js
// Use global Leaflet provided by plugin instead of importing
// import L from 'leaflet';

// Store initialized maps to prevent re-initialization
const initializedMaps = new Set();

// Custom icon for archaeological sites
const archaeologicalSiteIcon = L.icon({
    iconUrl: '/wp-content/themes/geotour-theme/assets/graphics/map-pins/pin-archaeological-site.svg',
    iconSize: [64, 64], // Size of the icon (doubled from 32x32)
    iconAnchor: [32, 64], // Point of the icon which will correspond to marker's location (adjusted for new size)
    popupAnchor: [0, -64] // Point from which the popup should open relative to the iconAnchor (adjusted for new size)
});

// Function to create custom icon from configuration
function createCustomIcon(iconConfig) {
    if (!iconConfig || !iconConfig.iconUrl) {
        return archaeologicalSiteIcon; // Fallback to default
    }
    
    return L.icon({
        iconUrl: iconConfig.iconUrl,
        iconSize: iconConfig.iconSize || [64, 64],
        iconAnchor: iconConfig.iconAnchor || [32, 64],
        popupAnchor: iconConfig.popupAnchor || [0, -64]
    });
}

// Fix for default markers in Leaflet with Vite
if (typeof L !== 'undefined' && L.Icon && L.Icon.Default) {
    // Only fix if not already fixed by plugin
    if (L.Icon.Default.prototype._getIconUrl) {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
            iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
        });
    }
}

export function initializeGeotourMap(mapElementId, mapData = {}) {
    // Check if Leaflet is available
    if (typeof L === 'undefined') {
        console.error('Leaflet not found - make sure the geotour-crete-maps plugin is active');
        return null;
    }
    
    if (!document.getElementById(mapElementId) || initializedMaps.has(mapElementId)) {
        return null;
    }

    const mapElement = document.getElementById(mapElementId);
    
    // Default center (Crete, Greece)
    const defaultCenter = [35.2401, 24.8093];
    let defaultZoom = 9;
    if (mapElementId === 'listing-map') {
        defaultZoom = 12;
    }
    
    // Determine map center and zoom
    let center = defaultCenter;
    let zoom = defaultZoom;
    
    if (mapData.coordinates) {
        center = mapData.coordinates;
        // Use zoomLevel if provided, otherwise use defaultZoom
        zoom = typeof mapData.zoomLevel !== 'undefined' ? mapData.zoomLevel : defaultZoom;
    } else {
        // Try to get from data attributes
        const lat = parseFloat(mapElement.dataset.lat);
        const lng = parseFloat(mapElement.dataset.lng);
        const zoomAttr = parseInt(mapElement.dataset.zoom);
        
        if (!isNaN(lat) && !isNaN(lng)) {
            center = [lat, lng];
            zoom = !isNaN(zoomAttr) ? zoomAttr : defaultZoom;
        }
    }    // Check if this should be a static map
    const isStatic = mapData.isStatic || mapElement.dataset.static === 'true';
    const showPopup = mapData.showPopup !== false && mapElement.dataset.noPopup !== 'true';

    // Initialize the map
    const mapOptions = {
        center: center,
        zoom: zoom,
        zoomControl: !isStatic,
        dragging: !isStatic,
        touchZoom: !isStatic,
        doubleClickZoom: !isStatic,
        scrollWheelZoom: !isStatic,
        boxZoom: !isStatic,
        keyboard: !isStatic
    };
    
    const map = L.map(mapElementId, mapOptions);

    // Add CyclOSM tile layer for single listing map
    let tileLayer;
    if (mapElementId === 'listing-map') {
        tileLayer = L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
            maxZoom: 20,
            attribution: '<a href="https://github.com/cyclosm/cyclosm-cartocss-style/releases" title="CyclOSM - Open Bicycle render">CyclOSM</a> | Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });
    } else {
        tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });
    }
    tileLayer.addTo(map);
    console.log('Raster tile map initialized successfully');
    
    // Add marker if coordinates are provided
    if (mapData.coordinates || (!isNaN(parseFloat(mapElement.dataset.lat)) && !isNaN(parseFloat(mapElement.dataset.lng)))) {
        console.log('Adding marker at coordinates:', center);
        
        // Create icon from configuration
        let markerIcon = archaeologicalSiteIcon; // Default fallback
        if (mapData.iconConfig) {
            markerIcon = createCustomIcon(mapData.iconConfig);
        }
        
        const marker = L.marker(center, { icon: markerIcon }).addTo(map);
        
        // Add popup only if enabled and content is available
        if (showPopup && mapData.popupText) {
            marker.bindPopup(mapData.popupText);
        }
    }

    initializedMaps.add(mapElementId);
    console.log(`Geotour raster map initialized on element: ${mapElementId}`);
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
        
        // Try to use geotourListingMapData first (from PHP template)
        if (typeof window.geotourListingMapData !== 'undefined' && window.geotourListingMapData.single) {
            console.log('Using geotourListingMapData for single map:', window.geotourListingMapData.single);
            initializeGeotourMap('listing-map', window.geotourListingMapData.single);
        } else {
            console.log('Using data attributes for single map');
            // Fallback to data attributes
            initializeMapFromDataAttributes('listing-map');
        }
    }    // Initialize archive map
    const archiveMapElement = document.getElementById('archive-map');
    if (archiveMapElement) {
        console.log('Found archive map element');
        
        if (typeof window.geotourListingMapData !== 'undefined' && window.geotourListingMapData.archive) {
            console.log('Using geotourListingMapData for archive map');
            initializeGeotourMap('archive-map', window.geotourListingMapData.archive);
        } else {
            console.log('Initializing default archive map');
            // Initialize with default view for now
            initializeGeotourMap('archive-map', {});
        }
    }
    
    console.log('Map initialization complete');
}
