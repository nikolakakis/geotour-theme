// ==========================================================================
// THEMATIC LAYER MANAGER
// ==========================================================================
// Handles non-marker layers like trails, boundaries, administrative areas, etc.
// Keeps the BigMapMarkers class focused on point data only.

export class ThematicLayerManager {
    constructor() {
        console.log('ThematicLayerManager constructed.');
        this.map = null; // Initialize map as null
        this.e4TrailLayers = null; // Will hold our two-layer E4 trail group
        this.allLayers = new Map(); // Track all thematic layers
        this.layerGroups = new Map(); // For managing multiple thematic layers
        
        // Don't initialize layers yet - wait for init() to be called with the map
    }

    /**
     * Initializes the manager with the Leaflet map object.
     * This method should be called AFTER the map is created.
     * @param {L.Map} map - The initialized Leaflet map instance.
     */
    init(map) {
        if (!map) {
            console.error('ThematicLayerManager Error: A valid map object was not provided to init().');
            return;
        }
        console.log('ThematicLayerManager initializing with map object...');
        this.map = map;
        
        // Now initialize layers
        this.initializeE4Trail();
    }

    /**
     * Initialize E4 trail layer from PostGIS vector tiles.
     * Implements the two-color dashed effect: solid base + dashed top layer.
     */
    initializeE4Trail() {
        // Check if Leaflet.VectorGrid is available, with retry logic
        if (typeof L.vectorGrid === 'undefined') {
            console.warn('Leaflet.VectorGrid plugin not found. E4 trail layer will not be available.');
            console.log('Available L properties:', Object.keys(L));
            console.log('Checking if script was loaded...');
            
            // Try to wait and retry in case VectorGrid is still loading
            setTimeout(() => {
                if (typeof L.vectorGrid !== 'undefined') {
                    console.log('VectorGrid found on retry, initializing E4 trail...');
                    this.createE4TrailLayers();
                } else {
                    console.error('VectorGrid still not available after retry');
                }
            }, 1000);
            return;
        }

        console.log('Initializing E4 trail layer with VectorGrid...');
        this.createE4TrailLayers();
        this.setupZoomListener();
    }

