<?php
/**
 * Redirect taxonomy archive pages to listing map with filters
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Redirect listing taxonomy archives to the big map with filters
 */
function geotour_redirect_taxonomy_to_listing_map() {
    if (is_tax(['listing-category', 'listing-region', 'listing-tag'])) {
        $queried_object = get_queried_object();
        
        if ($queried_object) {
            $taxonomy = $queried_object->taxonomy;
            $slug = $queried_object->slug;
            
            // Map taxonomy name to URL parameter
            $param_map = [
                'listing-category' => 'listing-category',
                'listing-region' => 'listing-region', 
                'listing-tag' => 'listing-tag'
            ];
            
            if (isset($param_map[$taxonomy])) {
                $listing_url = home_url('/listing/?' . $param_map[$taxonomy] . '=' . $slug);
                wp_redirect($listing_url, 301);
                exit;
            }
        }
    }
}
add_action('template_redirect', 'geotour_redirect_taxonomy_to_listing_map');

/**
 * Generate listing map filter URL for taxonomy terms
 */
function geotour_get_taxonomy_listing_url($taxonomy, $slug) {
    $param_map = [
        'listing-category' => 'listing-category',
        'listing-region' => 'listing-region',
        'listing-tag' => 'listing-tag'
    ];
    
    if (isset($param_map[$taxonomy])) {
        return home_url('/listing/?' . $param_map[$taxonomy] . '=' . $slug);
    }
    
    return home_url('/listing/');
}

/**
 * Filter taxonomy term links to point to listing map
 */
function geotour_filter_taxonomy_term_links($url, $term, $taxonomy) {
    if (in_array($taxonomy, ['listing-category', 'listing-region', 'listing-tag'])) {
        return geotour_get_taxonomy_listing_url($taxonomy, $term->slug);
    }
    return $url;
}
add_filter('term_link', 'geotour_filter_taxonomy_term_links', 10, 3);

/**
 * Add custom body class for filtered listing pages
 */
function geotour_add_listing_filter_body_class($classes) {
    if (is_page_template('page-listing.php') || 
        (isset($_GET['geotour_listing_map']) && $_GET['geotour_listing_map'] == '1')) {
        
        $classes[] = 'listing-map-page';
        
        // Add filter-specific classes
        if (!empty($_GET['listing-category'])) {
            $classes[] = 'filtered-by-category';
        }
        if (!empty($_GET['listing-region'])) {
            $classes[] = 'filtered-by-region';
        }
        if (!empty($_GET['listing-tag'])) {
            $classes[] = 'filtered-by-tag';
        }
    }
    return $classes;
}
add_filter('body_class', 'geotour_add_listing_filter_body_class');