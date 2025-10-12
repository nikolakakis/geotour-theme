# Big Map Architecture - Complete Technical Analysis

## 📋 **Table of Contents**
1. [System Overview](#system-overview)
2. [Architecture & Components](#architecture--components)
3. [Data Flow](#data-flow)
4. [Module Responsibilities](#module-responsibilities)
5. [Key Features](#key-features)
6. [File Structure](#file-structure)
7. [Dependencies](#dependencies)
8. [API Integration](#api-integration)

---

## 🎯 **System Overview**

The **Big Map** is a sophisticated full-screen mapping interface located at `/listing` that provides spatial exploration of WordPress CPT listings with advanced features including:

- **Full-screen Leaflet map** with responsive sidebar
- **Real-time AJAX filtering** based on viewport bounds
- **Route planning system** with turn-by-turn directions
- **Zoom-dependent supplementary data** (panoramas, people, photos, POIs)
- **Thematic layers** (E4 Trail, boundaries, etc.)
- **Mobile-responsive design** with adaptive UI

### **Critical Dependency Note**
⚠️ **Leaflet.js is NOT bundled in the theme** - it's provided by the `geotour-crete-maps` plugin. The theme ONLY contains the UI/UX implementation.

---

## 🏗️ **Architecture & Components**

The Big Map follows a **modular MVC-like architecture** with clear separation of concerns:

### **Component Hierarchy**

```
BigMapUI (Main Controller/Orchestrator)
├── BigMapDataHandler (API & Data Layer)
├── BigMapMarkers (Map Markers & Popups)
├── BigMapSidebar (UI & Responsive Behavior)
├── BigMapLoadingStates (Loading Indicators)
├── BigMapRoutePreview (Route Drawing via OpenRouteService)
├── BigMapToolbar (Route Planner Controls)
└── ThematicLayerManager (Vector Layers: E4 Trail, etc.)
```

### **Technology Stack**

| Layer | Technology | Source |
|-------|-----------|--------|
| **Map Engine** | Leaflet.js v1.9.4 | geotour-crete-maps plugin |
| **Vector Tiles** | Leaflet.VectorGrid | CDN (for E4 trail) |
| **Routing** | OpenRouteService API | External API |
| **Data Source** | WordPress REST API v3 | Custom endpoints |
| **Frontend** | Vanilla JavaScript (ES6+) | Theme bundled via Vite |
| **Styling** | SCSS with CSS Modules | Theme compiled |

---

## 🔄 **Data Flow**

### **1. Initial Page Load**
```
User visits /listing
    ↓
page-listing.php loads
    ↓
Window.geotourBigMap config injected
    ↓
BigMapUI.init() executes
    ↓
initializeMap() creates Leaflet instance
    ↓
loadInitialData() fetches listings
    ↓
API: /wp-json/geotour/v3/spatial-info
    ↓
updateMapAndSidebar() renders markers + sidebar
```

### **2. Map Interaction (Pan/Zoom)**
```
User pans/zooms map
    ↓
onMapMoveEnd() triggered (debounced 1000ms)
    ↓
Calculate new bounding box
    ↓
fetchListings(bbox, zoom) with zoom-aware params
    ↓
zoom >= 14: include_panorama=1 & include_people=1
zoom < 14: listings only
    ↓
Transform v3 API data to v2 format
    ↓
Update markers (32px listings + 24px supplementary)
Update sidebar (listings only)
```

### **3. Route Planning Flow**
```
User clicks "Add to Route" in popup
    ↓
addToRoute(listingId) in sidebar/markers
    ↓
Update URL: ?route_listings=123,456,789
    ↓
Dispatch 'routeChanged' event
    ↓
Reload data with route_listings param
    ↓
Markers show route_order badges
    ↓
Toolbar appears with controls
    ↓
User clicks "Preview Route"
    ↓
RoutePreview.drawRoute(listings)
    ↓
OpenRouteService API generates geometry
    ↓
Route polyline drawn on map
```

---

## 📦 **Module Responsibilities**

### **1. BigMapUI (main.js)** - Orchestrator
**Role:** Central controller that coordinates all other modules

**Key Methods:**
- `init()` - Checks page context, initializes all modules
- `initializeMap()` - Creates Leaflet map instance
- `loadInitialData()` - Fetches initial dataset
- `updateMapAndSidebar(data)` - Syncs map and sidebar
- `handleMarkerClick(listingId)` - Coordinates marker-sidebar interaction
- `handleRouteChange()` - Processes route modifications
- `zoomToRouteExtent(listings)` - Fits map to route bounds

**Event Listeners:**
- Map movement (pan/zoom)
- Window resize
- Custom 'routeChanged' events
- Control button clicks (locate, fit bounds, reset)

---

### **2. BigMapDataHandler (data-handler.js)** - API Layer
**Role:** Manages all API communication and data transformation

**Key Methods:**
- `fetchListings(bbox, currentZoom)` - Main API call with zoom awareness
- `_transformV3Data(v3Data)` - Converts API v3 → v2 format
- `_transformSupplementaryData(data)` - Processes panoramas/people/etc
- `onMapMoveEnd(map, callback)` - Debounced update handler
- `getCurrentListings()` - Returns cached listings

**API Parameters Handled:**
```javascript
{
  bbox: "west,south,east,north",
  category: "listing-category-slug",
  region: "listing-region-slug", 
  tag: "listing-tag-slug",
  search: "search terms",
  acffield: "custom_field_value",
  route_listings: "123,456,789",
  include_panorama: 1,  // zoom >= 14
  include_people: 1,     // zoom >= 14
  // Future: include_oldphotos, include_pois
}
```

**Data Transformation:**
```javascript
// INPUT (v3 API):
{
  source_id: 123,
  source_type: "listing",
  category_slug: "museum,historical",
  latitude: 35.24,
  longitude: 24.80
}

// OUTPUT (v2 format):
{
  id: 123,
  categories: [
    {slug: "museum", name: "Museum"},
    {slug: "historical", name: "Historical"}
  ],
  latitude: 35.24,
  longitude: 24.80,
  route_order: 1  // if in route
}
```

---

### **3. BigMapMarkers (markers.js)** - Map Rendering
**Role:** Creates and manages all Leaflet markers

**Key Methods:**
- `updateMap(map, data)` - Refreshes all markers
- `createMarker(listing)` - Creates 32px listing marker
- `createSupplementaryMarker(item)` - Creates 24px supplementary marker
- `createPopupContent(listing)` - Generates popup HTML
- `panToListing(map, listing)` - Smooth pan to location
- `fitBounds(map)` - Fits all markers in view
- `locateUser(map)` - Geolocation functionality

**Marker Types:**

| Type | Size | Border Color | Zoom Level | Sidebar Display |
|------|------|--------------|------------|-----------------|
| Listing | 32px | None | All | ✅ Yes |
| Panorama | 24px | Emerald | 14+ | ❌ No |
| People | 24px | Blue | 14+ | ❌ No |
| Old Photos | 24px | Amber | 14+ | ❌ No (future) |
| POIs | 24px | Violet | 14+ | ❌ No (future) |

**Route Marker Features:**
- Shows route_order number badge
- "Reorder" button in popup
- "Remove from Route" button
- Visual distinction (CSS class)

---

### **4. BigMapSidebar (sidebar.js)** - UI Management
**Role:** Handles sidebar display, search, and responsive behavior

**Key Methods:**
- `initialize()` - Setup event listeners
- `updateSidebar(listings)` - Refreshes listing display
- `createListingItem(listing)` - Generates listing card HTML
- `toggleSidebar()` - Mobile collapse/expand
- `applySearch(searchTerm)` - Client-side search
- `applyFilter(type, value)` - URL-based filtering
- `highlightListing(listingId)` - Focuses sidebar item

**Responsive Behavior:**
```scss
// Desktop (>1024px): Sidebar visible by default
.map-sidebar {
  width: 400px;
  transform: translateX(0);
}

// Mobile (≤1024px): Sidebar hidden, floatingtoggle appears
.map-sidebar {
  transform: translateX(-100%);
  &.open { transform: translateX(0); }
}

.floating-sidebar-toggle {
  display: none;
  @media (max-width: 1024px) {
    display: block;
  }
}
```

**Important:** Sidebar ONLY displays main listings, never supplementary data.

---

### **5. BigMapLoadingStates (loading.js)** - Loading UI
**Role:** Manages loading indicators across the interface

**Key Methods:**
- `showLoading(mapOnly)` - Shows loading overlay
- `hideLoading()` - Hides all loading states
- `showError(message)` - Displays error message

**Loading States:**
- Global overlay (initial load)
- Map indicator (data fetching)
- Sidebar indicator (sidebar updates)

---

### **6. BigMapRoutePreview (route-preview.js)** - Route Rendering
**Role:** Draws routes between listings using OpenRouteService

**Key Methods:**
- `init(map)` - Initialize with map instance
- `drawRoute(listings)` - Main route drawing logic
- `drawRouteSegment(start, end, profiles)` - Single segment
- `clearRoute()` - Removes route from map
- `formatDistance(meters)` - Human-readable distance
- `formatDuration(seconds)` - Human-readable time

**Routing Logic:**
```javascript
// Attempts profiles in order:
1. driving-car (primary)
2. cycling-regular (fallback)
3. foot-walking (last resort)

// Creates combined route with:
- Total distance
- Total duration
- Color-coded segments by transport mode
```

**API Used:** OpenRouteService (requires API key)

---

### **7. BigMapToolbar (toolbar-functions.js)** - Route Controls
**Role:** Manages route planner toolbar functionality

**Toolbar Buttons:**

| Button | Method | Functionality |
|--------|--------|---------------|
| Zoom to Route | `zoomToRoute()` | Fits map to route extent |
| Preview Route | `previewRoute()` | Draws route with directions |
| Export to Google Maps | `exportToGoogleMaps()` | Opens Google Maps with waypoints |
| Export to GeoJSON | `exportToGeoJSON()` | Downloads GeoJSON file |
| Copy Shareable Link | `copyShareableLink()` | Copies URL to clipboard |
| Clear Route | `clearRoute()` | Removes all route listings |

**Toolbar Behavior:**
- Hidden when no route listings
- Shows listing count when route active
- Displays route metadata after preview (distance, time, modes)

---

### **8. ThematicLayerManager (thematic-layers.js)** - Vector Layers
**Role:** Manages non-marker layers (trails, boundaries, etc.)

**Key Methods:**
- `init(map)` - Initialize with map
- `initializeE4Trail()` - Loads E4 trail vector tiles
- `createE4TrailLayers()` - Dual-layer dashed effect
- `setupZoomListener()` - Zoom-based visibility
- `toggleLayer(name, visible)` - Manual layer control

**E4 Trail Implementation:**
```javascript
// Two-layer technique for dashed effect:
1. Base layer: Solid orange line (4px)
2. Top layer: Dashed white line (2px)

// Vector tiles from PostGIS:
URL: 'https://geotour.gr/vector-tiles/{z}/{x}/{y}.pbf'
Layer: 'e4_trail_europe'
Zoom visibility: 10-18
```

**Future Layers:**
- Administrative boundaries
- Protected areas
- Other hiking trails
- Municipality borders

---

## 🗂️ **File Structure**

```
theme-root/
├── page-listing.php                    # Main template
├── src/
│   ├── js/
│   │   └── modules/
│   │       └── big-map/
│   │           ├── main.js            # Orchestrator
│   │           ├── data-handler.js    # API layer
│   │           ├── markers.js         # Map markers
│   │           ├── sidebar.js         # Sidebar UI
│   │           ├── loading.js         # Loading states
│   │           ├── route-preview.js   # Route drawing
│   │           ├── toolbar-functions.js # Toolbar
│   │           └── thematic-layers.js # Vector layers
│   └── scss/
│       └── components/
│           └── bigmap/
│               ├── _index.scss        # Main import
│               ├── _container.scss    # Layout
│               ├── _sidebar.scss      # Sidebar styles
│               ├── _listings.scss     # Listing cards
│               ├── _markers.scss      # Marker styles
│               ├── _controls.scss     # Control buttons
│               ├── _loading.scss      # Loading states
│               ├── _filters.scss      # Filter chips
│               ├── _toolbar.scss      # Route toolbar
│               └── _thematic-layers.scss # Trail styles
├── includes/
│   └── api/
│       ├── spatial-info-v2.php        # Legacy API
│       └── spatial-info-v3.php        # Current API
└── map-documentation/
    ├── big-map-overview.md
    ├── big-map-technical.md
    └── route-planner.md
```

---

## 🔌 **Dependencies**

### **External (Plugin-Provided)**
```json
{
  "leaflet": "^1.9.4",  // geotour-crete-maps plugin
  "leaflet.markercluster": "^1.5.3"  // plugin
}
```

### **Theme-Bundled (Vite)**
```json
{
  "polyline": "^0.2.0",  // Route encoding
  "@fancyapps/ui": "^5.0.36"  // Gallery (not used in Big Map)
}
```

### **CDN-Loaded**
```
Leaflet.VectorGrid: https://unpkg.com/leaflet.vectorgrid@1.3.0/
OpenRouteService: https://api.openrouteservice.org/
```

---

## 🌐 **API Integration**

### **Endpoint**
```
GET /wp-json/geotour/v3/spatial-info
```

### **Request Parameters**
```php
// Bounding box filter
?bbox=24.5,35.0,25.5,35.5

// Taxonomy filters
?category=museum
?region=heraklion
?tag=minoan

// Text search
?search=palace

// Custom field filter
?acffield=special_value

// Route planning
?route_listings=123,456,789

// Supplementary data (auto-added at zoom 14+)
?include_panorama=1
?include_people=1
?include_oldphotos=1  // Future
?include_pois=1        // Future
```

### **Response Format**
```json
[
  {
    "source_id": 123,
    "source_type": "listing",
    "title": "Knossos Palace",
    "latitude": 35.2979,
    "longitude": 25.1631,
    "category_slug": "archaeological,minoan",
    "region_slug": "heraklion",
    "map_icon_url": "https://...",
    "route_order": 1
  },
  {
    "source_id": 456,
    "source_type": "panorama",
    "title": "Virtual Tour - Knossos",
    "latitude": 35.2980,
    "longitude": 25.1630,
    "panorama_url": "https://...",
    "map_icon_url": "https://..."
  }
]
```

### **Data Transformation Pipeline**
```javascript
1. API Response (v3 format)
   ↓
2. _transformV3Data() - Main listings
   ↓
3. _transformSupplementaryData() - Panoramas/People
   ↓
4. Return: { listings: [...], supplementary: [...] }
   ↓
5. updateMap() - Render markers
   ↓
6. updateSidebar() - Display listings only
```

---

## 🎨 **Key Features Explained**

### **1. Zoom-Dependent Loading**
```javascript
// In data-handler.js
if (currentZoom && currentZoom >= 14) {
  params.append('include_panorama', '1');
  params.append('include_people', '1');
  console.log('Loading supplementary data at zoom', currentZoom);
} else {
  console.log('Listings only at zoom', currentZoom);
}
```

**Why?**
- Performance: Reduces data transfer at low zoom
- UX: Supplementary data only useful when zoomed in
- Scalability: Prevents overwhelming map with markers

### **2. Debounced Map Updates**
```javascript
// In data-handler.js - 1000ms debounce
this.boundsTimeout = setTimeout(async () => {
  const bbox = this.calculateBounds();
  if (bbox !== this.lastBounds) {
    await fetchListings(bbox, zoom);
  }
}, 1000);
```

**Why?**
- Performance: Prevents excessive API calls during pan/zoom
- UX: Smooth user experience
- Server load: Reduces backend pressure

### **3. Route Planning URL State**
```javascript
// URL format:
/listing?route_listings=123,456,789

// Enables:
- Shareable routes
- Browser back/forward
- Direct route loading
- Persistent state
```

### **4. Marker Differentiation**
```scss
// Listings: 32px, full color
.custom-map-marker {
  width: 32px;
  height: 32px;
}

// Supplementary: 24px, bordered
.supplementary-marker {
  width: 24px;
  height: 24px;
  border: 2px solid;
  
  &.marker-panorama { border-color: #10b981; }  // Emerald
  &.marker-people { border-color: #3b82f6; }     // Blue
  &.marker-oldphotos { border-color: #f59e0b; }  // Amber
  &.marker-pois { border-color: #8b5cf6; }       // Violet
}
```

---

## 🚀 **Performance Optimizations**

1. **Debounced API Calls** - 1000ms delay on map movement
2. **Marker Cleanup** - Removes old markers before adding new
3. **Loading State Management** - Prevents concurrent requests
4. **Bounds Change Detection** - Only fetches if bbox changed
5. **Zoom-Based Loading** - Progressive data enhancement
6. **Client-Side Caching** - Stores current listings in memory

---

## 📱 **Responsive Design**

### **Breakpoints**
```scss
// Desktop: 1025px+
- Sidebar visible (400px width)
- Horizontal layout
- All controls visible

// Mobile: ≤1024px
- Sidebar hidden (floating toggle)
- Vertical stacking
- Compact controls
```

### **Touch Optimization**
- 44px minimum touch targets
- Swipe gestures for sidebar
- Large tap areas for markers
- Mobile-friendly popups

---

## 🔧 **Configuration**

### **Global Config (page-listing.php)**
```javascript
window.geotourBigMap = {
  apiUrl: '<?php echo rest_url('geotour/v3/spatial-info'); ?>',
  nonce: '<?php echo wp_create_nonce('wp_rest'); ?>',
  defaultCenter: [35.2401, 24.8093],  // Heraklion
  defaultZoom: 10,
  defaultIconUrl: 'data:image/svg+xml;base64,...',
  urlParams: {
    category: '<?php echo $_GET['listing-category'] ?? ''; ?>',
    region: '<?php echo $_GET['listing-region'] ?? ''; ?>',
    // ... more params
  },
  strings: {
    loadingError: '<?php _e('Error...', 'geotour'); ?>',
    noResults: '<?php _e('No listings...', 'geotour'); ?>',
    // ... more strings
  }
};
```

---

## 🐛 **Common Issues & Solutions**

### **Issue: Map not loading**
**Check:**
1. Is `geotour-crete-maps` plugin active?
2. Console error: "Leaflet not found"?
3. Is `.big-map-container` present in DOM?

### **Issue: Markers not appearing**
**Check:**
1. API returning data? (Network tab)
2. Coordinates valid? (latitude/longitude)
3. Map bounds correct?
4. Zoom level appropriate?

### **Issue: Route not drawing**
**Check:**
1. OpenRouteService API key valid?
2. At least 2 route listings selected?
3. Console errors from routing API?
4. Coordinates in correct [lon, lat] order?

---

## 📝 **Future Enhancements**

### **Planned Features**
- [ ] Old Photos layer (zoom 14+)
- [ ] POIs layer (zoom 14+)
- [ ] Administrative boundaries
- [ ] Municipality filters
- [ ] Heatmap mode
- [ ] Offline support
- [ ] Progressive Web App (PWA)
- [ ] Advanced route optimization
- [ ] Multi-day itineraries

### **Performance Improvements**
- [ ] Virtual scrolling for sidebar (large datasets)
- [ ] Web Workers for data processing
- [ ] IndexedDB caching
- [ ] Service Worker for offline maps

---

## 📚 **Additional Documentation**

- [Big Map Overview](./map-documentation/big-map-overview.md)
- [Big Map Technical](./map-documentation/big-map-technical.md)
- [Route Planner](./map-documentation/route-planner.md)
- [Regular Maps](./map-documentation/regular-maps.md)
- [Troubleshooting](./map-documentation/troubleshooting.md)

---

**Last Updated:** October 12, 2025  
**Version:** 1.4.4.1  
**Maintainer:** Geotour Development Team
