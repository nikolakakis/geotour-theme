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
 * Display post author
 */
if (!function_exists('geotour_posted_by')) {
    function geotour_posted_by() {
        $byline = sprintf(
            /* translators: %s: post author. */
            esc_html_x('by %s', 'post author', 'geotour'),
            '<span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a></span>'
        );

        echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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