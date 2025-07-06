# Map Implementation Guide - Geotour Theme

## Overview
This guide explains how to implement maps in the Geotour WordPress theme. The theme uses **Leaflet.js** with **raster tile layers** for all mapping functionality. All necessary libraries are loaded via the **geotour-crete### File Structure Impact

### Big Map Files
- `page-listing.php` - Main template (Template Name: Listing Map)
- `src/js/modules/big-map/main.js` - BigMapUI class and functionality
- `src/js/modules/big-map/data-handler.js` - API calls and data transformation
- `src/js/modules/big-map/sidebar.js` - Sidebar functionality and search
- `src/js/modules/big-map/markers.js` - Marker management
- `src/js/modules/big-map/loading.js` - Loading states
- `src/scss/components/bigmap/_index.scss` - Full-screen map styling
- `includes/api/spatial-info-v3.php` - REST API endpoint for spatial data

### Modified Files
- `src/js/modules/maps/main.js` - Simplified to raster tiles, regular map handling
- `src/js/main.js` - Imports BigMapUI but auto-initializes
- `package.json` - Removed vector dependencies

### Removed Files
- `src/js/modules/maps/vector-styles.js` - No longer needed

### Maintained Files
- `src/scss/components/_maps.scss` - Regular map styling (unchanged)
- `template-parts/listing/map-*.php` - Template parts (unchanged)* and are NOT included in this theme.

## Core Libraries (Plugin-Managed)
- **Leaflet.js** (v1.9.4) - Main mapping library, provided by geotour-crete-maps plugin
- **Leaflet.markercluster** (v1.5.3) - For marker clustering
- **Leaflet CSS** - Styling for map controls and UI, provided by geotour-crete-maps plugin

⚠️ **Important**: All Leaflet dependencies are managed by the geotour-crete-maps plugin. The theme does NOT include these libraries.

## Map Implementation Types

### 1. Big Map (Full-Screen Map with Sidebar)
**File:** `page-listing.php` (Template Name: Listing Map)  
**Element ID:** `big-map`  
**JavaScript Module:** `src/js/modules/big-map/main.js`  
**SCSS Styles:** `src/scss/components/_big-map.scss`  
**Usage:** Full-screen interactive map with sidebar for spatial navigation and listing display  
**Features:**
- AJAX-powered listing loading based on map bounds
- Mobile-responsive sidebar toggle
- Real-time filtering by category, region, tag, and search
- Always-visible search input with enter key support
- Custom map markers with popups
- Spatial filtering via REST API
- Mobile-optimized interaction

### 2. Single Listing Maps
**File:** `template-parts/listing/map-single.php`
**Element ID:** `listing-map`
**Usage:** Display a single listing location

### 3. Archive Listing Maps
**File:** `template-parts/listing/map-archive.php`
**Element ID:** `archive-map`
**Usage:** Display multiple listings on one map

### 4. Custom Page Maps
**Element ID:** Any unique ID (e.g., `custom-map`)
**Usage:** Custom implementations

## Big Map Implementation Details

### Architecture
The Big Map is a sophisticated full-screen mapping interface that combines:
- **Frontend:** Leaflet.js with modular JavaScript components
- **Backend:** WordPress REST API (`/wp-json/geotour/v3/spatial-info`)
- **Data Flow:** AJAX-powered real-time filtering and loading

### Key Files
```
page-listing.php                           # Main template
src/js/modules/big-map/main.js            # BigMapUI orchestrator
src/js/modules/big-map/sidebar.js         # Sidebar and search functionality
src/js/modules/big-map/data-handler.js    # API integration and data transformation
src/js/modules/big-map/markers.js         # Map marker management
src/js/modules/big-map/loading.js         # Loading states
src/scss/components/bigmap/_index.scss    # Modular styling
includes/api/spatial-info-v3.php          # REST API endpoint
```

