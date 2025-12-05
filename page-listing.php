<?php
/**
 * Template Name: Listing Map
 * Template for the Listing Map UI
 * Full-screen map with sidebar for spatial navigation
 * page-listing.php
 * @package Geotour_Mobile_First
 */

get_header(); 

// Ensure VectorGrid is loaded for E4 trail functionality
wp_enqueue_script(
    'leaflet-vectorgrid-direct',
    'https://unpkg.com/leaflet.vectorgrid@1.3.0/dist/Leaflet.VectorGrid.bundled.js',
    array(),
    '1.3.0',
    false // Load in head to ensure it's available early
);
?>

<!-- Additional head scripts for map functionality -->
<script>
console.log('page-listing.php: Loading VectorGrid...');
if (typeof window !== 'undefined') {
    window.addEventListener('load', function() {
        console.log('Window loaded. Leaflet available:', typeof L !== 'undefined');
        console.log('VectorGrid available:', typeof L !== 'undefined' && typeof L.vectorGrid !== 'undefined');
    });
}
</script>

<div class="big-map-container">
    <button id="floating-sidebar-toggle" class="floating-sidebar-toggle" title="<?php _e('Show listings', 'geotour'); ?>">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
        </svg>
    </button>

    <div id="map-loading-indicator" class="map-loading-indicator">
        <span class="loading-text"><?php _e('Loading map', 'geotour'); ?></span>
        <div class="dots-container">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>

    <div id="map-loading-overlay" class="map-loading-overlay hidden">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p><?php _e('Loading map data...', 'geotour'); ?></p>
        </div>
    </div>

    <div id="map-sidebar" class="map-sidebar">
        <div id="sidebar-loading-indicator" class="sidebar-loading-indicator">
            <div class="progress-bar"></div>
        </div>
        
        <div class="sidebar-header">
            <div class="header-nav-controls">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="home-link" title="<?php _e('Go to Homepage', 'geotour'); ?>">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z"/>
                    </svg>
                </a>
            </div>
            <h1><?php _e('Discover Crete', 'geotour'); ?></h1>
            <button id="sidebar-toggle" class="sidebar-toggle" aria-label="<?php _e('Hide sidebar', 'geotour'); ?>">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                </svg>
            </button>
        </div>
        
        <div class="sidebar-results">
            <?php 
            $active_filters = array();
            if (!empty($_GET['listing-category'])) {
                $term = get_term_by('slug', sanitize_text_field($_GET['listing-category']), 'listing-category');
                if ($term) $active_filters[] = array('type' => 'category', 'name' => $term->name, 'slug' => $term->slug);
            }
            if (!empty($_GET['listing-region'])) {
                $term = get_term_by('slug', sanitize_text_field($_GET['listing-region']), 'listing-region');
                if ($term) $active_filters[] = array('type' => 'region', 'name' => $term->name, 'slug' => $term->slug);
            }
            if (!empty($_GET['listing-tag'])) {
                $term = get_term_by('slug', sanitize_text_field($_GET['listing-tag']), 'listing-tag');
                if ($term) $active_filters[] = array('type' => 'tag', 'name' => $term->name, 'slug' => $term->slug);
            }
            $current_search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
            ?>
            <div class="active-filters">
                <div class="filters-header">
                    <span><?php _e('Filtered by:', 'geotour'); ?></span>
                    <a href="/listing" class="clear-filters"><?php _e('Clear all', 'geotour'); ?></a>
                </div>
                
                <div class="map-search-box">
                    <input type="text" id="map-search-input" placeholder="<?php _e('Search listings...', 'geotour'); ?>" value="<?php echo esc_attr($current_search); ?>">
                    <button type="button" id="map-search-apply" title="<?php _e('Apply search', 'geotour'); ?>">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                            <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/>
                        </svg>
                    </button>
                </div>
                
                <?php if (!empty($active_filters)) : ?>
                <div class="filter-tags">
                    <?php foreach ($active_filters as $filter) : ?>
                        <span class="filter-tag filter-<?php echo esc_attr($filter['type']); ?>">
                            <?php echo esc_html($filter['name']); ?>
                            <a href="<?php echo esc_url(remove_query_arg('listing-' . $filter['type'])); ?>" class="remove-filter">&times;</a>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="results-header">
                <span id="results-count"><?php _e('Loading...', 'geotour'); ?></span>
            </div>
            <div id="listings-container" class="listings-container">
                </div>
            
            <div id="sidebar-loading-overlay" class="sidebar-loading-overlay">
                <div class="sidebar-spinner"></div>
            </div>
        </div>
    </div>
    
    <div id="big-map" class="big-map">
        </div>
    
    <div class="map-controls">
        <button id="locate-me" class="map-control-btn" title="<?php _e('Find my location', 'geotour'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8M3.05,13H1V11H3.05C3.5,6.83 6.83,3.5 11,3.05V1H13V3.05C17.17,3.5 20.5,6.83 20.95,11H23V13H20.95C20.5,17.17 17.17,20.5 13,20.95V23H11V20.95C6.83,20.5 3.5,17.17 3.05,13M12,5A7,7 0 0,0 5,12A7,7 0 0,0 12,19A7,7 0 0,0 19,12A7,7 0 0,0 12,5Z"/>
            </svg>
        </button>
        
        <button id="fit-bounds" class="map-control-btn" title="<?php _e('Fit all markers', 'geotour'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M9,3V5H7V9H5V7H3V5A2,2 0 0,1 5,3H7A2,2 0 0,1 9,3M19,3A2,2 0 0,1 21,5V7H19V5H17V3H19M5,15H7V17H9V19H7A2,2 0 0,1 5,17V15M15,17H17V19H19V17H21V19A2,2 0 0,1 19,21H17A2,2 0 0,1 15,19V17Z"/>
            </svg>
        </button>
        
        <button id="reset-view" class="map-control-btn" title="<?php _e('Reset view', 'geotour'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12,4C14.1,4 16.1,4.8 17.6,6.3C20.7,9.4 20.7,14.5 17.6,17.6C15.8,19.5 13.3,20.2 10.9,19.9L11.4,17.9C13.1,18.1 14.9,17.5 16.2,16.2C18.5,13.9 18.5,10.1 16.2,7.7C15.1,6.6 13.5,6.1 12,6.1V10.1L7,5.1L12,0.1V4M6.3,17.6C3.7,15 3.3,11 5.1,7.9L6.6,9.4C5.5,11.6 5.9,14.4 7.8,16.2C8.3,16.7 8.9,17.1 9.6,17.4L9,19.4C8,19 7.1,18.4 6.3,17.6Z"/>
            </svg>
        </button>

        <button id="open-3d-map" class="map-control-btn" title="<?php _e('Open 3D Map', 'geotour'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M21,16.5C21,16.88 20.79,17.21 20.47,17.38L12.57,21.82C12.41,21.94 12.21,22 12,22C11.79,22 11.59,21.94 11.43,21.82L3.53,17.38C3.21,17.21 3,16.88 3,16.5V7.5C3,7.12 3.21,6.79 3.53,6.62L11.43,2.18C11.59,2.06 11.79,2 12,2C12.21,2 12.41,2.06 12.57,2.18L20.47,6.62C20.79,6.79 21,7.12 21,7.5V16.5M12,4.15L6.04,7.5L12,10.85L17.96,7.5L12,4.15M5,15.91L11,19.29V12.58L5,9.21V15.91M19,15.91V9.21L13,12.58V19.29L19,15.91Z" />
            </svg>
        </button>
    </div>
