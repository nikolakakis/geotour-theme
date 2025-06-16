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
require_once GEOTOUR_THEME_DIR . '/includes/api/custom-listing-endpoints.php'; // Added this line
require_once GEOTOUR_THEME_DIR . '/includes/api/nearest-listings-endpoint.php'; // Nearest listings with ACF position support

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

/**
 * Add listing coordinates to the body tag as data attributes on single listing pages.
 * This allows client-side JavaScript to access coordinates without extra AJAX calls.
 */
function geotour_add_listing_coordinates_to_body() {
    if (is_singular('listing')) {
        $post_id = get_the_ID();
        if (!$post_id) {
            return;
        }

        $position_acf = get_field('position', $post_id);
        $lat = null;
        $lng = null;

        if ($position_acf && isset($position_acf['markers']) && is_array($position_acf['markers']) && !empty($position_acf['markers'])) {
            $first_marker = $position_acf['markers'][0];
            if (isset($first_marker['lat']) && isset($first_marker['lng'])) {
                $lat = esc_attr($first_marker['lat']);
                $lng = esc_attr($first_marker['lng']);
            }
        }

        if ($lat !== null && $lng !== null) {
            // Replace 'geotour-main-js' with your actual main theme script handle if different.
            // If you don't have a specific theme script handle, you could use a common one like 'jquery',
            // but it's generally better to attach to your theme's main script.
            $script_handle = 'geotour-main-js'; // IMPORTANT: Verify this script handle
            
            // Check if the handle is already registered to avoid errors if it's not.
            // A more robust solution would be to ensure 'geotour-main-js' is always enqueued on single listing pages.
            // For now, we'll add a simple check.
            if(wp_script_is($script_handle, 'registered') || wp_script_is($script_handle, 'enqueued')) {
                 $js_code = "
document.addEventListener('DOMContentLoaded', function() {
    if (document.body) {
        document.body.setAttribute('data-listing-lat', '{$lat}');
        document.body.setAttribute('data-listing-lng', '{$lng}');
    }
});";
                wp_add_inline_script($script_handle, $js_code);
            } else {
                // Fallback: if the handle isn't found, try to output directly, though less ideal.
                // This part is a basic fallback and might need adjustment based on theme structure.
                // A better approach is to ensure the $script_handle is correct and always available.
                add_action('wp_footer', function() use ($lat, $lng) {
                    echo "<script type=\'text/javascript\'>\n";
                    echo "document.addEventListener('DOMContentLoaded', function() {\n";
                    echo "    if (document.body) {\n";
                    echo "        document.body.setAttribute('data-listing-lat', '{$lat}');\n";
                    echo "        document.body.setAttribute('data-listing-lng', '{$lng}');\n";
                    echo "    }\n";
                    echo "});\n";
                    echo "</script>\n";
                }, 99); // High priority to ensure it's late in the footer
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'geotour_add_listing_coordinates_to_body');


function enable_block_editor_for_my_cpt( $args, $post_type ) {
    // Replace 'your_cpt_slug' with the actual slug of your post type.
    if ( 'timeline' === $post_type ) {
        
        
        if (!in_array('editor', $args['supports'])) {
            $args['supports'][] = 'editor';
        }
    }
    return $args;
}
add_filter( 'register_post_type_args', 'enable_block_editor_for_my_cpt', 20, 2 );