### JavaScript Class Structure
```javascript
class BigMapUI {
    constructor()          // Initialize handlers and mobile detection
    init()                // Setup map and event listeners
    initializeMap()       // Create Leaflet map instance
    updateMapAndSidebar() // Coordinate map and sidebar updates
}

class BigMapDataHandler {
    fetchListings(bbox)   // AJAX calls to v3 REST API
    onMapMoveEnd()        // Handle bounding box updates
}

class BigMapSidebar {
    updateSidebar()       // Populate sidebar with results
    setupSearchEventListeners() // Handle search functionality
    applySearch()         // Update URL with search parameters
}
```

### REST API Integration
- **Endpoint:** `geotour/v3/spatial-info`
- **Filters:** bbox, category, region, tag, search, acffield
- **Response:** Flat array format with listing metadata
- **Authentication:** WordPress nonce for security

### Mobile Responsiveness
- **Desktop:** Sidebar visible by default, map takes remaining space
- **Mobile:** Sidebar hidden by default, full-screen map with floating toggle
- **Touch:** Optimized touch interactions and map controls

## Basic Map Implementation

### HTML Structure
```html
<div id="your-map-id" class="geotour-map-container"></div>
```

### JavaScript Initialization
```javascript
// The theme automatically initializes maps with IDs:
// - 'listing-map' (single listings)
// - 'archive-map' (listing archives)

// For custom maps, use:
import { initializeGeotourMap } from './modules/maps/main.js';

const mapData = {
    coordinates: [35.2401, 24.8093], // [lat, lng]
    zoomLevel: 13,
    popupText: '<h5>Title</h5><p>Description</p>'
};

const map = initializeGeotourMap('your-map-id', mapData);
```

## Default Map Configuration

### Tile Layer
- **Provider:** OpenStreetMap
- **URL:** `https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png`
- **Max Zoom:** 19
- **Attribution:** Automatic

### Default Center
- **Location:** Crete, Greece
- **Coordinates:** [35.2401, 24.8093]
- **Zoom Level:** 9 (archive), 13 (single)

## Styling

### CSS Classes for Big Map
- `.big-map-container` - Full viewport container (fixed positioning)
- `.big-map` - Main map container (Leaflet target)
- `.map-sidebar` - Collapsible sidebar with listings
- `.floating-sidebar-toggle` - Mobile toggle button
- `.map-controls` - Custom map control buttons
- `.custom-map-marker` - Custom marker styling
- `.map-popup` - Popup content styling

### Responsive Heights
- **Big Map:** 100vh (full viewport)
- **Other Maps:**
  - **Mobile:** 300px
  - **Tablet:** 400px  
  - **Desktop:** 500px

## DO NOT DO

### ❌ Don't Import Leaflet in Theme Code
```javascript
// DON'T DO THIS - Leaflet is provided by plugin
import 'leaflet';
import L from 'leaflet';
```

### ❌ Don't Initialize Big Map Manually
```javascript
// DON'T DO THIS - BigMapUI auto-initializes
new BigMapUI();
```

### ❌ Don't Override Big Map Container Styles
```css
/* DON'T DO THIS - Breaks full-screen functionality */
.big-map-container {
    position: relative !important;
    height: 400px !important;
}
```

### ❌ Don't Use Vector Tiles
```javascript
// DON'T DO THIS - No vector tile support
L.vectorGrid.protobuf(url, options);
```

### ❌ Don't Add External Tile Providers Requiring API Keys
```javascript
// DON'T DO THIS - Avoid API dependencies
L.tileLayer('https://api.mapbox.com/...', {
    accessToken: 'your-api-key'
});
```

### ❌ Don't Override Default Styles Without Reason
```css
/* DON'T DO THIS - Breaks responsive design */
.leaflet-container {
    height: 400px !important;
}
```

### ❌ Don't Modify Big Map REST API Parameters
```javascript
// DON'T DO THIS - May break filtering
window.geotourBigMap.apiUrl = 'custom-endpoint';
```

## BEST PRACTICES

### ✅ Use Theme Functions for Regular Maps
```javascript
// DO THIS - Use theme's map initialization for non-big maps
initializeGeotourMap('map-id', mapData);
```

### ✅ Let Big Map Auto-Initialize
```javascript
// DO THIS - Big Map initializes automatically on page load
// No manual initialization needed for page-listing.php
```

