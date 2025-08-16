# Big Map - Technical Implementation

## JavaScript Classes

### BigMapUI (main.js)
- **Role**: Orchestrator
- **Auto-init**: `DOMContentLoaded` on `.big-map-container`
- **Key Methods**: `updateMapAndSidebar()`, `handleMarkerClick()`

### BigMapDataHandler (data-handler.js)
- **Role**: API calls and data transformation
- **Endpoint**: `/wp-json/geotour/v3/spatial-info`
- **Key Methods**: `fetchListings(bbox, currentZoom)`, `onMapMoveEnd()`
- **Debouncing**: 1000ms on map movement
- **Zoom-aware**: Fetches supplementary data (panoramas, people) at zoom >= 14

### BigMapSidebar (sidebar.js)
- **Role**: UI and responsive behavior
- **Breakpoint**: 1024px
- **Key Methods**: `updateSidebar()`, `toggleSidebar()`, `applySearch()`
- **Note**: Only displays main listings, not supplementary data

### BigMapMarkers (markers.js)
- **Role**: Leaflet marker management
- **Key Methods**: `updateMap()`, `createMarker()`, `createSupplementaryMarker()`, `panToListing()`
- **Marker Types**: Main listings (32px) + supplementary data (24px, colored borders)

### BigMapLoadingStates (loading.js)
- **Role**: Loading indicators
- **Key Methods**: `showLoading()`, `hideLoading()`, `showError()`

## Data Flow

### Initial Load
```
page-listing.php → BigMapUI.init() → fetchListings(null, zoom) → updateMapAndSidebar()
```

### Map Movement
```
pan/zoom → onMapMoveEnd(debounced) → fetchListings(bbox, zoom) → updateMapAndSidebar()
```

### Zoom-Based Data Loading
```
zoom < 14: listings only
zoom >= 14: listings + panoramas + people + (future: oldphotos, pois)
```

### Filtering
```
click filter → applyFilter() → URL update → page reload
```

## API Integration

### Parameters
- `bbox` (west,south,east,north)
- `category`, `region`, `tag`, `search`, `acffield`
- `include_panorama=1`, `include_people=1` (when zoom >= 14)
- **Note**: No `source_type` restriction - data sources controlled via include switches

### Data Structure
```javascript
// Response format:
{
  listings: [...],      // Main listings for sidebar & routes
  supplementary: [...]  // Panoramas, people, etc. (map only)
}
```

### Data Transformation (v3 → v2)
```javascript
// Input: {source_id: 123, category_slug: "museum,historical"}
// Output: {id: 123, categories: [{slug: "museum", name: "Museum"}]}
```

## Supplementary Data Types

### 1. Panoramas (panorama)
- **Icon**: 24px with emerald border
- **Popup**: Virtual Tour link
- **Zoom**: 14+

### 2. People (people)
- **Icon**: 24px with blue border  
- **Popup**: Historical figure info with dates/roles
- **Zoom**: 14+

### 3. Old Photos (oldphotos) - Future
- **Icon**: 24px with amber border
- **Zoom**: 14+

### 4. POIs (pois) - Future  
- **Icon**: 24px with violet border
- **Zoom**: 14+

## Responsive Implementation

### CSS
```scss
.map-sidebar {
    @media (max-width: 1024px) {
        transform: translateX(-100%);
        &.open { transform: translateX(0); }
    }
}
```

### JavaScript
```javascript
this.isMobile = window.innerWidth <= 1024;
this.sidebarVisible = !this.isMobile;
```

## Key Configuration

### Template Data Passing
```javascript
window.geotourBigMap = {
    apiUrl: '<?php echo rest_url('geotour/v3/spatial-info'); ?>',
    nonce: '<?php echo wp_create_nonce('wp_rest'); ?>',
    defaultCenter: [35.2401, 24.8093],
    urlParams: { category: '...', search: '...' }
};
```

## Performance Optimizations
- Debounced API calls (1000ms)
- Marker cleanup on updates
- Loading state management
- Bounds change detection
- Zoom-based data loading (supplementary data only at high zoom)