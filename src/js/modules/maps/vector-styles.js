// e:\\visualstudio\\geotour-theme\\src\\js\\modules\\maps\\vector-styles.js
/**
 * Vector tile styling for Leaflet.VectorGrid.Protobuf using MapTiler tiles.
 * Dark theme, focusing on readability.
 */

export const vectorTileLayerStyles = {
    // Water features
    water: {
        fill: true,
        fillColor: '#0a141e',
        stroke: false
    },

    // Land features
    landcover: {
        fill: true,
        fillColor: '#172737',
        stroke: false
    },
    landuse: {
        fill: true,
        fillColor: '#172737',
        stroke: false
    },

    // Parks and green areas
    park: {
        fill: true,
        fillColor: '#142230',
        stroke: false
    },

    // Administrative boundaries - hidden
    boundary: () => null,

    // Transportation (roads)
    // Assumes a 'transportation' layer in MapTiler tiles with a 'class' property.
    transportation: (properties, zoom) => {
        if (zoom < 10) {
            return null; // Roads not visible below zoom 10
        }

        const roadClass = properties.class;
        let style = {
            stroke: true,
            fill: false,
            opacity: 1,
            lineCap: 'round',
            lineJoin: 'round'
        };

        // Detailed styling at zoom >= 12
        if (zoom >= 12) {
            switch (roadClass) {
                case 'motorway':
                case 'primary':
                    style.weight = 1.5;
                    style.color = '#5a6b82';
                    break;
                case 'secondary':
                case 'tertiary':
                    style.weight = 1;
                    style.color = '#4a5b70';
                    break;
                case 'street': // Covers minor roads/streets
                    style.weight = 0.5;
                    style.color = '#3b4b5f';
                    break;
                default: // Other road classes (path, service, track, etc.)
                    return null; // Hide other road types to keep map clean
            }
        } else { // Simpler styling for zoom levels 10 and 11
            switch (roadClass) {
                case 'motorway':
                case 'primary':
                    style.weight = 0.75;
                    style.color = '#4a5b70'; // Darker/less prominent than z>=12
                    break;
                case 'secondary':
                case 'tertiary':
                    style.weight = 0.5;
                    style.color = '#3b4b5f';
                    break;
                case 'street': // Hide minor streets at zoom 10-11 for clarity
                default:
                    return null;
            }
        }
        return style;
    },

    // Place labels (cities, towns, villages)
    // Assumes a 'place' layer in MapTiler tiles with a 'class' property.
    place: (properties, zoom) => {
        const placeClass = properties.class;
        let style = {
            fill: true,
            fillColor: '#cdd7e4', // Light off-white for text
            fillOpacity: 1,
            stroke: true,         // Enable stroke for halo effect
            color: '#172737',    // Halo color (matching land background)
            weight: 3,            // Halo weight
            // fontSize is conceptual here; actual rendering depends on Leaflet.VectorGrid's capabilities
            // or custom handling (e.g., if it can return L.DivIcon).
        };

        let visible = false;
        let fontSize = '10px'; // Default, will be overridden

        switch (placeClass) {
            case 'city':
                if (zoom >= 8) {
                    fontSize = '16px';
                    visible = true;
                }
                break;
            case 'town':
                if (zoom >= 11) {
                    fontSize = '13px';
                    visible = true;
                }
                break;
            case 'village':
            case 'hamlet':
                if (zoom >= 13) {
                    fontSize = '11px';
                    visible = true;
                }
                break;
            default:
                // Hide all other place classes (e.g., country, state, suburb)
                return null;
        }

        if (visible) {
            style.fontSize = fontSize; // Attach conceptual fontSize
            return style;
        }
        return null;
    },

    // Points of Interest (POIs) - hidden from basemap
    poi: () => null,

    // Buildings
    building: (properties, zoom) => {
        if (zoom > 14) {
            return {
                fill: true,
                fillColor: '#1f2d3d',
                stroke: false,
                fillOpacity: 0.7 // Slight transparency for buildings
            };
        }
        return null; // Not visible at zoom <= 14
    }
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
    // attribution: '...' // Remember to add MapTiler attribution
};

const vectorGridLayer = L.vectorGrid.protobuf(MAPTILER_URL, vectorTileOptions);
// vectorGridLayer.addTo(map);

// Set map background color via CSS on the map container:
// .your-map-container { background-color: #0d1a26; }
*/
