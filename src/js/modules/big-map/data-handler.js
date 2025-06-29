// ==========================================================================
// BIG MAP DATA HANDLER
// ==========================================================================
// Handles API calls, data fetching, and state management

export class BigMapDataHandler {
    constructor() {
        this.isLoading = false;
        this.lastBounds = null;
        this.boundsTimeout = null;
    }
    
    async fetchListings(bbox = null) {
        const params = new URLSearchParams();
        
        // Add bounding box if provided
        if (bbox) {
            params.append('bbox', bbox);
        }
        
        // Add URL parameter filters if they exist
        if (window.geotourBigMap.urlParams.category) {
            params.append('listing_category', window.geotourBigMap.urlParams.category);
        }
        if (window.geotourBigMap.urlParams.region) {
            params.append('listing_region', window.geotourBigMap.urlParams.region);
        }
        if (window.geotourBigMap.urlParams.tag) {
            params.append('listing_tag', window.geotourBigMap.urlParams.tag);
        }
        
        // Add search parameter if it exists
        if (window.geotourBigMap.urlParams.search) {
            params.append('search', window.geotourBigMap.urlParams.search);
        }
        
        const url = `${window.geotourBigMap.apiUrl}?${params.toString()}`;
        console.log('Fetching listings with filters:', params.toString());
        
        const response = await fetch(url, {
            headers: {
                'X-WP-Nonce': window.geotourBigMap.nonce
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        return data;
    }
    
    async onMapMoveEnd(map, updateCallback) {
        if (this.isLoading) return;
        
        // Debounce the bounding box updates
        if (this.boundsTimeout) {
            clearTimeout(this.boundsTimeout);
        }
        
        this.boundsTimeout = setTimeout(async () => {
            // Get current bounding box
            const bounds = map.getBounds();
            const bbox = `${bounds.getWest().toFixed(6)},${bounds.getSouth().toFixed(6)},${bounds.getEast().toFixed(6)},${bounds.getNorth().toFixed(6)}`;
            
            // Check if bounds have changed significantly (prevent duplicate calls)
            if (this.lastBounds && this.lastBounds === bbox) {
                return;
            }
            
            this.lastBounds = bbox;
            console.log('Map bounds changed, fetching new data for bbox:', bbox);
            
            try {
                this.isLoading = true;
                const data = await this.fetchListings(bbox);
                updateCallback(data);
            } catch (error) {
                console.error('Error loading data for current view:', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        }, 1000); // Increased debounce to 1 second to reduce API calls
    }
    
    setLoading(loading) {
        this.isLoading = loading;
    }
    
    getLoadingState() {
        return this.isLoading;
    }
}
