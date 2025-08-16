# Big Map - System Overview

## What is Big Map?
Full-screen map interface at `/listing` for exploring WordPress listings spatially with supplementary contextual data.

## Core Components
- **Map**: Leaflet.js with multiple marker types
- **Sidebar**: Responsive listing display with search (listings only)
- **API**: `/wp-json/geotour/v3/spatial-info`
- **Filtering**: Real-time by category, region, tag, search
- **Supplementary Data**: Panoramas, people, old photos, POIs (zoom-dependent)

## Map Pin Types

### Primary: Listings (All Zoom Levels)
- **Size**: 32px markers
- **Purpose**: Main content for route planning and sidebar
- **Features**: Route planning, sidebar display, filtering

### Secondary: Supplementary Data (Zoom 14+)
- **Panoramas**: Virtual tours (emerald border)
- **People**: Historical figures (blue border)  
- **Old Photos**: Historical images (amber border) - *Future*
- **POIs**: Points of interest (violet border) - *Future*
- **Size**: 24px markers
- **Purpose**: Contextual information only

## User Flow
1. **Load**: Map shows listings in default view
2. **Pan/Zoom**: Sidebar updates with visible listings
3. **Zoom In (14+)**: Supplementary data appears
4. **Filter**: Use search, click taxonomy tags (listings only)
5. **Interact**: Click markers → popups, click listings → focus map

## Responsive Behavior
- **Desktop (>1024px)**: Sidebar visible, can be hidden
- **Mobile (≤1024px)**: Sidebar hidden by default, floating toggle

## Data Pipeline
```
WordPress CPT → Cache Table → REST API → JavaScript → UI
                                    ↓
                               Zoom-based loading
                            Listings + Supplementary
```

## Key Files
```
page-listing.php                    # Main template
src/js/modules/big-map/main.js     # Orchestrator
src/js/modules/big-map/sidebar.js  # UI management
src/js/modules/big-map/markers.js  # Map handling + supplementary
includes/api/spatial-info-v3.php   # Data endpoint
```

## URL Parameters
- `listing-category`, `listing-region`, `listing-tag`
- `search` - text search
- `acffield` - custom field filtering
- `include_panorama=1`, `include_people=1` - auto-added at zoom 14+

All filters create shareable URLs and trigger page reload.