</div>

<!-- Route Planner Toolbar - Positioned outside the main container -->
<div id="route-planner-toolbar" class="route-planner-toolbar hidden">
    <div class="toolbar-header">
        <?php _e('Route Planner', 'geotour'); ?>
    </div>
    
    <div class="toolbar-actions">
        <!-- NEW: Add Zoom to Route button -->
        <button id="zoom-to-route" class="toolbar-btn" title="<?php _e('Zoom to Route', 'geotour'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M15.5,14L20.5,19L19,20.5L14,15.5V14.71L13.73,14.43C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.43,13.73L14.71,14H15.5M9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14M12,10H10V12H9V10H7V9H9V7H10V9H12V10Z"/>
            </svg>
        </button>
        
        <button id="preview-route" class="toolbar-btn" title="<?php _e('Preview Route on Map', 'geotour'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z"/>
            </svg>
        </button>
        
        <button id="export-google-maps" class="toolbar-btn" title="<?php _e('Export to Google Maps', 'geotour'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z"/>
            </svg>
        </button>
        
        <button id="export-geojson" class="toolbar-btn" title="<?php _e('Export to GeoJSON', 'geotour'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
            </svg>
        </button>
        
        <button id="copy-shareable-link" class="toolbar-btn" title="<?php _e('Copy Shareable Link', 'geotour'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M3.9,12C3.9,10.29 5.29,8.9 7,8.9H11V7H7A5,5 0 0,0 2,12A5,5 0 0,0 7,17H11V15.1H7C5.29,15.1 3.9,13.71 3.9,12M8,13H16V11H8V13M17,7H13V8.9H17C18.71,8.9 20.1,10.29 20.1,12C20.1,13.71 18.71,15.1 17,15.1H13V17H17A5,5 0 0,0 22,12A5,5 0 0,0 17,7Z"/>
            </svg>
        </button>
        
        <button id="clear-route" class="toolbar-btn clear-btn" title="<?php _e('Clear Route', 'geotour'); ?>">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z"/>
            </svg>
        </button>
    </div>
    
    <div class="toolbar-info">
        <div class="route-stats">
            <span id="route-count"><?php _e('0 stops selected', 'geotour'); ?></span>
        </div>
        <div id="route-preview-info" class="route-preview-info hidden">
            <!-- Route preview information will be displayed here -->
        </div>
    </div>
