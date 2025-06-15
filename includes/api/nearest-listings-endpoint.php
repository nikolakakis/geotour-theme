<?php
/**
 * Nearest Listings REST API Endpoint
 * Finds nearest listings based on coordinates using ACF position field
 * 
 * @package Geotour_Mobile_First
 */

add_action('rest_api_init', 'nearest_listings_rest_v2');

function nearest_listings_rest_v2() {
    register_rest_route("panotours/v1","nearest",array(
        'methods' => WP_REST_SERVER::READABLE,
        'callback' => 'nearestlistingsSearch_v2' // Renamed callback
    ));    
}

function nearestlistingsSearch_v2($data) {
    // Get post ID from request
    $post_id = isset($data['postid']) ? intval($data['postid']) : 0; 

    // Get coordinates (from post ID or query parameters)
    if ($post_id > 0) {
        $position_acf = get_field('position', $post_id); // Get ACF position field
        if ($position_acf && isset($position_acf['markers']) && is_array($position_acf['markers']) && !empty($position_acf['markers'])) {
            $first_marker = $position_acf['markers'][0];
            if (isset($first_marker['lat']) && isset($first_marker['lng'])) {
                $lat = floatval($first_marker['lat']);
                $lon = floatval($first_marker['lng']);
            } else {
                return new WP_Error('no_location_found', 'No valid coordinates found for the given post ID.', array('status' => 400));
            }
        } else {
            return new WP_Error('no_location_found', 'No location found for the given post ID.', array('status' => 400));
        }
    } else {
        // Sanitize user input (latitude and longitude) from query parameters
        $lat = isset($data['lat']) ? floatval($data['lat']) : 35.033411; 
        $lon = isset($data['lon']) ? floatval($data['lon']) : 24.790291;
    }

    // Limit parameter
    $limit = isset($data['limit']) ? intval($data['limit']) : 12;

    // Category filtering
    $categories = isset($data['category']) ? sanitize_text_field($data['category']) : '';
    $category_filter = array();
    
    if (!empty($categories)) {
        $category_array = explode(',', $categories);
        $category_filter = array(
            'taxonomy' => 'listing-category',
            'field'    => 'slug',
            'terms'    => $category_array,
        );
    }

    // Base query arguments
    $query_args = array(
        'post_type'      => 'listing',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // Get all posts, we'll sort by distance
        'meta_query'     => array(
            array(
                'key'     => 'position',
                'compare' => 'EXISTS'
            )
        )
    );

    // Add category filter if specified
    if (!empty($category_filter)) {
        $query_args['tax_query'] = array($category_filter);
    }

    // Exclude the current post if post_id is provided
    if ($post_id > 0) {
        $query_args['post__not_in'] = array($post_id);
    }

    $listings_query = new WP_Query($query_args);
    $listings_with_distance = array();

    while ($listings_query->have_posts()) {
        $listings_query->the_post();
        $theid = get_the_ID();

        // Get ACF position field for each listing
        $listing_position = get_field('position', $theid);
        
        if ($listing_position && isset($listing_position['markers']) && is_array($listing_position['markers']) && !empty($listing_position['markers'])) {
            $listing_marker = $listing_position['markers'][0];
            if (isset($listing_marker['lat']) && isset($listing_marker['lng'])) {
                $listing_lat = floatval($listing_marker['lat']);
                $listing_lon = floatval($listing_marker['lng']);

                // Calculate distance using haversine formula
                $distance = calculateDistance($lat, $lon, $listing_lat, $listing_lon);

                // Get the featured image URL (with fallback)
                $thumb_id = get_post_thumbnail_id($theid);
                if ($thumb_id) {
                    $thumb_url = wp_get_attachment_image_src($thumb_id, 'thumbnail');
                    $thumb_url = $thumb_url ? $thumb_url[0] : ''; 
                } else {
                    $thumb_url = 'https://www.geotour.gr/wp-content/uploads/2024/04/no-image-bello-160x160.jpg'; // Fallback image
                }

                // Get listing categories and icons
                $listing_categories = get_the_terms($theid, 'listing-category');
                $category_data = array();
                $icon_url = false; 

                if ($listing_categories && !is_wp_error($listing_categories)) {
                    foreach ($listing_categories as $category) {
                        // Try ACF field first, fallback to old meta
                        $temp_icon_url = get_field('icon', 'listing-category_' . $category->term_id);
                        if (!$temp_icon_url) {
                            $icon_id = get_term_meta($category->term_id, 'showcase-taxonomy-selected-image-id', true);
                            $temp_icon_url = wp_get_attachment_image_url($icon_id, 'full');
                        }

                        if ($temp_icon_url && !$icon_url) {
                            $icon_url = $temp_icon_url; // Use the first valid icon URL
                        }

                        $category_data[] = array(
                            'id' => $category->term_id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                            'icon_url' => $temp_icon_url,
                        );
                    }
                }

                // Get metabox fields (for transitional period)
                $metabox_fields = get_post_meta($theid);

                // Calculate bearing
                $bearing = calculateBearing($lat, $lon, $listing_lat, $listing_lon);
                $bearing_code = getBearingCode($bearing);

                // Get Yoast SEO data
                $yoast_metadesc = get_post_meta($theid, '_yoast_wpseo_metadesc', true);
                $yoast_focuskw = get_post_meta($theid, '_yoast_wpseo_focuskw', true);
                $yoast_title = get_post_meta($theid, '_yoast_wpseo_title', true);

                $listings_with_distance[] = array(
                    'distance' => $distance,
                    'data' => array(
                        'listingID' => $theid,
                        'title' => html_entity_decode(get_the_title($theid)),
                        'url' => get_the_permalink($theid),
                        'distance' => number_format($distance, 2),
                        'latitude' => $listing_lat,
                        'longitude' => $listing_lon, 
                        'bearing' => $bearing,
                        'bearing_code' => $bearing_code,
                        'featured_image_url' => $thumb_url,            
                        'listing_categories' => $category_data,
                        'icon_url' => $icon_url,
                        'metadesc' => $yoast_metadesc,
                        'focuskw' => $yoast_focuskw,            
                        'listingproperties' => $metabox_fields,            
                    )
                );
            }
        }
    }

    wp_reset_postdata();

    // Sort by distance
    usort($listings_with_distance, function($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });

    // Apply limit and extract data
    $listingsapiarray = array();
    $count = 0;
    foreach ($listings_with_distance as $item) {
        if ($limit > 0 && $count >= $limit) {
            break;
        }
        $listingsapiarray[] = $item['data'];
        $count++;
    }

    return $listingsapiarray;
}

// Function to calculate distance using haversine formula
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // Earth's radius in kilometers

    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    $dlat = $lat2 - $lat1;
    $dlon = $lon2 - $lon1;

    $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earth_radius * $c;

    return $distance;
}

// Function to calculate bearing
function calculateBearing($lat1, $lon1, $lat2, $lon2) {
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    $dLon = $lon2 - $lon1;

    $y = sin($dLon) * cos($lat2);
    $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);

    $brng = atan2($y, $x);
    $brng = rad2deg($brng);
    $brng = fmod(($brng + 360), 360);

    return round($brng); // Round to the nearest degree
}

// Function to get bearing code
function getBearingCode($bearing) {
    $directions = ["N", "NE", "E", "SE", "S", "SW", "W", "NW", "N"]; 
    $index = round($bearing / 45); 
    return $directions[$index];
}