<?php
/**
 * Custom URL handler for /listing endpoint
 */
function geotour_listing_map_rewrite_rules() {
    add_rewrite_rule('^listing/?$', 'index.php?geotour_listing_map=1', 'top');
}
add_action('init', 'geotour_listing_map_rewrite_rules');

/**
 * Add custom query var
 */
function geotour_listing_map_query_vars($vars) {
    $vars[] = 'geotour_listing_map';
    return $vars;
}
add_filter('query_vars', 'geotour_listing_map_query_vars');

/**
 * Template redirect for listing map
 */
function geotour_listing_map_template_redirect() {
    if (get_query_var('geotour_listing_map')) {
        include(get_template_directory() . '/page-listing.php');
        exit;
    }
}
add_action('template_redirect', 'geotour_listing_map_template_redirect');