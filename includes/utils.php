<?php
/**
 * Utility Functions
 * 
 * @package Geotour_Mobile_First
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if current page should display maps
 * 
 * @return bool
 */
function geotour_is_map_page() {
    return (
        is_post_type_archive('listing') ||
        is_tax('listing-category') ||
        is_tax('listing-region') ||
        is_tax('listing-tag') ||
        is_singular('listing') ||
        is_page_template('templates/map-page.php')
    );
}

/**
 * Get listing coordinates
 * 
 * @param int $post_id
 * @return array|false
 */
function geotour_get_listing_coordinates($post_id) {
    $lat = get_post_meta($post_id, 'latitude', true);
    $lng = get_post_meta($post_id, 'longitude', true);
    
    if ($lat && $lng) {
        return array(
            'lat' => floatval($lat),
            'lng' => floatval($lng)
        );
    }
    
    return false;
}

/**
 * Generate GeoJSON for listings
 * 
 * @param WP_Query $query
 * @return string JSON
 */
function geotour_generate_geojson($query) {
    $features = array();
    
    while ($query->have_posts()) {
        $query->the_post();
        $coordinates = geotour_get_listing_coordinates(get_the_ID());
        
        if ($coordinates) {
            $features[] = array(
                'type' => 'Feature',
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array($coordinates['lng'], $coordinates['lat'])
                ),
                'properties' => array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'iconUrl' => geotour_get_listing_icon_url(get_the_ID())
                )
            );
        }
    }
    
    wp_reset_postdata();
    
    return json_encode(array(
        'type' => 'FeatureCollection',
        'features' => $features
    ));
}