// e:\\visualstudio\\geotour-theme\\src\\js\\modules\\maps\\vector-styles.js
/**
 * Vector tile styling for Leaflet.VectorGrid.Protobuf using MapTiler tiles.
 * Adapted from MapTiler Terrain GL Style
 */

// Color palette (approximated or direct from Terrain style)
// Note: Leaflet path options accept HSL/HSLA/RGBA strings directly.
const terrainColors = {
    background: 'hsl(47, 26%, 88%)', // #f0e6c0
    land: 'hsl(47, 26%, 88%)', // General land color, similar to background
    residential: 'hsl(47, 13%, 86%)', // #e6e2d8
    grass: 'hsl(82, 46%, 72%)', // #b7e283
    wood: 'hsl(82, 46%, 72%)', // #b7e283
    parkFill: 'rgba(192, 216, 151, 0.53)', // #c0d897 with opacity
    parkOutline: 'rgba(159, 183, 118, 0.69)',
    iceShelf: 'hsl(47, 26%, 88%)',
    glacier: 'hsl(47, 22%, 94%)', // #faf8f2
    sand: 'rgba(232, 214, 38, 1)', // #e8d626
    agriculture: '#eae0d0',
    nationalPark: '#E1EBB0',
    water: 'hsl(205, 56%, 73%)', // #a3c7e6
    buildingFill: 'hsl(39, 41%, 86%)', // #e6d5b9
    buildingOutline: 'hsl(36, 45%, 80%)', // #e0d1b0
    roadPath: 'hsl(0, 0%, 97%)', // #f7f7f7
    roadMinor: 'hsl(0, 0%, 97%)',
    roadSecondaryTertiary: '#fff',
    roadTrunkPrimary: '#fff',
    roadMotorway: 'hsl(0, 0%, 100%)',
    tunnelMinor: '#efefef',
    tunnelMajor: '#fff',
    bridgeMinorCase: '#dedede',
    bridgeMajorCase: '#dedede',
    railway: 'hsl(34, 12%, 66%)',
    boundaries: 'hsla(0, 8%, 22%, 0.51)', // #413b3b with opacity
    boundariesSub: 'hsl(0, 0%, 76%)' // #c2c2c2
};

// --- GLOBAL LAYER LOGGER ---
// This will log every layer name and properties as features are styled
function logLayer(layerName, properties) {
    if (!window._loggedLayers) window._loggedLayers = new Set();
    if (!window._loggedLayers.has(layerName)) {
        console.log('[VectorGrid] Layer:', layerName, 'Sample properties:', properties);
        window._loggedLayers.add(layerName);
    }
}

