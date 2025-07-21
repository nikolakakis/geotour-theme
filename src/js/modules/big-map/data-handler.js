// ==========================================================================
// BIG MAP DATA HANDLER
// ==========================================================================
// Handles API calls, data fetching, and state management

export class BigMapDataHandler {
    constructor() {
        this.isLoading = false;
        this.lastBounds = null;
        this.boundsTimeout = null;
        this.currentListings = []; // Store the current listings data
    }

    /**
     * Capitalizes the first letter of each word in a string.
     * @param {string} str The input string (e.g., 'hello-world').
     * @returns {string} The formatted string (e.g., 'Hello World').
     */
    _formatSlugAsName(str) {
        if (!str) return '';
        return str.replace(/-/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
    }

    /**
     * Transforms the flat data from the v3 API into the nested structure
     * expected by the v2-compatible frontend components.
     * @param {Array} v3Data The array of raw data from the v3 API.
     * @returns {Array} The transformed data.
     * @private
     */
    _transformV3Data(v3Data) {
        if (!Array.isArray(v3Data)) return [];

        return v3Data.map(item => {
            // Transform comma-separated slugs into arrays of objects
            const categories = item.category_slug ? item.category_slug.split(',').map(slug => ({
                slug: slug.trim(),
                name: this._formatSlugAsName(slug.trim())
            })) : [];

            const regions = item.region_slug ? item.region_slug.split(',').map(slug => ({
                slug: slug.trim(),
                name: this._formatSlugAsName(slug.trim())
            })) : [];

            const tags = item.tags_slug ? item.tags_slug.split(',').map(slug => ({
                slug: slug.trim(),
                name: this._formatSlugAsName(slug.trim())
            })) : [];

            // Return the v2-compatible structure
            return {
                id: item.source_id, // Map source_id to id
                title: item.title,
                excerpt: item.description || '', // Use description as excerpt
                meta_description: item.description || '',
                permalink: item.item_url,
                latitude: item.latitude,
                longitude: item.longitude,
                // CORRECTED: Use the map_icon_url directly from the API
                map_icon_url: item.map_icon_url, 
                categories: categories,
                regions: regions,
                tags: tags,
                featured_image: item.image_url,
                featured_image_medium: item.image_url, // Use same image for both sizes
                // Add route_order property if it exists
                route_order: item.route_order || null
            };
        });
    }

    async fetchListings(bbox = null) {
        const params = new URLSearchParams();

        // --- V3 API CHANGES ---
        // 1. ALWAYS specify the source_type for the listings map
        params.append('source_type', 'listing');

        // 2. Add bounding box if provided
        if (bbox) {
            params.append('bbox', bbox);
        }

        // 3. Add URL parameter filters using the NEW v3 keys
        if (window.geotourBigMap.urlParams.category) {
            params.append('category', window.geotourBigMap.urlParams.category);
        }
        if (window.geotourBigMap.urlParams.region) {
            params.append('region', window.geotourBigMap.urlParams.region);
        }
        if (window.geotourBigMap.urlParams.tag) {
            params.append('tag', window.geotourBigMap.urlParams.tag);
        }
        if (window.geotourBigMap.urlParams.search) {
            params.append('search', window.geotourBigMap.urlParams.search);
        }
        // Add acffield filter if present
        if (window.geotourBigMap.urlParams.acffield) {
            params.append('acffield', window.geotourBigMap.urlParams.acffield);
        }
        // Add route_listings filter if present
        if (window.geotourBigMap.urlParams.route_listings) {
            params.append('route_listings', window.geotourBigMap.urlParams.route_listings);
        }
        // --- END V3 API CHANGES ---

        const url = `${window.geotourBigMap.apiUrl}?${params.toString()}`;
        console.log('Fetching v3 listings with filters:', params.toString());

        const response = await fetch(url, {
            headers: {
                'X-WP-Nonce': window.geotourBigMap.nonce
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const rawData = await response.json();

        // Transform the v3 data to the structure the app expects
        const transformedData = this._transformV3Data(rawData);

        // Store the current listings
        this.currentListings = transformedData;

        return transformedData;
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

    // Add this method to the BigMapDataHandler class
    getCurrentListings() {
        // Return the most recently fetched listings
        return this.currentListings || [];
    }
}