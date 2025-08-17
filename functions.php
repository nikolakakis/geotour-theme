<?php
/**
 * Geotour Mobile First Theme Functions
 * 
 * Main functions file - acts as a loader for organized includes
 * 
 * @package Geotour_Mobile_First
 * @version 1.4.4.1
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

// --- DEBUGGING THEME TRANSLATION NOTICE ---
// The "Translation loading ... triggered too early" notice you are seeing on the /blog page
// is caused by calling a translation function (like __() or _e()) in a file before
// WordPress has loaded the theme's text domain (which happens at the 'init' action).
//
// The most common causes are:
// 1. In CPT or Taxonomy registration files: Using __() for labels outside of the 'init' hook.
// 2. In shortcode files: Using __() for a default attribute value in shortcode_atts().
//
// The error is not in your template files (like single.php or page.php), but in one of the
// files included below. Please check 'listing.php' and 'legacy-shortcodes.php' for this pattern.
//
// EXAMPLE of what to find and fix in a shortcode file (e.g., legacy-shortcodes.php):
//
// WRONG:
// function my_shortcode_handler( $atts ) {
//     $atts = shortcode_atts( array(
//         'title' => __( 'Default Title', 'geotour' ), // This runs when the file is loaded, which is too early.
//     ), $atts );
//     return '<h2>' . esc_html( $atts['title'] ) . '</h2>';
// }
//
// CORRECT:
// function my_shortcode_handler( $atts ) {
//     $atts = shortcode_atts( array(
//         'title' => '', // Use an empty string for the default.
//     ), $atts );
//
//     // Set the title and translate it here, inside the function body. This runs at the correct time.
//     $title = ! empty( $atts['title'] ) ? $atts['title'] : __( 'Default Title', 'geotour' );
//
//     return '<h2>' . esc_html( $title ) . '</h2>';
// }
// --- END DEBUGGING INFO ---

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

/**
 * Add ACF fields for sidebar control
 */
function geotour_add_sidebar_acf_fields() {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group(array(
            'key' => 'group_sidebar_control',
            'title' => 'Layout Options',
            'fields' => array(
                array(
                    'key' => 'field_hide_sidebar',
                    'label' => 'Hide Sidebar',
                    'name' => 'hide_sidebar',
                    'type' => 'true_false',
                    'instructions' => 'Check this to hide the sidebar and use full-width layout',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => 'Hidden',
                    'ui_off_text' => 'Visible',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'listing',
                    ),
                ),
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ),
                ),
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'side',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ));
    }
}
add_action('acf/init', 'geotour_add_sidebar_acf_fields');

/**
 * Build hierarchical options for select dropdowns
 * Handles parent-child relationships and exclusions
 */
function geotour_build_hierarchical_options($terms, $parent_id = 0, $level = 0, $excluded_slugs = array()) {
    $output = '';
    
    if (empty($terms)) {
        return $output;
    }
    
    // Get all excluded term IDs (including children)
    $excluded_ids = array();
    if (!empty($excluded_slugs)) {
        foreach ($excluded_slugs as $slug) {
            $term = get_term_by('slug', $slug, isset($terms[0]) ? $terms[0]->taxonomy : 'listing-category');
            if ($term && !is_wp_error($term)) {
                $excluded_ids[] = $term->term_id;
                // Get all children of excluded terms
                $children = get_term_children($term->term_id, $term->taxonomy);
                if (!empty($children) && !is_wp_error($children)) {
                    $excluded_ids = array_merge($excluded_ids, $children);
                }
            }
        }
    }
    
    foreach ($terms as $term) {
        // Skip if term is excluded
        if (in_array($term->term_id, $excluded_ids)) {
            continue;
        }
        
        // Only process terms with the correct parent
        if ($term->parent == $parent_id) {
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
            $output .= '<option value="' . esc_attr($term->slug) . '">' . $indent . esc_html($term->name) . '</option>';
            
            // Recursively get children
            $children_output = geotour_build_hierarchical_options($terms, $term->term_id, $level + 1, $excluded_slugs);
            $output .= $children_output;
        }
    }
    
    return $output;
}

/**
 * Prints HTML with meta information for the current post-date/time.
 */
if (!function_exists('geotour_posted_on')) :
    function geotour_posted_on() {
        $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
        if (get_the_time('U') !== get_the_modified_time('U')) {
            $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
        }

        $time_string = sprintf(
            $time_string,
            esc_attr(get_the_date(DATE_W3C)),
            esc_html(get_the_date()),
            esc_attr(get_the_modified_date(DATE_W3C)),
            esc_html(get_the_modified_date())
        );

        $posted_on = sprintf(
            esc_html_x('Posted on %s', 'post date', 'geotour'),
            '<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . $time_string . '</a>'
        );

        echo '<span class="posted-on">' . $posted_on . '</span>';
    }
endif;

/**
 * Prints HTML with meta information for the current post-date/time.
 * Simplified to only show creation date.
 */
function geotour_posted_by() {
    $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
    
    $time_string = sprintf(
        $time_string,
        esc_attr(get_the_date(DATE_W3C)),
        esc_html(get_the_date())
    );

    $posted_on = sprintf(
        /* translators: %s: post date. */
        esc_html_x('Posted on %s', 'post date', 'geotour'),
        '<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . $time_string . '</a>'
    );

    echo '<span class="posted-on">' . $posted_on . '</span>';
}

/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
if (!function_exists('geotour_entry_footer')) :
    function geotour_entry_footer() {
        // Only show categories/tags in footer if not already shown in header/meta
        if ('post' === get_post_type()) {
            // Only show tags in footer, not categories (to avoid duplicate "Posted in ...")
            $tags_list = get_the_tag_list('', esc_html_x(', ', 'list item separator', 'geotour'));
            if ($tags_list) {
                printf('<span class="tags-links">' . esc_html__('Tagged %1$s', 'geotour') . '</span>', $tags_list); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }
        edit_post_link(
            sprintf(
                wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers */
                    __('Edit <span class="screen-reader-text">%s</span>', 'geotour'),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                get_the_title()
            ),
            '<span class="edit-link">',
            '</span>'
        );
    }
endif;

/**
 * Enqueue Google Fonts correctly for performance.
 */
function geotour_enqueue_google_fonts() {
    // Preconnect to the font server.
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    
    // Enqueue the fonts stylesheet asynchronously.
    echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;700&family=Syne:wght@700;800&display=swap">';
}
add_action('wp_head', 'geotour_enqueue_google_fonts');

// Include shortcodes
require_once get_template_directory() . '/inc/shortcodes/map-controls-popup.php';