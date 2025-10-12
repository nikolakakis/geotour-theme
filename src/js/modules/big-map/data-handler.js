// ==========================================================================
// BIG MAP DATA HANDLER
// ==========================================================================
// Handles API calls, data fetching, and state management

export class BigMapDataHandler {
    constructor() {
        this.isLoading = false;
        this.lastBounds = null;
        this.lastZoom = null; // Track zoom level changes
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

    async fetchListings(bbox = null, currentZoom = null) {
        const params = new URLSearchParams();

        // --- V3 API CHANGES ---
        // 1. Add bounding box if provided
        if (bbox) {
            params.append('bbox', bbox);
        }

        // 2. Add URL parameter filters using the NEW v3 keys
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

        // 3. Control data sources via switches - listings are always included
        // Supplementary data sources at high zoom levels (14+)
        if (currentZoom && currentZoom >= 14) {
            params.append('include_panorama', '1');
            params.append('include_people', '1');
            console.log(`Zoom level ${currentZoom} >= 14: Including supplementary data`);
            // Future: include_oldphotos=1 and include_pois=1
        } else {
            console.log(`Zoom level ${currentZoom} < 14: Only listings`);
        }
        // --- END V3 API CHANGES ---

        const url = `${window.geotourBigMap.apiUrl}?${params.toString()}`;
        console.log('=== BIG MAP API REQUEST ===');
        console.log('Full REST URL:', url);
        console.log('Zoom level:', currentZoom);
        console.log('Include supplementary:', currentZoom >= 14);
        console.log('Route listings:', window.geotourBigMap.urlParams.route_listings);
        console.log('==========================');

        const response = await fetch(url, {
            headers: {
                'X-WP-Nonce': window.geotourBigMap.nonce
            }
        });

        if (!response.ok) {
            console.error('API Error:', response.status, response.statusText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const rawData = await response.json();
        
        console.log('=== API RESPONSE ===');
        console.log('Total items received:', rawData.length);
        console.log('====================');
        
        // Check if rawData is an array
        if (!Array.isArray(rawData)) {
            console.error('API did not return an array:', rawData);
            throw new Error('Invalid API response format');
        }

        // Separate listings from supplementary data
        const listings = rawData.filter(item => item.source_type === 'listing');
        const supplementaryData = rawData.filter(item => item.source_type !== 'listing');
        
        console.log(`Listings: ${listings.length}, Supplementary: ${supplementaryData.length}`);

        // Transform the v3 data to the structure the app expects
        const transformedListings = this._transformV3Data(listings);
        const transformedSupplementary = this._transformSupplementaryData(supplementaryData);

        // Store the current listings (only main listings, not supplementary)
        this.currentListings = transformedListings;

        return {
            listings: transformedListings,
            supplementary: transformedSupplementary
        };
    }

    /**
     * Transforms supplementary data (panoramas, people, etc.) for map display
     * @param {Array} supplementaryData The array of supplementary data from the v3 API.
     * @returns {Array} The transformed supplementary data.
     * @private
     */
    _transformSupplementaryData(supplementaryData) {
        if (!Array.isArray(supplementaryData)) return [];

        return supplementaryData.map(item => {
            return {
                id: item.source_id, // Use source_id as unique identifier
                source_type: item.source_type, // panorama, people, oldphotos, pois
                title: item.title,
                description: item.description || '',
                latitude: item.latitude,
                longitude: item.longitude,
                map_icon_url: item.map_icon_url,
                image_url: item.image_url,
                item_url: item.item_url,
                acf_fields: item.acf_fields || {},
                // Add category info for filtering if needed
                category_slug: item.category_slug,
                region_slug: item.region_slug,
                tags_slug: item.tags_slug
            };
        });
    }

    async onMapMoveEnd(map, updateCallback) {
        if (this.isLoading) return;

        // Debounce the bounding box updates
        if (this.boundsTimeout) {
            clearTimeout(this.boundsTimeout);
        }

        this.boundsTimeout = setTimeout(async () => {
            // Get current bounding box and zoom
            const bounds = map.getBounds();
            const bbox = `${bounds.getWest().toFixed(6)},${bounds.getSouth().toFixed(6)},${bounds.getEast().toFixed(6)},${bounds.getNorth().toFixed(6)}`;
            const currentZoom = map.getZoom();

            // Check if bounds OR zoom have changed significantly
            const boundsChanged = this.lastBounds !== bbox;
            const zoomChanged = this.lastZoom !== currentZoom;
            const zoomCrossedThreshold = (this.lastZoom < 14 && currentZoom >= 14) || (this.lastZoom >= 14 && currentZoom < 14);

            if (!boundsChanged && !zoomChanged && !zoomCrossedThreshold) {
                console.log('No significant changes, skipping fetch');
                return;
            }

            this.lastBounds = bbox;
            this.lastZoom = currentZoom;
            
            console.log(`Map changed - Bounds: ${boundsChanged}, Zoom: ${zoomChanged} (${this.lastZoom} → ${currentZoom}), Threshold crossed: ${zoomCrossedThreshold}`);

            try {
                this.isLoading = true;
                const data = await this.fetchListings(bbox, currentZoom);
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