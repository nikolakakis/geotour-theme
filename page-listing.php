<?php
/**
 * Template Name: Listing Map
 * Template for the Listing Map UI
 * Full-screen map with sidebar for spatial navigation
 *
 * @package Geotour_Mobile_First
 */

get_header(); ?>

<div class="big-map-container">
    <!-- Floating Sidebar Toggle Button -->
    <button id="floating-sidebar-toggle" class="floating-sidebar-toggle" title="<?php _e('Show listings', 'geotour'); ?>">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
        </svg>
    </button>

    <!-- NEW: Non-blocking Map Loading Indicator -->
    <div id="map-loading-indicator" class="map-loading-indicator">
        <span class="loading-text"><?php _e('Loading map', 'geotour'); ?></span>
        <div class="dots-container">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>

    <!-- Old Map Loading Overlay (kept for backward compatibility) -->
    <div id="map-loading-overlay" class="map-loading-overlay hidden">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p><?php _e('Loading map data...', 'geotour'); ?></p>
        </div>
    </div>

    <!-- Sidebar for listings -->
    <div id="map-sidebar" class="map-sidebar">
        <!-- NEW: Non-blocking Sidebar Loading Indicator -->
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
            <h2><?php _e('Discover Crete', 'geotour'); ?></h2>
            <button id="sidebar-toggle" class="sidebar-toggle" aria-label="<?php _e('Hide sidebar', 'geotour'); ?>">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                </svg>
            </button>
        </div>
        
        <!-- Results -->
        <div class="sidebar-results">
            <!-- Active Filters Display -->
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
            
            if (!empty($active_filters)) : ?>
            <div class="active-filters">
                <div class="filters-header">
                    <span><?php _e('Filtered by:', 'geotour'); ?></span>
                    <a href="/listing" class="clear-filters"><?php _e('Clear all', 'geotour'); ?></a>
                </div>
                <div class="filter-tags">
                    <?php foreach ($active_filters as $filter) : ?>
                        <span class="filter-tag filter-<?php echo esc_attr($filter['type']); ?>">
                            <?php echo esc_html($filter['name']); ?>
                            <a href="<?php echo esc_url(remove_query_arg('listing-' . $filter['type'])); ?>" class="remove-filter">&times;</a>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="results-header">
                <span id="results-count"><?php _e('Loading...', 'geotour'); ?></span>
            </div>
            <div id="listings-container" class="listings-container">
                <!-- Listings will be loaded via AJAX -->
            </div>
            
            <!-- Old Sidebar Loading Overlay (kept for backward compatibility) -->
            <div id="sidebar-loading-overlay" class="sidebar-loading-overlay">
                <div class="sidebar-spinner"></div>
            </div>
        </div>
    </div>
    
    <!-- Map Container -->
    <div id="big-map" class="big-map">
        <!-- Map will be initialized here -->
    </div>
    
    <!-- Map Controls -->
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
    </div>
</div>

<script>
// Pass PHP data to JavaScript
window.geotourBigMap = {
    apiUrl: '<?php echo esc_js(rest_url('geotour/v2/spatial-info')); ?>',
    nonce: '<?php echo wp_create_nonce('wp_rest'); ?>',
    defaultCenter: [35.2401, 24.8093], // Heraklion, Crete
    defaultZoom: 10,
    defaultIconUrl: 'data:image/svg+xml;base64,<?php echo base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#3b82f6"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>'); ?>',
    // URL parameters for filtering
    urlParams: {
        category: '<?php echo esc_js(isset($_GET['listing-category']) ? sanitize_text_field($_GET['listing-category']) : ''); ?>',
        region: '<?php echo esc_js(isset($_GET['listing-region']) ? sanitize_text_field($_GET['listing-region']) : ''); ?>',
        tag: '<?php echo esc_js(isset($_GET['listing-tag']) ? sanitize_text_field($_GET['listing-tag']) : ''); ?>',
        search: '<?php echo esc_js(isset($_GET['search']) ? sanitize_text_field($_GET['search']) : ''); ?>'
    },
    strings: {
        loadingError: '<?php _e('Error loading map data. Please try again.', 'geotour'); ?>',
        noResults: '<?php _e('No listings found in this area.', 'geotour'); ?>',
        resultsFound: '<?php _e('{count} listings found', 'geotour'); ?>',
        loadingListings: '<?php _e('Loading listings...', 'geotour'); ?>',
        filteredBy: '<?php _e('Filtered by:', 'geotour'); ?>',
        clearFilters: '<?php _e('Clear filters', 'geotour'); ?>'
    }
};
</script>

<?php get_footer(); ?>