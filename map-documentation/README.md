# Map System Overview

## Map Types

### Big Map (`/listing`)
- **File**: `page-listing.php`
- **Purpose**: Full-screen exploration with sidebar filtering
- **Features**: Real-time AJAX, responsive sidebar, search

### Single Maps
- **File**: `template-parts/listing/map-single.php`
- **Purpose**: Show one listing location
- **Features**: Static display, external controls (Google Maps, 3D)

### Archive Maps
- **File**: `template-parts/listing/map-archive.php`
- **Purpose**: Multiple listings on category pages
- **Features**: Multi-marker, clustering

## Dependencies
- **geotour-crete-maps plugin** (provides Leaflet.js)
- **REST API**: `/wp-json/geotour/v3/spatial-info`

## Quick Start
1. Big Map: Set page template to "Listing Map"
2. Single Maps: Auto-initialized by theme
3. Custom Maps: Use `initializeGeotourMap(id, data)`

## File Structure
```
src/js/modules/big-map/     # Big Map modules
template-parts/listing/     # Map templates  
src/scss/components/        # Map styles
includes/api/              # REST endpoints
```