</div><script>
// Pass PHP data to JavaScript
window.geotourBigMap = {
    // 1. UPDATED API URL
    apiUrl: '<?php echo esc_js(rest_url('geotour/v3/spatial-info')); ?>',
    nonce: '<?php echo wp_create_nonce('wp_rest'); ?>',
    defaultCenter: [35.2401, 24.8093], // Heraklion, Crete
    defaultZoom: 10,
    defaultIconUrl: 'data:image/svg+xml;base64,<?php echo base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#3b82f6"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>'); ?>',
    // 2. UPDATED URL PARAMS (using new v3 keys)
    urlParams: {
        category: '<?php echo esc_js(isset($_GET['listing-category']) ? sanitize_text_field($_GET['listing-category']) : ''); ?>',
        region: '<?php echo esc_js(isset($_GET['listing-region']) ? sanitize_text_field($_GET['listing-region']) : ''); ?>',
        tag: '<?php echo esc_js(isset($_GET['listing-tag']) ? sanitize_text_field($_GET['listing-tag']) : ''); ?>',
        search: '<?php echo esc_js(isset($_GET['search']) ? sanitize_text_field($_GET['search']) : ''); ?>',
        acffield: '<?php echo esc_js(isset($_GET['acffield']) ? sanitize_text_field($_GET['acffield']) : ''); ?>',
        route_listings: '<?php echo esc_js(isset($_GET['route_listings']) ? sanitize_text_field($_GET['route_listings']) : ''); ?>'
    },
    strings: {
        loadingError: '<?php _e('Error loading map data. Please try again.', 'geotour'); ?>',
        noResults: '<?php _e('No listings found in this area.', 'geotour'); ?>',
        resultsFound: '<?php _e('{count} listings found', 'geotour'); ?>',
        loadingListings: '<?php _e('Loading listings...', 'geotour'); ?>',
        filteredBy: '<?php _e('Filtered by:', 'geotour'); ?>',
        clearFilters: '<?php _e('Clear filters', 'geotour'); ?>',
        searchPlaceholder: '<?php _e('Search listings...', 'geotour'); ?>',
        applySearch: '<?php _e('Apply search', 'geotour'); ?>',
        clearSearch: '<?php _e('Clear search', 'geotour'); ?>'
    }
};
</script>

<?php get_footer(); ?>