    /**
     * Creates the styled E4 trail layers using vector tiles from PostGIS.
     * Implements the two-color dashed effect: solid base + dashed top layer.
     */
    createE4TrailLayers() {
        // Check if Leaflet.VectorGrid is available
        if (typeof L.vectorGrid === 'undefined') {
            console.warn('Leaflet.VectorGrid plugin not found. E4 trail layer will not be available.');
            console.log('Available L properties:', Object.keys(L));
            console.log('Checking if script was loaded...');
            return;
        }

        console.log('Initializing E4 trail layer with VectorGrid...');

        // E4 Trail configuration from your PostGIS service
        const e4Config = {
            tileUrl: 'https://tiles.geotour.gr/public.cretan_e4/{z}/{x}/{y}.pbf',
            layerName: 'cretan_e4', // PostGIS table name
            minZoom: 14, // Only show at zoom 14+
            maxZoom: 22
        };
        
        console.log('Using layer name:', e4Config.layerName);
        console.log('Tile URL:', e4Config.tileUrl);

        // --- Style Definition for the two-layer dashed effect ---
        
        // Base layer: Solid light earth/stone color (represents the physical path)
        const baseTrailStyle = {
            color: '#bcaea0', // Light earth/stone color
            weight: 3,
            opacity: 1.0,
            lineCap: 'round',
            lineJoin: 'round'
        };

        // Top layer: Dashed strong red (represents trail markers)
        const markerTrailStyle = {
            color: '#D83C3C', // Strong trail red
            weight: 3,
            opacity: 1.0,
            dashArray: '10, 15', // 10px dash, 15px gap
            lineCap: 'round',
            lineJoin: 'round'
        };

        // --- Create Vector Tile Layers ---
        
        // Base solid layer (rendered first)
        const baseTrailLayer = L.vectorGrid.protobuf(e4Config.tileUrl, {
            rendererFactory: L.svg.tile,
            maxZoom: e4Config.maxZoom,
            vectorTileLayerStyles: {
                // Use function-based styling for better control and property access
                [e4Config.layerName]: (properties, zoom) => {
                    return {
                        color: '#bcaea0', // Light earth/stone color
                        weight: 3,
                        opacity: 1.0,
                        lineCap: 'round',
                        lineJoin: 'round'
                    };
                }
            },
            interactive: true // Allow mouse events for popup functionality
        });

        // Add debugging for vector tile loading
        baseTrailLayer.on('loading', function(e) {
            console.log('E4 base layer: Vector tiles loading...');
        });

        baseTrailLayer.on('load', function(e) {
            console.log('E4 base layer: Vector tiles loaded successfully');
        });

        // Log available layers when tiles are added
        baseTrailLayer.on('tileload', function(e) {
            if (e.tile && e.tile._layers) {
                console.log('Available layers in vector tile:', Object.keys(e.tile._layers));
            }
        });

        // Top dashed layer (rendered on top)
        const markerTrailLayer = L.vectorGrid.protobuf(e4Config.tileUrl, {
            rendererFactory: L.svg.tile,
            maxZoom: e4Config.maxZoom,
            vectorTileLayerStyles: {
                // Use function-based styling for better control and property access
                [e4Config.layerName]: (properties, zoom) => {
                    return {
                        color: '#D83C3C', // Strong trail red
                        weight: 3,
                        opacity: 1.0,
                        dashArray: '10, 15', // 10px dash, 15px gap
                        lineCap: 'round',
                        lineJoin: 'round'
                    };
                }
            },
            interactive: true
        });

        // Group the layers for unified management
        this.e4TrailLayers = L.layerGroup([baseTrailLayer, markerTrailLayer]);
        
        // Add click interactivity for popups
        this.e4TrailLayers.on('click', (e) => {
            // Stop the click from propagating to the map, which might close other popups
            L.DomEvent.stop(e);

            const properties = e.layer.properties;
            let popupContent = '<h4>E4 European Path</h4>';

            // Build the popup content dynamically from the feature's properties
            // Adjust these property names to match your actual PostGIS data structure
            if (properties) {
                if (properties.name) {
                    popupContent += `<p><strong>Section:</strong> ${properties.name}</p>`;
                }
                if (properties.section_id) {
                    popupContent += `<p><strong>Section ID:</strong> ${properties.section_id}</p>`;
                }
                if (properties.difficulty) {
                    popupContent += `<p><strong>Difficulty:</strong> ${properties.difficulty}</p>`;
                }
                if (properties.length_km) {
                    popupContent += `<p><strong>Length:</strong> ${properties.length_km} km</p>`;
                }
                if (properties.description) {
                    popupContent += `<p><strong>Description:</strong> ${properties.description}</p>`;
                }
                
                // If no specific properties are found, show available data
                if (!properties.name && !properties.section_id && !properties.difficulty) {
                    popupContent += '<p><strong>Trail Section</strong></p>';
                    popupContent += '<p>Part of the E4 European Long Distance Path crossing Crete</p>';
                    
                    // Debug: show available properties in development
                    if (Object.keys(properties).length > 0) {
                        popupContent += '<hr><small><strong>Available data:</strong><br>';
                        Object.keys(properties).slice(0, 5).forEach(key => {
                            popupContent += `${key}: ${properties[key]}<br>`;
                        });
                        popupContent += '</small>';
                    }
                }
            } else {
                popupContent += '<p>Trail information is not available for this section.</p>';
            }

            // Create and open the popup at the location of the click
            L.popup()
                .setLatLng(e.latlng)
                .setContent(popupContent)
                .openOn(this.map);
        });
        
        // Store in our layer management system
        this.layerGroups.set('e4_trail', {
            layers: this.e4TrailLayers,
            minZoom: e4Config.minZoom,
            visible: false
        });

        // Add hover effects and potential click handlers
        this.setupE4TrailInteractions(baseTrailLayer, markerTrailLayer);
    }

    /**
     * Sets up interactive features for the E4 trail
     */
    setupE4TrailInteractions(baseLayer, topLayer) {
        // Enhanced hover effects for better user experience
        const originalTopLayerStyle = {
            color: '#D83C3C',
            weight: 3,
            opacity: 1.0
        };

        const highlightStyle = {
            color: '#FF4444',
            weight: 5,
            opacity: 0.9
        };

        // Add hover effect to the top (dashed) layer
        topLayer.on('mouseover', (e) => {
            // Temporarily increase weight and change color for hover feedback
            e.layer.setStyle && e.layer.setStyle(highlightStyle);
        });

        topLayer.on('mouseout', (e) => {
            // Reset to original style
            e.layer.setStyle && e.layer.setStyle(originalTopLayerStyle);
        });

        // Add hover effect to base layer as well for consistent interaction
        baseLayer.on('mouseover', (e) => {
            // Change cursor to pointer to indicate clickability
            this.map.getContainer().style.cursor = 'pointer';
        });

        baseLayer.on('mouseout', (e) => {
            // Reset cursor
            this.map.getContainer().style.cursor = '';
        });

        console.log('E4 trail interactions set up successfully');
    }