### ✅ Use Big Map URL Parameters for Filtering
```url
# DO THIS - Use URL parameters for initial filtering
/listing?listing-category=museums&listing-region=heraklion&search=history
```

### ✅ Use Always-Visible Search Input
```javascript
// DO THIS - Search input is always present, no conditional display needed
// Users can search even when no search term is active
```

### ✅ Follow Naming Conventions
```html
<!-- DO THIS - Use theme CSS classes for regular maps -->
<div id="my-map" class="geotour-map-container custom-map"></div>

<!-- Big Map uses its own container structure -->
<div class="big-map-container">
    <div id="big-map" class="big-map"></div>
</div>
```

### ✅ Use Consistent Data Structure
```javascript
// DO THIS - Follow theme's data structure
const mapData = {
    coordinates: [lat, lng],
    zoomLevel: 12,
    popupText: 'HTML content',
    // Custom properties as needed
};
```

### ✅ Handle Coordinates Properly
```php
// DO THIS - Use theme utilities
$coordinates = geotour_get_listing_coordinates($post_id);
if ($coordinates) {
    // Safe to use coordinates
}
```

## Custom Map Examples

### Big Map URL Integration
```php
// DO THIS - Link to Big Map with filters
$big_map_url = add_query_arg([
    'listing-category' => 'museums',
    'listing-region' => 'heraklion',
    'search' => 'archaeological'
], '/listing');
echo '<a href="' . esc_url($big_map_url) . '">View on Map</a>';
```

### Simple Location Map
```javascript
const mapData = {
    coordinates: [35.2401, 24.8093],
    zoomLevel: 15,
    popupText: '<h5>My Location</h5><p>Description here</p>'
};
initializeGeotourMap('location-map', mapData);
```

### Multiple Markers Map
```javascript
// For multiple markers, extend the initialization
const map = initializeGeotourMap('multi-map', {
    coordinates: [35.2401, 24.8093],
    zoomLevel: 10
});

// Add additional markers
const marker1 = L.marker([35.2401, 24.8093]).addTo(map);
const marker2 = L.marker([35.3401, 24.9093]).addTo(map);
```

## File Structure Impact

### Modified Files
- `src/js/modules/maps/main.js` - Simplified to raster tiles
- `src/js/main.js` - Removed vector imports
- `package.json` - Removed vector dependencies

### Removed Files
- `src/js/modules/maps/vector-styles.js` - No longer needed

### Maintained Files
- `src/scss/components/_maps.scss` - Map styling (unchanged)
- `template-parts/listing/map-*.php` - Template parts (unchanged)

## Troubleshooting

### Big Map Not Loading
1. Check if geotour-crete-maps plugin is active
2. Verify page template is "Listing Map"
3. Check browser console for Leaflet errors
4. Ensure REST API endpoint is accessible

### Regular Maps Not Displaying
1. Check element ID exists in DOM
2. Verify container has height (CSS)
3. Check browser console for errors
4. Ensure Leaflet is loaded by plugin

### Markers Not Showing
1. Verify coordinates are valid numbers
2. Check coordinate format [lat, lng]
3. Ensure popup content is properly formatted
4. Check network tab for API response errors

### Sidebar Issues (Big Map)
1. Check mobile responsiveness settings
2. Verify sidebar toggle event listeners
3. Test on different screen sizes
4. Check for CSS conflicts with position: fixed

### Performance Issues
1. Limit number of markers on map
2. Use bounding box filtering for large datasets
3. Implement marker clustering for dense areas
4. Monitor API response times

## Support

### For Big Map Customizations
Modify the core BigMapUI class at:
`src/js/modules/big-map/main.js`

### For Regular Maps
Modify the regular map module at:
`src/js/modules/maps/main.js`

### For Styling
- Big Map: `src/scss/components/bigmap/_index.scss`
- Regular Maps: `src/scss/components/_maps.scss`

### For API Modifications
Backend endpoint: `includes/api/spatial-info-v3.php`

Always test changes across different devices and browsers, especially the responsive behavior of the Big Map interface.