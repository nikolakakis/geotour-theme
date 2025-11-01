<?php
/**
 * Redirect taxonomy archive pages to listing map with filters
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Redirect listing taxonomy archives to the listings list page with filters
 */
function geotour_redirect_taxonomy_to_listing_map() {
    if (is_tax(['listing-category', 'listing-region', 'listing-tag'])) {
        $queried_object = get_queried_object();
        
        if ($queried_object) {
            $taxonomy = $queried_object->taxonomy;
            $slug = $queried_object->slug;
            
            // Map taxonomy name to URL parameter
            $param_map = [
                'listing-category' => 'listing_category',
                'listing-region' => 'listing_region', 
                'listing-tag' => 'listing_tag'
            ];
            
            if (isset($param_map[$taxonomy])) {
                $listing_url = home_url('/listings-list/?' . $param_map[$taxonomy] . '=' . $slug);
                wp_redirect($listing_url, 301);
                exit;
            }
        }
    }
}
add_action('template_redirect', 'geotour_redirect_taxonomy_to_listing_map');

/**
 * Redirect simple listings to the listings-list page with highlighting
 */
function geotour_redirect_simple_listings() {
    // Only run on single listing pages
    if (!is_singular('listing')) {
        return;
    }
    
    $post_id = get_the_ID();
    
    // Check if this listing has listing-content-type = simple
    $content_types = get_the_terms($post_id, 'listing-content-type');
    
    if ($content_types && !is_wp_error($content_types)) {
        foreach ($content_types as $content_type) {
            if ($content_type->slug === 'simple') {
                // This is a simple listing, redirect to listings-list with highlight
                $listings_list_url = home_url('/listings-list/?highlight_post=' . $post_id);
                wp_redirect($listings_list_url, 302); // 302 temporary redirect
                exit;
            }
        }
    }
}
add_action('template_redirect', 'geotour_redirect_simple_listings', 5); // Priority 5 to run before other redirects

/**
 * Generate listings list filter URL for taxonomy terms
 */
function geotour_get_taxonomy_listing_url($taxonomy, $slug) {
    $param_map = [
        'listing-category' => 'listing_category',
        'listing-region' => 'listing_region',
        'listing-tag' => 'listing_tag'
    ];
    
    if (isset($param_map[$taxonomy])) {
        return home_url('/listings-list/?' . $param_map[$taxonomy] . '=' . $slug);
    }
    
    return home_url('/listings-list/');
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