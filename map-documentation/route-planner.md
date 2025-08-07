# Route Planner System

## Overview

The Route Planner is an integrated feature of the Big Map system that allows users to:
- Select listings to create a custom route
- Reorder route stops
- Preview the route with directions between stops
- Export routes to Google Maps
- Export routes as GeoJSON
- Share routes via URL
- Zoom to the extent of all route stops

## User Features

### Adding Listings to a Route
- Click the "Add to Route" button in a listing popup
- The listing is assigned a route order number
- A toolbar appears at the bottom of the screen when at least one listing is added

### Changing Route Order
- Click on the route order number in a listing popup
- Enter a new order number
- All other listings are automatically reordered

### Removing Listings from a Route
- Click the "Remove from Route" button in a listing popup
- The listing is removed from the route
- All other listings are automatically reordered

### Route Toolbar Functions

The toolbar appears automatically when at least one listing is added to the route:

| Button | Function | Description |
|--------|----------|-------------|
| Zoom to Route | `zoomToRoute()` | Fits the map view to show all route stops |
| Preview Route | `previewRoute()` | Draws a route line between all stops using OpenRouteService API |
| Export to Google Maps | `exportToGoogleMaps()` | Opens Google Maps with all stops as waypoints |
| Export to GeoJSON | `exportToGeoJSON()` | Downloads a GeoJSON file with all route stops |
| Copy Shareable Link | `copyShareableLink()` | Copies a URL with the current route to clipboard |
| Clear Route | `clearRoute()` | Removes all listings from the route |

### Route Preview Details

When a route is previewed, the toolbar displays:
- Total distance
- Estimated travel time (for driving routes)
- Transportation modes used

## Technical Implementation

### File Structure