    /**
     * Sets up zoom-based visibility control for all thematic layers
     */
    setupZoomListener() {
        this.map.on('zoomend', () => {
            this.updateLayerVisibility();
        });

        // Trigger initial visibility check
        this.updateLayerVisibility();
    }

    /**
     * Updates visibility of all thematic layers based on current zoom level
     */
    updateLayerVisibility() {
        const currentZoom = this.map.getZoom();

        this.layerGroups.forEach((layerConfig, layerName) => {
            const { layers, minZoom, maxZoom = 22 } = layerConfig;
            const shouldBeVisible = currentZoom >= minZoom && currentZoom <= maxZoom;

            if (shouldBeVisible && !layerConfig.visible) {
                // Add layer to map
                this.map.addLayer(layers);
                layerConfig.visible = true;
                console.log(`Thematic layer '${layerName}' added at zoom ${currentZoom}`);
            } else if (!shouldBeVisible && layerConfig.visible) {
                // Remove layer from map
                this.map.removeLayer(layers);
                layerConfig.visible = false;
                console.log(`Thematic layer '${layerName}' removed at zoom ${currentZoom}`);
            }
        });
    }

    /**
     * Manually show/hide a specific thematic layer
     * @param {string} layerName - Name of the layer to toggle
     * @param {boolean} visible - Whether to show or hide the layer
     */
    toggleLayer(layerName, visible) {
        const layerConfig = this.layerGroups.get(layerName);
        if (!layerConfig) {
            console.warn(`Thematic layer '${layerName}' not found`);
            return;
        }

        if (visible && !layerConfig.visible) {
            this.map.addLayer(layerConfig.layers);
            layerConfig.visible = true;
        } else if (!visible && layerConfig.visible) {
            this.map.removeLayer(layerConfig.layers);
            layerConfig.visible = false;
        }
    }

    /**
     * Get the bounds of a specific thematic layer (useful for fitting map view)
     * @param {string} layerName - Name of the layer
     * @returns {L.LatLngBounds|null} Layer bounds or null if not available
     */
    getLayerBounds(layerName) {
        // For the E4 trail, we can use the bounds from your API response
        if (layerName === 'e4_trail') {
            // Bounds from your JSON: [23.53631654032919,35.070327539314654,26.25841268304673,35.5155332624568]
            return L.latLngBounds(
                [35.070327539314654, 23.53631654032919], // Southwest
                [35.5155332624568, 26.25841268304673]    // Northeast
            );
        }
        
        return null;
    }

    /**
     * Fit map view to show the E4 trail completely
     */
    fitToE4Trail() {
        const bounds = this.getLayerBounds('e4_trail');
        if (bounds) {
            this.map.fitBounds(bounds, { padding: [20, 20] });
        }
    }
    
    /**
     * Toggle E4 trail visibility regardless of zoom level
     * Useful for future UI controls
     * @param {boolean} forceVisible - Force visibility regardless of zoom
     */
    toggleE4Trail(forceVisible = null) {
        if (forceVisible !== null) {
            this.toggleLayer('e4_trail', forceVisible);
        } else {
            const layerConfig = this.layerGroups.get('e4_trail');
            if (layerConfig) {
                this.toggleLayer('e4_trail', !layerConfig.visible);
            }
        }
    }
    
    /**
     * Check if E4 trail is currently visible
     * @returns {boolean}
     */
    isE4TrailVisible() {
        const layerConfig = this.layerGroups.get('e4_trail');
        return layerConfig ? layerConfig.visible : false;
    }

    /**
     * Add future thematic layers here
     * Examples: Administrative boundaries, other hiking trails, protected areas, etc.
     */
    
    // Future method: initializeAdministrativeBoundaries()
    // Future method: initializeProtectedAreas()
    // Future method: initializeOtherTrails()

    /**
     * Cleanup method - remove all thematic layers
     */
    destroy() {
        this.layerGroups.forEach((layerConfig) => {
            if (layerConfig.visible) {
                this.map.removeLayer(layerConfig.layers);
            }
        });
        this.layerGroups.clear();
        this.map.off('zoomend');
    }
}