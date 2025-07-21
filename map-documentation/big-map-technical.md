# Big Map - Technical Implementation

## JavaScript Classes

### BigMapUI (main.js)
- **Role**: Orchestrator
- **Auto-init**: `DOMContentLoaded` on `.big-map-container`
- **Key Methods**: `updateMapAndSidebar()`, `handleMarkerClick()`

### BigMapDataHandler (data-handler.js)
- **Role**: API calls and data transformation
- **Endpoint**: `/wp-json/geotour/v3/spatial-info`
- **Key Methods**: `fetchListings(bbox)`, `onMapMoveEnd()`
- **Debouncing**: 1000ms on map movement

### BigMapSidebar (sidebar.js)
- **Role**: UI and responsive behavior
- **Breakpoint**: 1024px
- **Key Methods**: `updateSidebar()`, `toggleSidebar()`, `applySearch()`

### BigMapMarkers (markers.js)
- **Role**: Leaflet marker management
- **Key Methods**: `updateMap()`, `createMarker()`, `panToListing()`

### BigMapLoadingStates (loading.js)
- **Role**: Loading indicators
- **Key Methods**: `showLoading()`, `hideLoading()`, `showError()`

## Data Flow

### Initial Load
```
page-listing.php → BigMapUI.init() → fetchListings() → updateMapAndSidebar()
```

### Map Movement
```
pan/zoom → onMapMoveEnd(debounced) → fetchListings(bbox) → updateMapAndSidebar()
```

### Filtering
```
click filter → applyFilter() → URL update → page reload
```

## API Integration

### Parameters
- `source_type=listing` (always set)
- `bbox` (west,south,east,north)
- `category`, `region`, `tag`, `search`, `acffield`

### Data Transformation (v3 → v2)
```javascript
// Input: {source_id: 123, category_slug: "museum,historical"}
// Output: {id: 123, categories: [{slug: "museum", name: "Museum"}]}
```

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