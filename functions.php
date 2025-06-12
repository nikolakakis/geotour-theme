<?php
/**
 * Geotour Mobile First Theme Functions
 * 
 * Main functions file - acts as a loader for organized includes
 * 
 * @package Geotour_Mobile_First
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('GEOTOUR_THEME_VERSION', '1.0.0');
define('GEOTOUR_THEME_DIR', get_template_directory());
define('GEOTOUR_THEME_URI', get_template_directory_uri());

// Core theme setup
require_once GEOTOUR_THEME_DIR . '/includes/theme-support.php';

// Theme utilities and helpers (MUST be included BEFORE scripts-styles.php)
require_once GEOTOUR_THEME_DIR . '/includes/utils.php';

// Scripts and styles management
require_once GEOTOUR_THEME_DIR . '/includes/scripts-styles.php';

// Navigation specific helpers (like custom walkers)
require_once GEOTOUR_THEME_DIR . '/includes/navigation.php';

// Custom post types and taxonomies
require_once GEOTOUR_THEME_DIR . '/includes/custom-post-types/listing.php';
// require_once GEOTOUR_THEME_DIR . '/includes/taxonomies/listing-taxonomies.php';

// Legacy shortcode replacements
require_once GEOTOUR_THEME_DIR . '/includes/legacy-shortcodes.php';

// Modern shortcode implementations
require_once GEOTOUR_THEME_DIR . '/includes/shortcodes/content-gallery.php';

// Map functionality
require_once GEOTOUR_THEME_DIR . '/includes/maps/icon-management.php';

// REST API endpoints
require_once GEOTOUR_THEME_DIR . '/includes/api/spatial-info-v2.php';

// Listing map routes
require_once GEOTOUR_THEME_DIR . '/includes/listing-map-routes.php';

// Listing taxonomy redirects
require_once GEOTOUR_THEME_DIR . '/includes/listing-taxonomy-redirects.php';

// Custom hooks and filters (Uncomment when ready)
// require_once GEOTOUR_THEME_DIR . '/includes/hooks.php'; // Note: your file is named hook.php, consider renaming to hooks.php for consistency or update here

// Admin customizations (Uncomment when ready)
// require_once GEOTOUR_THEME_DIR . '/includes/admin/admin-init.php';

// API endpoints for AJAX and custom functionality (Uncomment when ready)
// require_once GEOTOUR_THEME_DIR . '/includes/api/api-init.php';

/**
 * Temporary inclusion of the data migration tools.
 * REMOVE THIS AND THE /migration FOLDER AFTER MIGRATION IS COMPLETE.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once get_template_directory() . '/migration/cli-command.php';
}

add_action('after_setup_theme', function() {
    register_nav_menus([
        'primary' => __('Primary Menu', 'geotour-theme'),
    ]);
});

/**
 * Disable comments and trackbacks completely
 */
function geotour_disable_comments_completely() {
    // Disable support for comments and trackbacks in post types
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
}
add_action('admin_init', 'geotour_disable_comments_completely');

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove comments page in menu
add_action('admin_menu', function() {
    remove_menu_page('edit-comments.php');
});

// Remove comments links from admin bar
add_action('init', function() {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});

// Remove comments metabox from dashboard
add_action('admin_init', function() {
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
});

// Remove comments from REST API
add_filter('rest_endpoints', function($endpoints) {
    if (isset($endpoints['/wp/v2/comments'])) {
        unset($endpoints['/wp/v2/comments']);
    }
    if (isset($endpoints['/wp/v2/comments/(?P<id>[\d]+)'])) {
        unset($endpoints['/wp/v2/comments/(?P<id>[\d]+)']);
    }
    return $endpoints;
});