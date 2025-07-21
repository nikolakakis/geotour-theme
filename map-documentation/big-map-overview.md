# Big Map - System Overview

## What is Big Map?
Full-screen map interface at `/listing` for exploring WordPress listings spatially.

## Core Components
- **Map**: Leaflet.js with markers
- **Sidebar**: Responsive listing display with search
- **API**: `/wp-json/geotour/v3/spatial-info`
- **Filtering**: Real-time by category, region, tag, search

## User Flow
1. **Load**: Map shows all listings in default view
2. **Pan/Zoom**: Sidebar updates with visible area listings
3. **Filter**: Use search, click taxonomy tags
4. **Interact**: Click markers → popups, click listings → focus map

## Responsive Behavior
- **Desktop (>1024px)**: Sidebar visible, can be hidden
- **Mobile (≤1024px)**: Sidebar hidden by default, floating toggle

## Data Pipeline
```
WordPress CPT → Cache Table → REST API → JavaScript → UI
```

## Key Files
```
page-listing.php                    # Main template
src/js/modules/big-map/main.js     # Orchestrator
src/js/modules/big-map/sidebar.js  # UI management
src/js/modules/big-map/markers.js  # Map handling
includes/api/spatial-info-v3.php   # Data endpoint
```

## URL Parameters
- `listing-category`, `listing-region`, `listing-tag`
- `search` - text search
- `acffield` - custom field filtering

All filters create shareable URLs and trigger page reload.