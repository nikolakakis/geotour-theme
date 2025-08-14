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

/**
 * Display post date with time element
 */
if (!function_exists('geotour_posted_on')) {
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
            /* translators: %s: post date. */
            esc_html_x('Posted on %s', 'post date', 'geotour'),
            '<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . $time_string . '</a>'
        );

        echo '<span class="posted-on">' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}



/**
 * Display entry footer with categories, tags, and edit link
 */
if (!function_exists('geotour_entry_footer')) {
    function geotour_entry_footer() {
        // Hide category and tag text for pages.
        if ('post' === get_post_type()) {
            /* translators: used between list items, there is a space after the comma */
            $categories_list = get_the_category_list(esc_html__(', ', 'geotour'));
            if ($categories_list) {
                /* translators: 1: list of categories. */
                printf('<span class="cat-links">' . esc_html__('Posted in %1$s', 'geotour') . '</span>', $categories_list); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }

            /* translators: used between list items, there is a space after the comma */
            $tags_list = get_the_tag_list('', esc_html_x(', ', 'list item separator', 'geotour'));
            if ($tags_list) {
                /* translators: 1: list of tags. */
                printf('<span class="tags-links">' . esc_html__('Tagged %1$s', 'geotour') . '</span>', $tags_list); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }

        if (!is_single() && !post_password_required() && (comments_open() || get_comments_number())) {
            echo '<span class="comments-link">';
            comments_popup_link(
                sprintf(
                    wp_kses(
                        /* translators: %s: post title */
                        __('Leave a Comment<span class="screen-reader-text"> on %s</span>', 'geotour'),
                        array(
                            'span' => array(
                                'class' => array(),
                            ),
                        )
                    ),
                    wp_kses_post(get_the_title())
                )
            );
            echo '</span>';
        }

        edit_post_link(
            sprintf(
                wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers */
                    __('Edit <span class="screen-reader-text">"%s"</span>', 'geotour'),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post(get_the_title())
            ),
            '<span class="edit-link">',
            '</span>'
        );
    }
}

/**
 * Generate better meta descriptions for different page types
 */
function geotour_get_meta_description() {
    $meta_description = '';
    
    if (is_singular('listing')) {
        // For listings, create rich description
        $post_id = get_the_ID();
        
        // Try Yoast first
        $meta_description = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
        
        if (empty($meta_description)) {
            // Create from listing data
            $categories = wp_get_post_terms($post_id, 'listing-category');
            $regions = wp_get_post_terms($post_id, 'listing-region');
            
            $category_name = (!empty($categories) && !is_wp_error($categories)) ? $categories[0]->name : '';
            $region_name = (!empty($regions) && !is_wp_error($regions)) ? $regions[0]->name : '';
            
            $meta_description = sprintf(
                __('Discover %s in %s, Crete. %s', 'geotour'),
                get_the_title(),
                $region_name ?: 'Crete',
                wp_trim_words(get_the_excerpt() ?: get_the_content(), 20, '...')
            );
        }
    } elseif (is_page_template('page-listing.php')) {
        // Big map page
        $meta_description = __('Explore Crete with our interactive map. Discover archaeological sites, beaches, museums, and hidden gems across the island.', 'geotour');
    } elseif (is_page_template('page-homepage.php')) {
        // Homepage template
        $meta_description = get_bloginfo('description') ?: __('Your ultimate guide to exploring Crete. Discover the best places to visit, from ancient Minoan sites to pristine beaches.', 'geotour');
    } elseif (is_tax('listing-category')) {
        // Listing category pages
        $term = get_queried_object();
        $meta_description = sprintf(
            __('Explore %s in Crete. Find the best %s locations with detailed information, photos, and interactive maps.', 'geotour'),
            $term->name,
            strtolower($term->name)
        );
    } elseif (is_tax('listing-region')) {
        // Listing region pages
        $term = get_queried_object();
        $meta_description = sprintf(
            __('Discover %s region in Crete. Explore archaeological sites, beaches, attractions and local gems in %s.', 'geotour'),
            $term->name,
            $term->name
        );
    } elseif (is_home() || is_front_page()) {
        // Blog home or front page
        $meta_description = get_bloginfo('description') ?: __('Explore Crete with GeoTour - your comprehensive guide to the island\'s history, culture, and natural beauty.', 'geotour');
    } elseif (is_404()) {
        // 404 pages
        $meta_description = __('Page not found. Explore our interactive map of Crete or search for archaeological sites, beaches, and attractions.', 'geotour');
    }
    
    return $meta_description;
}

/**
 * Get search results count by post type
 * 
 * @param string $search_query The search query
 * @return array Array of post types and their counts
 */
function geotour_get_search_results_by_type($search_query) {
    if (empty($search_query)) {
        return array();
    }
    
    // Get all public post types that support search
    $post_types = get_post_types(array(
        'public' => true,
        'exclude_from_search' => false
    ), 'names');
    
    // Remove attachment from search as it's not typically what users want
    if (isset($post_types['attachment'])) {
        unset($post_types['attachment']);
    }
    
    $results = array();
    
    foreach ($post_types as $post_type) {
        $query = new WP_Query(array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            's' => $search_query,
            'posts_per_page' => -1,
            'fields' => 'ids' // Only get IDs for performance
        ));
        
        $count = $query->found_posts;
        if ($count > 0) {
            $results[$post_type] = $count;
        }
        wp_reset_postdata();
    }
    
    // Get special counts for listings if they exist
    if (isset($results['listing']) && $results['listing'] > 0) {
        // Count listings in POIs category
        $pois_query = new WP_Query(array(
            'post_type' => 'listing',
            'post_status' => 'publish',
            's' => $search_query,
            'tax_query' => array(
                array(
                    'taxonomy' => 'listing-category',
                    'field' => 'slug',
                    'terms' => 'pois'
                )
            ),
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        if ($pois_query->found_posts > 0) {
            $results['listing_pois'] = $pois_query->found_posts;
        }
        wp_reset_postdata();
    }
    
    return $results;
}