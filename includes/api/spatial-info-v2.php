<?php
/**
 * GeoTour Spatial Info REST API Endpoint v2
 *
 * Provides a REST API endpoint to fetch listing spatial data with enhanced filtering capabilities.
 * This version focuses on the 'listing' custom post type with advanced taxonomy filtering.
 *
 * Endpoint URL: YOUR_SITE_URL/wp-json/geotour/v2/spatial-info
 *
 * Supported GET Parameters:
 *
 * - per_page (integer):
 *   Maximum number of items to return.
 *   Default: 500. Maximum: 999.  
 *   Example: /wp-json/geotour/v2/spatial-info?per_page=50
 *
 * - bbox (string):
 *   Filter by bounding box. Comma-separated string: minLng,minLat,maxLng,maxLat
 *   Coordinates must be numeric. Latitudes between -90 and 90. Longitudes between -180 and 180.
 *   Example: /wp-json/geotour/v2/spatial-info?bbox=24.0,35.0,25.0,35.5
 *
 * - listing_category (string):
 *   Filter by listing category slugs. Multiple values separated by commas.
 *   Example: /wp-json/geotour/v2/spatial-info?listing_category=archaeological-sites,museums
 *
 * - listing_region (string):
 *   Filter by listing region slugs. Multiple values separated by commas.
 *   Example: /wp-json/geotour/v2/spatial-info?listing_region=heraklion,chania
 *
 * - listing_tag (string):
 *   Filter by listing tag slugs. Multiple values separated by commas.
 *   Example: /wp-json/geotour/v2/spatial-info?listing_tag=family-friendly,accessible
 *
 * Combining Parameters:
 * All parameters can be combined.
 * Example:
 * /wp-json/geotour/v2/spatial-info?bbox=24.0,35.0,25.0,35.5&listing_category=museums&listing_region=heraklion&per_page=25
 *
 * Response Format:
 * Each item includes:
 * - Basic info: id, title, excerpt, coordinates
 * - Taxonomy data: categories, regions, tags
 * - Map icon URL based on primary category
 * - Permalink for navigation
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Registers the custom REST API endpoint v2 for GeoTour listing spatial data.
 */
