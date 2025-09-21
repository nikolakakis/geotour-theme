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
                [e4Config.layerName]: baseTrailStyle
            },
            interactive: true // Allow mouse events for future popup functionality
        });

        // Top dashed layer (rendered on top)
        const markerTrailLayer = L.vectorGrid.protobuf(e4Config.tileUrl, {
            rendererFactory: L.svg.tile,
            maxZoom: e4Config.maxZoom,
            vectorTileLayerStyles: {
                [e4Config.layerName]: markerTrailStyle
            },
            interactive: true
        });

        // Group the layers for unified management
        this.e4TrailLayers = L.layerGroup([baseTrailLayer, markerTrailLayer]);
        
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
        // Add hover effect to highlight the trail
        const highlightStyle = {
            color: '#FF4444',
            weight: 5,
            opacity: 0.8
        };

        // You can add click handlers here for future features like:
        // - Showing trail information popups
        // - Adding trail segments to route planning
        // - Displaying elevation profiles
        
        topLayer.on('mouseover', (e) => {
            // Potential future enhancement: highlight trail segment on hover
        });

        topLayer.on('mouseout', (e) => {
            // Reset highlight
        });
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