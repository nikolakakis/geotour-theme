# Map Implementation Guide - Geotour Theme

## Overview
This guide explains how to implement maps in the Geotour WordPress theme. The theme uses **Leaflet.js** with **raster tile layers** for all mapping functionality. All necessary libraries are loaded by default.

## Core Libraries (Pre-loaded)
- **Leaflet.js** (v1.9.4) - Main mapping library
- **Leaflet.markercluster** (v1.5.3) - For marker clustering
- **Leaflet CSS** - Styling for map controls and UI

## Map Implementation Locations

### 1. Single Listing Maps
**File:** `template-parts/listing/map-single.php`
**Element ID:** `listing-map`
**Usage:** Display a single listing location

### 2. Archive Listing Maps
**File:** `template-parts/listing/map-archive.php`
**Element ID:** `archive-map`
**Usage:** Display multiple listings on one map

### 3. Custom Page Maps
**Element ID:** Any unique ID (e.g., `custom-map`)
**Usage:** Custom implementations

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

### CSS Classes
- `.geotour-map-container` - Main map container
- `.listing-single-map` - Single listing map variant
- `.listing-archive-map` - Archive map variant

### Responsive Heights
- **Mobile:** 300px
- **Tablet:** 400px
- **Desktop:** 500px

## DO NOT DO

### ❌ Don't Load Additional Map Libraries
```javascript
// DON'T DO THIS - Libraries are already loaded
import 'leaflet';
import L from 'leaflet';
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

## BEST PRACTICES

### ✅ Use Theme Functions
```javascript
// DO THIS - Use theme's map initialization
initializeGeotourMap('map-id', mapData);
```

### ✅ Follow Naming Conventions
```html
<!-- DO THIS - Use theme CSS classes -->
<div id="my-map" class="geotour-map-container custom-map"></div>
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

### Map Not Displaying
1. Check element ID exists in DOM
2. Verify container has height (CSS)
3. Check browser console for errors

### Markers Not Showing
1. Verify coordinates are valid numbers
2. Check coordinate format [lat, lng]
3. Ensure popup content is properly formatted

### Responsive Issues
1. Use theme CSS classes
2. Don't override container heights
3. Test on different screen sizes

## Support

For custom implementations beyond this guide, modify the core map module at:
`src/js/modules/maps/main.js`

Always test changes across different devices and browsers.