export const vectorTileLayerStyles = {
    // Background color should be set on the map container via CSS
    // background: () => ({ fill: true, fillColor: terrainColors.background, stroke: false }),

    landcover: (properties, zoom) => {
        logLayer('landcover', properties);
        const pClass = properties.class;
        const pSubclass = properties.subclass;

        if (pClass === 'grass') {
            return { fill: true, fillColor: terrainColors.grass, fillOpacity: 0.45, stroke: false };
        }
        if (pClass === 'wood') {
            let opacity = 0.6;
            if (zoom > 8) opacity = 1; // Simplified from stops: [[8, 0.6], [22, 1]]
            return { fill: true, fillColor: terrainColors.wood, fillOpacity: opacity, stroke: false };
        }
        if (pSubclass === 'ice_shelf') {
            return { fill: true, fillColor: terrainColors.iceShelf, fillOpacity: 0.8, stroke: false };
        }
        if (pSubclass === 'glacier') {
            let opacity = 1;
            if (zoom > 8) opacity = 0.5; // Simplified from stops: [[0, 1], [8, 0.5]]
            return { fill: true, fillColor: terrainColors.glacier, fillOpacity: opacity, stroke: false };
        }
        if (pClass === 'sand') {
            return { fill: true, fillColor: terrainColors.sand, fillOpacity: 0.3, stroke: false };
        }
        if (pClass === 'national_park') { // from landuse_overlay_national_park
            let opacity = 0;
            if (zoom > 5) opacity = 0.75; // Simplified from stops: [[5, 0], [9, 0.75]]
            if (zoom < 9) return { fill: true, fillColor: terrainColors.nationalPark, fillOpacity: opacity, stroke: false };
            return { fill: true, fillColor: terrainColors.nationalPark, fillOpacity: 0.75, stroke: false };
        }
        // Default land if no other landcover matches
        return { fill: true, fillColor: terrainColors.land, stroke: false, fillOpacity: 1 };
    },

    landuse: (properties, zoom) => {
        logLayer('landuse', properties);
        const pClass = properties.class;
        if (pClass === 'residential') {
            return { fill: true, fillColor: terrainColors.residential, fillOpacity: 0.7, stroke: false };
        }
        if (pClass === 'agriculture') {
            return { fill: true, fillColor: terrainColors.agriculture, stroke: false };
        }
        // Other landuse classes from OpenMapTiles (e.g., industrial, school, hospital) could be added here
        return { stroke: false, fill: false }; // Hide other landuses
    },

    park: (properties, zoom) => {
        logLayer('park', properties);
        // This combines 'park' fill and 'park_outline' from the Terrain style
        if (properties.$type === 'Polygon' || (properties.type && properties.type.toLowerCase() === 'polygon')) {
            return { 
                fill: true, fillColor: terrainColors.parkFill, fillOpacity: 1, // Opacity is in RGBA for parkFill
                stroke: true, color: terrainColors.parkOutline, weight: 1, dashArray: '0.5, 1' 
            };
        }
         if (properties.$type === 'LineString' || (properties.type && properties.type.toLowerCase() === 'linestring')) {
            return { 
                stroke: true, color: terrainColors.parkOutline, weight: 1, dashArray: '0.5, 1' 
            };
        }
        return { stroke: false, fill: false };
    },

    water: (properties, zoom) => {
        logLayer('water', properties);
        if (properties.brunnel === 'tunnel') return { stroke: false, fill: false }; // Hide tunnelled water for fill
        return { fill: true, fillColor: terrainColors.water, stroke: false };
    },

    waterway: (properties, zoom) => {
        logLayer('waterway', properties);
        const brunnel = properties.brunnel;
        let style = {
            stroke: true,
            color: terrainColors.water,
            lineCap: 'round',
            lineJoin: 'round',
            fill: false
        };

        if (brunnel === 'tunnel') {
            style.dashArray = '3, 3';
            style.weight = zoom >= 8 ? (zoom >= 20 ? 2 : 1) : 0; // Simplified from stops
            style.gap = zoom >= 12 ? (zoom >= 20 ? 6 : 0) : 0; // approx line-gap-width
        } else if (brunnel === 'bridge') {
            // Simplified: no separate casing, just style the bridge waterway
            style.weight = zoom >= 8 ? (zoom >= 20 ? 8 : 1) : 0;
        } else { // Not tunnel or bridge
            style.weight = zoom >= 8 ? (zoom >= 20 ? 8 : 1) : 0;
        }
        if (style.weight === 0 && brunnel !== 'tunnel') return {stroke:false, fill:false}; // hide if no weight unless it's a tunnel to show dash

        return style;
    },

    building: (properties, zoom) => {
        logLayer('building', properties);
        if (zoom <= 13) return { stroke: false, fill: false };
        
        let opacity = 0.6;
        if (zoom >= 14) opacity = 1; // Simplified from stops: [[13, 0.6], [14, 1]]
        
        return {
            fill: true,
            fillColor: terrainColors.buildingFill,
            fillOpacity: opacity,
            stroke: true,
            color: terrainColors.buildingOutline,
            weight: 0.5 // Inferred, as fill-outline-color implies a stroke
        };
    },

    transportation: (properties, zoom) => {
        logLayer('transportation', properties);
        const pClass = properties.class;
        const brunnel = properties.brunnel;

        let style = {
            stroke: true,
            lineCap: 'round',
            lineJoin: 'round',
            fill: false,
            opacity: 1
        };
        
        // Pier
        if (pClass === 'pier') {
            if (properties.$type === 'Polygon' || (properties.type && properties.type.toLowerCase() === 'polygon')) {
                 return { fill: true, fillColor: terrainColors.background, stroke:false };
            }
            style.color = terrainColors.background; // same as background for pier lines
            style.weight = zoom >= 15 ? (zoom >= 17 ? 4 : 1) : 0.5;
            return style;
        }

        // Bridge area fill
        if (brunnel === 'bridge' && (properties.$type === 'Polygon' || (properties.type && properties.type.toLowerCase() === 'polygon'))) {
            return { fill: true, fillColor: terrainColors.background, fillOpacity: 0.5, stroke: false };
        }


        // Tunnel styling
        if (brunnel === 'tunnel') {
            style.lineCap = 'butt';
            style.lineJoin = 'miter';
            if (pClass === 'minor_road' || pClass === 'minor' || pClass === 'service') { // tunnel_minor
                style.color = terrainColors.tunnelMinor;
                style.dashArray = '0.36, 0.18';
                style.weight = zoom >= 4 ? (zoom >= 20 ? 30 : 0.25) : 0; // approx
            } else if (['primary', 'secondary', 'tertiary', 'trunk'].includes(pClass)) { // tunnel_major
                style.color = terrainColors.tunnelMajor;
                style.dashArray = '0.28, 0.14';
                style.weight = zoom >= 6 ? (zoom >= 20 ? 30 : 0.5) : 0; // approx
            } else if (pClass === 'transit') { // tunnel_railway_transit
                 style.color = terrainColors.railway;
                 style.dashArray = '3,3';
                 style.opacity = zoom >= 11 ? (zoom >= 16 ? 1 : 0) : 0;
                 style.weight = 1; // Default weight for transit tunnel
            } else {
                return { stroke: false, fill: false }; // Hide other tunnel types
            }
            return style.weight > 0 && style.opacity > 0 ? style : { stroke: false, fill: false };
        }

        // Bridge styling (lines) - simplified, no casing
        if (brunnel === 'bridge') {
            if (pClass === 'minor_road' || pClass === 'minor' || pClass === 'service') {
                style.color = terrainColors.tunnelMinor; // Using tunnelMinor as #efefef
                style.weight = zoom >= 4 ? (zoom >= 20 ? 30 : 0.25) : 0;
            } else if (['primary', 'secondary', 'tertiary', 'trunk'].includes(pClass)) {
                style.color = terrainColors.tunnelMajor; // Using tunnelMajor as #fff
                style.weight = zoom >= 6 ? (zoom >= 20 ? 30 : 0.5) : 0;
            } else {
                 return { stroke: false, fill: false }; // Hide other bridge road types
            }
            return style.weight > 0 ? style : { stroke: false, fill: false };
        }
        
        // Regular roads
        switch (pClass) {
            case 'path':
            case 'track':
                style.color = terrainColors.roadPath;
                style.dashArray = '1, 1';
                style.lineCap = 'square';
                style.lineJoin = 'bevel';
                style.weight = zoom >= 4 ? (zoom >= 20 ? 10 : 0.25) : 0; // approx
                break;
            case 'minor':
            case 'service':
                if (zoom < 13) return { stroke: false, fill: false };
                style.color = terrainColors.roadMinor;
                style.weight = zoom >= 4 ? (zoom >= 20 ? 30 : 0.25) : 0; // approx
                break;
            case 'secondary':
            case 'tertiary':
                style.color = terrainColors.roadSecondaryTertiary;
                style.weight = zoom >= 6 ? (zoom >= 20 ? 20 : 0.5) : 0; // approx
                break;
            case 'primary':
            case 'trunk':
                style.color = terrainColors.roadTrunkPrimary;
                style.weight = zoom >= 6 ? (zoom >= 20 ? 30 : 0.5) : 0; // approx
                break;
            case 'motorway':
                style.color = terrainColors.roadMotorway;
                style.weight = zoom >= 8 ? (zoom >= 16 ? 10 : 1) : 0; // approx
                break;
            case 'rail':
            case 'transit': // railway & railway_transit (non-tunnel)
                style.color = terrainColors.railway;
                style.opacity = zoom >= 11 ? (zoom >= 16 ? 1 : 0) : 0;
                style.weight = 1.2; // Default weight
                break;
            default:
                return { stroke: false, fill: false }; // Hide other road classes
        }
        return style.weight > 0 && style.opacity > 0 ? style : { stroke: false, fill: false };
    },
    // Aliases for transportation
    road: (p, z) => vectorTileLayerStyles.transportation(p, z),
    roads: (p, z) => vectorTileLayerStyles.transportation(p, z),

    boundary: (properties, zoom) => {
        logLayer('boundary', properties);
        const adminLevel = properties.admin_level;
        let style = {
            stroke: true,
            fill: false,
            opacity: 1 // Handled by HSL alpha or direct opacity
        };

        if (adminLevel <= 2) { // admin_country_z0-4 and admin_country_z5-
            if (properties.disputed === 1 && zoom > 3 && zoom < 8) { // Special disputed style from some themes, not in Terrain directly
                 // style.dashArray = '4,2';
            }
            style.color = terrainColors.boundaries; // hsla(0, 8%, 22%, 0.51)
            style.weight = zoom >= 3 ? (zoom >= 22 ? 15 : 0.5 ) : 0; // approx
        } else if ([4, 6, 8].includes(adminLevel)) { // admin_sub
            style.color = terrainColors.boundariesSub; // hsl(0, 0%, 76%)
            style.dashArray = '2, 1';
            style.weight = 0.8;
        } else {
            return { stroke: false, fill: false };
        }
        return style.weight > 0 ? style : { stroke: false, fill: false };
    },

    // Hide all label/point layers by default
    place: () => null,
    poi: () => null,
    housenumber: () => null,
    mountain_peak: () => null,
    place_label: () => null,
    poi_label: () => null,
    peaks: () => null,
    admin: () => null, // Covered by boundary
    point: () => null,
    points: () => null,
    transportation_name: () => null,
    water_name: () => null,
    aerodrome_label: () => null,
    // Contour labels would go here if contours source was used
    // contour_label: () => null, 

    // Default for any other layers from the primary source
    default: (properties, zoom, layerName) => {
        logLayer(layerName, properties);
        console.log(`%c[VectorGrid] DEFAULT HITTING (Primary Source): ${layerName}`, 'color: orange; font-weight: bold;', 'Sample properties:', properties);
        return { stroke: false, fill: false }; // Hide everything else
    },
};