function geotour_register_spatial_info_v2_route() {
    $namespace = 'geotour/v2';
    $route = '/spatial-info';

    register_rest_route(
        $namespace,
        $route,
        [
            [
                'methods' => WP_REST_Server::READABLE, // GET requests
                'callback' => 'geotour_get_spatial_info_v2',
                'permission_callback' => '__return_true', // Publicly accessible
                'args' => [
                    'per_page' => [
                        'description' => 'Maximum number of items to return.',
                        'type' => 'integer',
                        'default' => 500,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric($param) && $param > 0 && $param <= 999;
                        },
                    ],
                    'bbox' => [
                        'description' => __('Filter by bounding box. Comma-separated string: minLng,minLat,maxLng,maxLat', 'geotour'),
                        'type' => 'string',
                        'validate_callback' => 'geotour_validate_bbox_v2',
                    ],
                    'listing_category' => [
                        'description' => __('Filter by listing category slugs. Multiple values separated by commas.', 'geotour'),
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'listing_region' => [
                        'description' => __('Filter by listing region slugs. Multiple values separated by commas.', 'geotour'),
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'listing_tag' => [
                        'description' => __('Filter by listing tag slugs. Multiple values separated by commas.', 'geotour'),
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ]
    );
}
add_action('rest_api_init', 'geotour_register_spatial_info_v2_route');

/**
 * Validates the bbox parameter for v2.
 */
function geotour_validate_bbox_v2($param, $request, $key) {
    if (!is_string($param)) {
        return new WP_Error('rest_invalid_param', __('Bounding box (bbox) must be a string.', 'geotour'), ['status' => 400]);
    }
    
    $coords = explode(',', $param);
    if (count($coords) !== 4) {
        return new WP_Error('rest_invalid_param', __('Bounding box (bbox) must have 4 comma-separated values: minLng,minLat,maxLng,maxLat.', 'geotour'), ['status' => 400]);
    }

    foreach ($coords as $coord) {
        if (!is_numeric(trim($coord))) {
            return new WP_Error('rest_invalid_param', __('All bounding box (bbox) coordinates must be numeric.', 'geotour'), ['status' => 400]);
        }
    }

    $minLng = floatval(trim($coords[0]));
    $minLat = floatval(trim($coords[1]));
    $maxLng = floatval(trim($coords[2]));
    $maxLat = floatval(trim($coords[3]));

    if ($minLat < -90 || $minLat > 90 || $maxLat < -90 || $maxLat > 90) {
        return new WP_Error('rest_invalid_param', __('Bounding box (bbox) latitudes must be between -90 and 90.', 'geotour'), ['status' => 400]);
    }
    if ($minLng < -180 || $minLng > 180 || $maxLng < -180 || $maxLng > 180) {
        return new WP_Error('rest_invalid_param', __('Bounding box (bbox) longitudes must be between -180 and 180.', 'geotour'), ['status' => 400]);
    }
    if ($minLat > $maxLat) {
        return new WP_Error('rest_invalid_param', __('Bounding box (bbox) minLat cannot be greater than maxLat.', 'geotour'), ['status' => 400]);
    }

    return true;
}

/**
 * Callback function to retrieve listing spatial info for v2.
 */
function geotour_get_spatial_info_v2(WP_REST_Request $request) {
    $per_page = $request->get_param('per_page') ?: 500;
    $bbox = $request->get_param('bbox');
    $listing_category = $request->get_param('listing_category');
    $listing_region = $request->get_param('listing_region');
    $listing_tag = $request->get_param('listing_tag');

    // Base query args
    $query_args = [
        'post_type' => 'listing',
        'post_status' => 'publish',
        'posts_per_page' => min($per_page, 999),
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'position',
                'compare' => 'EXISTS'
            ]
        ]
    ];

    // Add taxonomy filters
    $tax_query = ['relation' => 'AND'];

    if (!empty($listing_category)) {
        $category_slugs = array_map('trim', explode(',', $listing_category));
        $tax_query[] = [
            'taxonomy' => 'listing-category',
            'field' => 'slug',
            'terms' => $category_slugs,
            'operator' => 'IN'
        ];
    }

    if (!empty($listing_region)) {
        $region_slugs = array_map('trim', explode(',', $listing_region));
        $tax_query[] = [
            'taxonomy' => 'listing-region',
            'field' => 'slug',
            'terms' => $region_slugs,
            'operator' => 'IN'
        ];
    }

    if (!empty($listing_tag)) {
        $tag_slugs = array_map('trim', explode(',', $listing_tag));
        $tax_query[] = [
            'taxonomy' => 'listing-tag',
            'field' => 'slug',
            'terms' => $tag_slugs,
            'operator' => 'IN'
        ];
    }

    if (count($tax_query) > 1) {
        $query_args['tax_query'] = $tax_query;
    }

    // Handle bounding box filtering with custom query
    if (!empty($bbox)) {
        $coords = explode(',', $bbox);
        $minLng = floatval(trim($coords[0]));
        $minLat = floatval(trim($coords[1]));
        $maxLng = floatval(trim($coords[2]));
        $maxLat = floatval(trim($coords[3]));

        // We'll filter at the PHP level after getting all posts
        // This is more reliable than trying to filter ACF serialized data in the database
    }

    $query = new WP_Query($query_args);
    $data = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $position = get_field('position', $post_id);

            if (empty($position) || !isset($position['lat']) || !isset($position['lng'])) {
                continue; // Skip if no valid coordinates
            }

            $lat = floatval($position['lat']);
            $lng = floatval($position['lng']);

            // Apply bbox filtering at PHP level for precision
            if (!empty($bbox)) {
                if ($lat < $minLat || $lat > $maxLat || $lng < $minLng || $lng > $maxLng) {
                    continue; // Skip if outside bbox
                }
            }

            // Get taxonomy data
            $categories = get_the_terms($post_id, 'listing-category');
            $regions = get_the_terms($post_id, 'listing-region');
            $tags = get_the_terms($post_id, 'listing-tag');

            // Get Yoast meta description
            $meta_description = '';
            if (class_exists('WPSEO_Meta')) {
                $meta_description = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
            }
            if (empty($meta_description)) {
                $meta_description = get_the_excerpt();
            }

            $item = [
                'id' => $post_id,
                'title' => get_the_title(),
                'excerpt' => get_the_excerpt(),
                'meta_description' => $meta_description,
                'permalink' => get_permalink(),
                'latitude' => floatval($position['lat']),
                'longitude' => floatval($position['lng']),
                'map_icon_url' => geotour_get_listing_map_icon_url($post_id),
                'categories' => [],
                'regions' => [],
                'tags' => [],
                'featured_image' => get_the_post_thumbnail_url($post_id, 'thumbnail'), // Small square thumbnail
                'featured_image_medium' => get_the_post_thumbnail_url($post_id, 'medium'),
            ];

            // Add category data
            if (!empty($categories) && !is_wp_error($categories)) {
                foreach ($categories as $category) {
                    $item['categories'][] = [
                        'id' => $category->term_id,
                        'name' => $category->name,
                        'slug' => $category->slug
                    ];
                }
            }

            // Add region data
            if (!empty($regions) && !is_wp_error($regions)) {
                foreach ($regions as $region) {
                    $item['regions'][] = [
                        'id' => $region->term_id,
                        'name' => $region->name,
                        'slug' => $region->slug
                    ];
                }
            }

            // Add tag data
            if (!empty($tags) && !is_wp_error($tags)) {
                foreach ($tags as $tag) {
                    $item['tags'][] = [
                        'id' => $tag->term_id,
                        'name' => $tag->name,
                        'slug' => $tag->slug
                    ];
                }
            }

            $data[] = $item;
        }
        wp_reset_postdata();
    }

    return new WP_REST_Response($data, 200);
}