/**
 * Route Preview Module for Big Map
 * 
 * Handles drawing routes between selected listings using OpenRouteService API
 * Adapted from the ListingSelector plugin
 */

import polyline from 'polyline';

export class BigMapRoutePreview {
    constructor(apiKey) {
        this.apiKey = apiKey;
        this.routeLayer = null;
        this.map = null;
    }

    /**
     * Initialize the route preview module with a map instance
     * @param {Object} map - Leaflet map instance
     */
    init(map) {
        this.map = map;
    }

    /**
     * Clear any existing route from the map
     */
    clearRoute() {
        if (this.routeLayer) {
            this.map.removeLayer(this.routeLayer);
            this.routeLayer = null;
        }
    }

    /**
     * Draw a route between all selected route listings
     * @param {Array} listings - Array of listing objects with route_order property
     * @returns {Promise<Object>} - Promise resolving to route metadata
     */
    async drawRoute(listings) {
        // Clear any existing route
        this.clearRoute();
        
        // Filter and sort listings by route_order
        const routeListings = listings
            .filter(listing => listing.route_order)
            .sort((a, b) => parseInt(a.route_order) - parseInt(b.route_order));
        
        console.log('Route listings:', routeListings);
        
        if (routeListings.length < 2) {
            return { 
                success: false, 
                message: "Need at least two route stops to draw a route."
            };
        }

        // Extract coordinates from listings
        const coordinates = routeListings.map(listing => {
            // Ensure we're using numerical values
            const lon = parseFloat(listing.longitude);
            const lat = parseFloat(listing.latitude);
            
            // Log any problematic coordinates
            if (isNaN(lon) || isNaN(lat)) {
                console.error('Invalid coordinates for listing:', listing);
            }
            
            return [lon, lat]; // [lon, lat] for OpenRouteService
        });
        
        console.log('Route coordinates:', coordinates);
        
        // Filter out any invalid coordinates
        const validCoordinates = coordinates.filter(
            coord => !isNaN(coord[0]) && !isNaN(coord[1])
        );
        
        if (validCoordinates.length < 2) {
            return {
                success: false,
                message: "Not enough valid coordinates to draw a route."
            };
        }

        // Draw route segments between consecutive stops
        const segments = [];
        let allSegmentsSuccessful = true;
        
        for (let i = 0; i < validCoordinates.length - 1; i++) {
            const start = validCoordinates[i];
            const end = validCoordinates[i + 1];
            // Try different transport profiles in order of preference
            const profiles = ['driving-car', 'cycling-regular', 'foot-walking'];
            
            const segmentResult = await this.drawRouteSegment(start, end, profiles);
            
            if (segmentResult) {
                segments.push(segmentResult);
            } else {
                allSegmentsSuccessful = false;
                const startListing = routeListings[i];
                const endListing = routeListings[i + 1];
                
                return {
                    success: false,
                    message: `Could not find a route between "${startListing.title}" and "${endListing.title}". Please try a different route.`
                };
            }
        }
        
        if (allSegmentsSuccessful) {
            // Draw the combined route and get metadata
            const routeMetadata = this.drawCombinedRoute(segments);
            return {
                success: true,
                ...routeMetadata
            };
        }
        
        return { success: false, message: "Failed to draw route." };
    }

    /**
     * Draw a route segment between two coordinates
     * @param {Array} start - Starting coordinates [lon, lat]
     * @param {Array} end - Ending coordinates [lon, lat]
     * @param {Array} profiles - Array of routing profiles to try
     * @returns {Promise<Object>} - Promise resolving to route segment data
     */
    async drawRouteSegment(start, end, profiles) {
        for (const profile of profiles) {
            const url = `https://api.openrouteservice.org/v2/directions/${profile}/json`;
            
            const body = JSON.stringify({
                coordinates: [start, end]
            });

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8',
                        'Authorization': this.apiKey,
                        'Content-Type': 'application/json; charset=utf-8'
                    },
                    body: body
                });
                
                if (!response.ok) {
                    const errorData = await response.json();
                    // If it's a "no route found" error, try the next profile
                    if (response.status === 404 && 
                        errorData.error && 
                        errorData.error.code === 2010) {
                        console.warn(`Profile ${profile} failed (2010), trying next.`);
                        continue;
                    }
                    throw new Error(`API error: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.routes && data.routes.length > 0) {
                    // Return the first route and the profile used
                    return { 
                        route: data.routes[0], 
                        profile: profile 
                    };
                }
            } catch (error) {
                console.error(`Error drawing route with ${profile}:`, error);
                // Continue to next profile on error
            }
        }
        
        // No profile succeeded
        return null;
    }

    /**
     * Draw a combined route from multiple segments
     * @param {Array} segments - Array of route segments
     * @returns {Object} - Route metadata
     */
    drawCombinedRoute(segments) {
        if (this.routeLayer) {
            this.map.removeLayer(this.routeLayer);
        }

        let totalDistance = 0;
        let totalDuration = 0;
        const profileColors = {
            'driving-car': '#3b82f6',  // Blue
            'cycling-regular': '#10b981', // Green
            'foot-walking': '#ef4444'  // Red
        };
        const usedProfiles = new Set();
        let drivingCarUsed = true;

        this.routeLayer = L.featureGroup();

        segments.forEach(segment => {
            const decodedPoints = polyline.decode(segment.route.geometry);
            const polylineOptions = {
                color: profileColors[segment.profile] || '#000000',
                weight: 5,
                opacity: 0.8
            };

            const segmentPolyline = L.polyline(decodedPoints, polylineOptions);
            this.routeLayer.addLayer(segmentPolyline);

            totalDistance += segment.route.summary.distance;
            totalDuration += segment.route.summary.duration;
            usedProfiles.add(segment.profile);
            
            if (segment.profile !== 'driving-car') {
                drivingCarUsed = false;
            }
        });

        this.routeLayer.addTo(this.map);

        if (this.routeLayer.getLayers().length > 0) {
            this.map.fitBounds(this.routeLayer.getBounds().pad(0.1));
        }

        // Prepare metadata
        const metadata = {
            distance: totalDistance,
            distanceFormatted: this.formatDistance(totalDistance),
            duration: totalDuration,
            durationFormatted: this.formatDuration(totalDuration),
            profiles: Array.from(usedProfiles),
            profilesFormatted: Array.from(usedProfiles).join(', '),
            drivingCarUsed: drivingCarUsed
        };

        return metadata;
    }

    /**
     * Format distance in meters to a human-readable string
     * @param {number} meters - Distance in meters
     * @returns {string} - Formatted distance
     */
    formatDistance(meters) {
        const km = (meters / 1000).toFixed(1);
        return `${km} km`;
    }

    /**
     * Format duration in seconds to a human-readable string
     * @param {number} seconds - Duration in seconds
     * @returns {string} - Formatted duration
     */
    formatDuration(seconds) {
        const days = Math.floor(seconds / (3600 * 24));
        const remainingSeconds = seconds % (3600 * 24);
        const hours = Math.floor(remainingSeconds / 3600);
        const minutes = Math.floor((remainingSeconds % 3600) / 60);
        
        let daysStr = days === 0 ? "" : days === 1 ? "1 day " : days + " days ";
        let hoursStr = hours === 0 ? "" : hours === 1 ? "1 hour " : hours + " hours ";
        let minutesStr = minutes === 0 ? "" : minutes === 1 ? "1 minute" : minutes + " minutes";
        
        return daysStr + hoursStr + minutesStr;
    }
}