/*
Example of how to use this with Leaflet.VectorGrid.Protobuf:

import L from 'leaflet';
import 'leaflet.vectorgrid'; // Assuming Leaflet.VectorGrid is installed or included
import { vectorTileLayerStyles } from './vector-styles'; // This file

// ... (Leaflet map initialization) ...
// const map = L.map('map-id').setView([lat, lng], zoom);

// MapTiler API Key and URL
const MAPTILER_API_KEY = 'VrfcfMogFgrPX2raBcBO'; // Replace with your actual key if different
const MAPTILER_URL = \`https://api.maptiler.com/tiles/v3/{z}/{x}/{y}.pbf?key=\${MAPTILER_API_KEY}\`;

const vectorTileOptions = {
    vectorTileLayerStyles: vectorTileLayerStyles,
    interactive: true, // Set to true if you want to interact with features
    getFeatureId: function(f) {
        return f.properties.id; // Ensure your features have a unique ID if using this
    },
    maxNativeZoom: 14, // Typically for MapTiler v3 PBFs
    // minZoom: ...
    // maxZoom: ...
    attribution: '<a href="https://www.maptiler.com/copyright/" target="_blank">&copy; MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>',
};

const vectorGridLayer = L.vectorGrid.protobuf(MAPTILER_URL, vectorTileOptions);
// vectorGridLayer.addTo(map);

// Set map background color via CSS on the map container:
// .your-map-container { background-color: #0d1a26; }
*/
