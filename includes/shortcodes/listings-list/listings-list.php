<?php
/**
 * Listings List Shortcode
 * 
 * Displays a paginated list of listing posts with filtering and navigation.
 * 
 * @package Geotour_Mobile_First
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register the listings list shortcode
 */
function geotour_register_listings_list_shortcode() {
    add_shortcode( 'listings_list', 'geotour_listings_list_shortcode' );
}
add_action( 'init', 'geotour_register_listings_list_shortcode' );

/**
 * Enqueue assets for listings list
 */
function geotour_listings_list_enqueue_assets() {
    // Check if shortcode is present on the page
    global $post;
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'listings_list' ) ) {
        
        // Get Vite assets helper (should already be available from scripts-styles.php)
        $vite_assets = geotour_get_vite_assets();
        
        if ( isset( $vite_assets['js_src'] ) ) {
            // Development mode
            wp_enqueue_script(
                'geotour-listings-list',
                $vite_assets['base_uri'] . '/src/js/listings-list.js',
                array(),
                GEOTOUR_THEME_VERSION,
                true
            );
        } elseif ( ! empty( $vite_assets['manifest'] ) ) {
            // Production mode - get assets from manifest
            $manifest = $vite_assets['manifest'];
            $base_uri = $vite_assets['base_uri'];
            
            // Look for the listings-list entry in manifest
            $entry_key = 'src/js/listings-list.js';
            
            if ( isset( $manifest[ $entry_key ] ) ) {
                $entry = $manifest[ $entry_key ];
                
                // Enqueue JavaScript
                if ( isset( $entry['file'] ) ) {
                    wp_enqueue_script(
                        'geotour-listings-list',
                        $base_uri . $entry['file'],
                        array(),
                        GEOTOUR_THEME_VERSION,
                        true
                    );
                }
                
                // Enqueue CSS
                if ( isset( $entry['css'] ) ) {
                    foreach ( $entry['css'] as $index => $css_file ) {
                        wp_enqueue_style(
                            'geotour-listings-list-' . $index,
                            $base_uri . $css_file,
                            array(),
                            GEOTOUR_THEME_VERSION
                        );
                    }
                }
            }
        }

        // Localize script with data
        wp_localize_script(
            'geotour-listings-list',
            'geotourListingsData',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'geotour_listings_nonce' )
            )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'geotour_listings_list_enqueue_assets' );

/**
 * Get listing categories that have map icons
 * 
 * @return array Array of category term objects that have category_map_icon set
 */
function geotour_get_categories_with_icons() {
    $categories = get_terms( array(
        'taxonomy' => 'listing-category',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ) );

    if ( is_wp_error( $categories ) || empty( $categories ) ) {
        return array();
    }

    $categories_with_icons = array();

    foreach ( $categories as $category ) {
        // Check if ACF field exists and has a value
        $icon = get_field( 'category_map_icon', 'listing-category_' . $category->term_id );
        
        if ( ! empty( $icon ) && is_array( $icon ) && isset( $icon['url'] ) ) {
            $category->icon_url = $icon['url'];
            $category->icon_alt = isset( $icon['alt'] ) ? $icon['alt'] : $category->name;
            $categories_with_icons[] = $category;
        }
    }

    return $categories_with_icons;
}

/**
 * Get hierarchical listing regions for dropdown
 * 
 * @return array Hierarchical array of region terms
 */
function geotour_get_hierarchical_regions() {
    $regions = get_terms( array(
        'taxonomy' => 'listing-region',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
        'parent' => 0 // Get top-level terms first
    ) );

    if ( is_wp_error( $regions ) || empty( $regions ) ) {
        return array();
    }

    $hierarchical = array();

    foreach ( $regions as $region ) {
        $hierarchical[] = array(
            'term' => $region,
            'level' => 0
        );
        
        // Get children recursively
        $children = geotour_get_region_children( $region->term_id, 1 );
        if ( ! empty( $children ) ) {
            $hierarchical = array_merge( $hierarchical, $children );
        }
    }

    return $hierarchical;
}

/**
 * Get children of a region term recursively
 * 
 * @param int $parent_id Parent term ID
 * @param int $level Depth level for indentation
 * @return array Array of child terms with level
 */
function geotour_get_region_children( $parent_id, $level ) {
    $children = get_terms( array(
        'taxonomy' => 'listing-region',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
        'parent' => $parent_id
    ) );

    if ( is_wp_error( $children ) || empty( $children ) ) {
        return array();
    }

    $result = array();

    foreach ( $children as $child ) {
        $result[] = array(
            'term' => $child,
            'level' => $level
        );

        // Get grandchildren
        $grandchildren = geotour_get_region_children( $child->term_id, $level + 1 );
        if ( ! empty( $grandchildren ) ) {
            $result = array_merge( $result, $grandchildren );
        }
    }

    return $result;
}

/**
 * Calculate which page a specific listing is on
 * 
 * @param int $post_id The post ID to find
 * @param array $atts The shortcode attributes (for filtering and sorting)
 * @return int The page number where the post is located
 */
function geotour_calculate_listing_page( $post_id, $atts ) {
    
    // Build the same query args as the main shortcode (without pagination)
    $query_args = array(
        'post_type' => 'listing',
        'posts_per_page' => -1, // Get all posts
        'orderby' => sanitize_text_field( $atts['orderby'] ),
        'order' => sanitize_text_field( $atts['order'] ),
        'post_status' => 'publish',
        'fields' => 'ids', // Only get IDs for performance
    );

    // Add tax query if filters are set
    $tax_query = array();

    // Categories: OR relation (match ANY category)
    if ( ! empty( $atts['category'] ) ) {
        $categories = explode( ',', sanitize_text_field( $atts['category'] ) );
        $tax_query[] = array(
            'taxonomy' => 'listing-category',
            'field'    => 'slug',
            'terms'    => $categories,
            'operator' => 'IN', // OR - listing can be in any of these categories
        );
    }

    // Region: AND relation with categories
    if ( ! empty( $atts['region'] ) ) {
        $tax_query[] = array(
            'taxonomy' => 'listing-region',
            'field'    => 'slug',
            'terms'    => explode( ',', sanitize_text_field( $atts['region'] ) ),
            'operator' => 'IN',
        );
    }

    // Content Type: AND relation with other filters
    if ( ! empty( $atts['content_type'] ) ) {
        $tax_query[] = array(
            'taxonomy' => 'listing-content-type',
            'field'    => 'slug',
            'terms'    => explode( ',', sanitize_text_field( $atts['content_type'] ) ),
            'operator' => 'IN',
        );
    }

    // Set relation to AND for multiple taxonomy conditions
    // This means: (Category1 OR Category2 OR Category3) AND (Region) AND (ContentType)
    if ( count( $tax_query ) > 1 ) {
        $tax_query['relation'] = 'AND';
    }

    if ( ! empty( $tax_query ) ) {
        $query_args['tax_query'] = $tax_query;
    }

    // Execute query to get all matching post IDs
    $all_posts = get_posts( $query_args );

    // Find the position of our target post
    $position = array_search( $post_id, $all_posts );

    // If post not found in results, return page 1
    if ( $position === false ) {
        return 1;
    }

    // Calculate page number (positions are 0-indexed, pages are 1-indexed)
    $posts_per_page = intval( $atts['posts_per_page'] );
    $page = floor( $position / $posts_per_page ) + 1;

    return $page;
}

/**
 * Render the listings list shortcode
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function geotour_listings_list_shortcode( $atts ) {
    
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'posts_per_page' => 10,
            'category' => '',
            'region' => '',
            'content_type' => '',
            'orderby' => 'date',
            'order' => 'DESC',
        ),
        $atts,
        'listings_list'
    );

    // Allow URL parameters to override shortcode attributes
    if ( isset( $_GET['listing_category'] ) ) {
        $atts['category'] = sanitize_text_field( $_GET['listing_category'] );
    }
    if ( isset( $_GET['listing_region'] ) ) {
        $atts['region'] = sanitize_text_field( $_GET['listing_region'] );
    }
    if ( isset( $_GET['listing_content_type'] ) ) {
        $atts['content_type'] = sanitize_text_field( $_GET['listing_content_type'] );
    }
    if ( isset( $_GET['listing_orderby'] ) ) {
        $atts['orderby'] = sanitize_text_field( $_GET['listing_orderby'] );
    }
    if ( isset( $_GET['listing_order'] ) ) {
        $atts['order'] = sanitize_text_field( $_GET['listing_order'] );
    }
    if ( isset( $_GET['listing_per_page'] ) ) {
        $atts['posts_per_page'] = absint( $_GET['listing_per_page'] );
    }

    // Get text search parameter
    $search_query = isset( $_GET['listing_search'] ) ? sanitize_text_field( $_GET['listing_search'] ) : '';

    // Get highlight post ID from URL (for scroll-to functionality)
    $highlight_id = isset( $_GET['highlight_post'] ) ? absint( $_GET['highlight_post'] ) : 0;

    // If highlight_post is set, calculate which page it's on
    $paged = 1;
    if ( $highlight_id > 0 ) {
        $paged = geotour_calculate_listing_page( $highlight_id, $atts );
    } elseif ( isset( $_GET['listing_page'] ) ) {
        $paged = absint( $_GET['listing_page'] );
    } elseif ( get_query_var( 'paged' ) ) {
        $paged = get_query_var( 'paged' );
    }

    // Build query arguments
    $query_args = array(
        'post_type' => 'listing',
        'posts_per_page' => intval( $atts['posts_per_page'] ),
        'paged' => $paged,
        'orderby' => sanitize_text_field( $atts['orderby'] ),
        'order' => sanitize_text_field( $atts['order'] ),
        'post_status' => 'publish',
    );

    // Add text search if provided
    if ( ! empty( $search_query ) ) {
        $query_args['s'] = $search_query;
    }

    // Add tax query if filters are set
    $tax_query = array();

    // Categories: OR relation (match ANY category)
    if ( ! empty( $atts['category'] ) ) {
        $categories = explode( ',', sanitize_text_field( $atts['category'] ) );
        $tax_query[] = array(
            'taxonomy' => 'listing-category',
            'field'    => 'slug',
            'terms'    => $categories,
            'operator' => 'IN', // OR - listing can be in any of these categories
        );
    }

    // Region: AND relation with categories
    if ( ! empty( $atts['region'] ) ) {
        $tax_query[] = array(
            'taxonomy' => 'listing-region',
            'field'    => 'slug',
            'terms'    => explode( ',', sanitize_text_field( $atts['region'] ) ),
            'operator' => 'IN',
        );
    }

    // Content Type: AND relation with other filters
    if ( ! empty( $atts['content_type'] ) ) {
        $tax_query[] = array(
            'taxonomy' => 'listing-content-type',
            'field'    => 'slug',
            'terms'    => explode( ',', sanitize_text_field( $atts['content_type'] ) ),
            'operator' => 'IN',
        );
    }

    // Set relation to AND for multiple taxonomy conditions
    // This means: (Category1 OR Category2 OR Category3) AND (Region) AND (ContentType)
    if ( count( $tax_query ) > 1 ) {
        $tax_query['relation'] = 'AND';
    }

    if ( ! empty( $tax_query ) ) {
        $query_args['tax_query'] = $tax_query;
    }

    // Execute query
    $listings_query = new WP_Query( $query_args );

    // Start output buffering
    ob_start();

    ?>
    <div class="listings-list-container">
        <?php 
        // Render filter/sort header
        geotour_render_listings_filter_header( $atts, $search_query, $listings_query->found_posts ); 
        ?>

        <?php if ( $listings_query->have_posts() ) : ?>
            <div class="listings-list" data-highlight-id="<?php echo esc_attr( $highlight_id ); ?>">
                <?php
                while ( $listings_query->have_posts() ) {
                    $listings_query->the_post();
                    geotour_render_listing_item( get_the_ID(), $highlight_id );
                }
                ?>
            </div>

            <?php
            // Render pagination
            geotour_render_listings_pagination( $listings_query, $paged, $atts );
            ?>
        <?php else : ?>
            <p class="listings-list-no-results"><?php esc_html_e( 'No listings found.', 'geotour' ); ?></p>
        <?php endif; ?>
    </div>
    <?php

    wp_reset_postdata();

    return ob_get_clean();
}

/**
 * Render a single listing item
 * 
 * @param int $post_id The post ID
 * @param int $highlight_id The ID of the post to highlight
 */
function geotour_render_listing_item( $post_id, $highlight_id = 0 ) {
    
    // Get taxonomies
    $categories = get_the_terms( $post_id, 'listing-category' );
    $regions = get_the_terms( $post_id, 'listing-region' );
    $content_types = get_the_terms( $post_id, 'listing-content-type' );
    
    // Check if content type is simple
    $is_simple = false;
    if ( $content_types && ! is_wp_error( $content_types ) ) {
        foreach ( $content_types as $content_type ) {
            if ( $content_type->slug === 'simple' ) {
                $is_simple = true;
                break;
            }
        }
    }

    // Check if this is the highlighted post
    $is_highlighted = ( $highlight_id && $highlight_id === $post_id );
    $highlight_class = $is_highlighted ? ' listing-item--highlighted' : '';

    ?>
    <article id="listing-<?php echo esc_attr( $post_id ); ?>" class="listing-item<?php echo esc_attr( $highlight_class ); ?>" data-post-id="<?php echo esc_attr( $post_id ); ?>">
        
        <div class="listing-item__image">
            <?php if ( ! $is_simple ) : ?>
                <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="listing-item__image-link">
                    <?php 
                    if ( has_post_thumbnail( $post_id ) ) {
                        echo get_the_post_thumbnail( $post_id, 'medium', array( 'class' => 'listing-item__thumbnail' ) );
                    } else {
                        $fallback_image_url = home_url( '/wp-content/uploads/2024/04/no-image-bello.jpg' );
                        echo '<img src="' . esc_url( $fallback_image_url ) . '" alt="' . esc_attr( get_the_title( $post_id ) ) . '" class="listing-item__thumbnail listing-item__thumbnail--fallback" />';
                    }
                    ?>
                </a>
            <?php else : ?>
                <?php 
                if ( has_post_thumbnail( $post_id ) ) {
                    echo get_the_post_thumbnail( $post_id, 'medium', array( 'class' => 'listing-item__thumbnail' ) );
                } else {
                    $fallback_image_url = home_url( '/wp-content/uploads/2024/04/no-image-bello.jpg' );
                    echo '<img src="' . esc_url( $fallback_image_url ) . '" alt="' . esc_attr( get_the_title( $post_id ) ) . '" class="listing-item__thumbnail listing-item__thumbnail--fallback" />';
                }
                ?>
            <?php endif; ?>
        </div>

        <div class="listing-item__content">
            
            <h2 class="listing-item__title">
                <?php if ( ! $is_simple ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="listing-item__title-link">
                        <?php echo esc_html( get_the_title( $post_id ) ); ?>
                    </a>
                <?php else : ?>
                    <?php echo esc_html( get_the_title( $post_id ) ); ?>
                <?php endif; ?>
            </h2>

            <div class="listing-item__meta">
                
                <?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
                    <div class="listing-item__categories">
                        <span class="listing-item__meta-label"><?php esc_html_e( 'Category:', 'geotour' ); ?></span>
                        <?php
                        $category_names = array();
                        foreach ( $categories as $category ) {
                            $category_names[] = esc_html( $category->name );
                        }
                        echo implode( ', ', $category_names );
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ( $regions && ! is_wp_error( $regions ) ) : ?>
                    <div class="listing-item__regions">
                        <span class="listing-item__meta-label"><?php esc_html_e( 'Region:', 'geotour' ); ?></span>
                        <?php
                        $region_names = array();
                        foreach ( $regions as $region ) {
                            $region_names[] = esc_html( $region->name );
                        }
                        echo implode( ', ', $region_names );
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ( $content_types && ! is_wp_error( $content_types ) ) : ?>
                    <div class="listing-item__content-types">
                        <span class="listing-item__meta-label"><?php esc_html_e( 'Type:', 'geotour' ); ?></span>
                        <?php
                        $type_names = array();
                        foreach ( $content_types as $type ) {
                            $type_names[] = esc_html( $type->name );
                        }
                        echo implode( ', ', $type_names );
                        ?>
                    </div>
                <?php endif; ?>

            </div>

            <div class="listing-item__excerpt">
                <?php echo wp_kses_post( get_the_excerpt( $post_id ) ); ?>
            </div>

            <?php if ( ! $is_simple ) : ?>
                <div class="listing-item__actions">
                    <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="listing-item__read-more">
                        <?php esc_html_e( 'Read More', 'geotour' ); ?>
                    </a>
                </div>
            <?php endif; ?>

        </div>

    </article>
    <?php
}

/**
 * Render the filter and sort header
 * 
 * @param array $atts Shortcode attributes
 * @param string $search_query Current search query
 * @param int $found_posts Number of found posts
 */
function geotour_render_listings_filter_header( $atts, $search_query, $found_posts ) {
    
    // Get categories with icons
    $categories = geotour_get_categories_with_icons();
    
    // Get hierarchical regions
    $regions = geotour_get_hierarchical_regions();
    
    // Get current filters from URL
    $current_categories = isset( $_GET['listing_category'] ) ? explode( ',', sanitize_text_field( $_GET['listing_category'] ) ) : array();
    $current_region = isset( $_GET['listing_region'] ) ? sanitize_text_field( $_GET['listing_region'] ) : '';
    $current_orderby = isset( $_GET['listing_orderby'] ) ? sanitize_text_field( $_GET['listing_orderby'] ) : 'date';
    $current_order = isset( $_GET['listing_order'] ) ? sanitize_text_field( $_GET['listing_order'] ) : 'DESC';
    
    ?>
    <div class="listings-filter-header">
        <div class="listings-filter-header__inner">
            
            <!-- Results count -->
            <div class="listings-filter-header__count">
                <span class="count-number"><?php echo esc_html( number_format_i18n( $found_posts ) ); ?></span>
                <span class="count-label">
                    <?php 
                    echo esc_html( _n( 'listing found', 'listings found', $found_posts, 'geotour' ) ); 
                    ?>
                </span>
            </div>

            <!-- Search box -->
            <div class="listings-filter-search">
                <label for="listing-search-input" class="screen-reader-text"><?php esc_html_e( 'Search listings', 'geotour' ); ?></label>
                <input 
                    type="text" 
                    id="listing-search-input" 
                    class="listings-filter-search__input" 
                    placeholder="<?php esc_attr_e( 'Search listings...', 'geotour' ); ?>"
                    value="<?php echo esc_attr( $search_query ); ?>"
                    name="listing_search"
                >
                <button type="button" class="listings-filter-search__button" aria-label="<?php esc_attr_e( 'Search', 'geotour' ); ?>">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19 19L14.65 14.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <!-- Sort options -->
            <div class="listings-filter-sort">
                <label for="listing-sort-select" class="listings-filter-sort__label">
                    <?php esc_html_e( 'Sort by:', 'geotour' ); ?>
                </label>
                <select id="listing-sort-select" class="listings-filter-sort__select" name="listing_orderby">
                    <option value="date" <?php selected( $current_orderby === 'date' && $current_order === 'DESC' ); ?>>
                        <?php esc_html_e( 'Newest First', 'geotour' ); ?>
                    </option>
                    <option value="date-asc" <?php selected( $current_orderby === 'date' && $current_order === 'ASC' ); ?>>
                        <?php esc_html_e( 'Oldest First', 'geotour' ); ?>
                    </option>
                    <option value="title" <?php selected( $current_orderby === 'title' && $current_order === 'ASC' ); ?>>
                        <?php esc_html_e( 'Name (A-Z)', 'geotour' ); ?>
                    </option>
                    <option value="title-desc" <?php selected( $current_orderby === 'title' && $current_order === 'DESC' ); ?>>
                        <?php esc_html_e( 'Name (Z-A)', 'geotour' ); ?>
                    </option>
                    <option value="modified" <?php selected( $current_orderby === 'modified' && $current_order === 'DESC' ); ?>>
                        <?php esc_html_e( 'Recently Updated', 'geotour' ); ?>
                    </option>
                </select>
            </div>

            <!-- Filter toggle button -->
            <button type="button" class="listings-filter-toggle" aria-expanded="false" aria-controls="listings-filters-panel">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 5H18M5 10H15M8 15H12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span><?php esc_html_e( 'Filter Options', 'geotour' ); ?></span>
                <span class="filter-toggle-icon">▼</span>
            </button>

            <!-- View on Map button -->
            <button type="button" class="listings-view-map-btn" id="listings-view-map-btn">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 6V19L7 16L13 19L19 16V3L13 6L7 3L1 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M7 3V16M13 6V19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span><?php esc_html_e( 'View on Map', 'geotour' ); ?></span>
            </button>
        </div>

        <!-- Filters panel -->
        <div id="listings-filters-panel" class="listings-filters-panel" hidden>
            
            <!-- Category filters (icons) -->
            <?php if ( ! empty( $categories ) ) : ?>
                <div class="listings-filter-group">
                    <h3 class="listings-filter-group__title"><?php esc_html_e( 'Categories', 'geotour' ); ?></h3>
                    <div class="listings-filter-categories">
                        <?php foreach ( $categories as $category ) : 
                            $is_active = in_array( $category->slug, $current_categories );
                            $active_class = $is_active ? ' is-active' : '';
                        ?>
                            <button 
                                type="button" 
                                class="listings-filter-category<?php echo esc_attr( $active_class ); ?>" 
                                data-slug="<?php echo esc_attr( $category->slug ); ?>"
                                data-name="<?php echo esc_attr( $category->name ); ?>"
                                aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
                                title="<?php echo esc_attr( $category->name ); ?>"
                            >
                                <img 
                                    src="<?php echo esc_url( $category->icon_url ); ?>" 
                                    alt="<?php echo esc_attr( $category->icon_alt ); ?>"
                                    class="listings-filter-category__icon"
                                >
                                <span class="listings-filter-category__name"><?php echo esc_html( $category->name ); ?></span>
                                <span class="listings-filter-category__checkmark" aria-hidden="true">✓</span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Region filter (dropdown) -->
            <?php if ( ! empty( $regions ) ) : ?>
                <div class="listings-filter-group">
                    <label for="listing-region-select" class="listings-filter-group__title">
                        <?php esc_html_e( 'Region', 'geotour' ); ?>
                    </label>
                    <select id="listing-region-select" class="listings-filter-region__select" name="listing_region">
                        <option value=""><?php esc_html_e( 'All Regions', 'geotour' ); ?></option>
                        <?php foreach ( $regions as $region_data ) : 
                            $region = $region_data['term'];
                            $level = $region_data['level'];
                            $indent = str_repeat( '— ', $level );
                        ?>
                            <option 
                                value="<?php echo esc_attr( $region->slug ); ?>"
                                <?php selected( $current_region, $region->slug ); ?>
                                data-level="<?php echo esc_attr( $level ); ?>"
                            >
                                <?php echo esc_html( $indent . $region->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <!-- Active filters display -->
            <div class="listings-active-filters" hidden>
                <span class="listings-active-filters__label"><?php esc_html_e( 'Active filters:', 'geotour' ); ?></span>
                <div class="listings-active-filters__tags"></div>
            </div>

            <!-- Apply/Clear buttons -->
            <div class="listings-filter-actions">
                <button type="button" class="listings-filter-apply">
                    <?php esc_html_e( 'Apply Filters', 'geotour' ); ?>
                </button>
                <button type="button" class="listings-filter-clear">
                    <?php esc_html_e( 'Clear All', 'geotour' ); ?>
                </button>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render pagination for listings
 * 
 * @param WP_Query $query The query object
 * @param int $paged Current page number
 * @param array $atts Shortcode attributes to preserve in URLs
 */
function geotour_render_listings_pagination( $query, $paged, $atts = array() ) {
    
    $total_pages = $query->max_num_pages;

    if ( $total_pages <= 1 ) {
        return;
    }

    // Get current URL and remove pagination parameters
    $current_url = add_query_arg( null, null );
    $current_url = remove_query_arg( array( 'listing_page', 'paged', 'highlight_post' ), $current_url );

    // Build URL parameters to preserve filters
    $url_params = array();
    
    // Preserve filter parameters from URL if they exist
    if ( isset( $_GET['listing_category'] ) ) {
        $url_params['listing_category'] = sanitize_text_field( $_GET['listing_category'] );
    }
    if ( isset( $_GET['listing_region'] ) ) {
        $url_params['listing_region'] = sanitize_text_field( $_GET['listing_region'] );
    }
    if ( isset( $_GET['listing_content_type'] ) ) {
        $url_params['listing_content_type'] = sanitize_text_field( $_GET['listing_content_type'] );
    }
    if ( isset( $_GET['listing_orderby'] ) ) {
        $url_params['listing_orderby'] = sanitize_text_field( $_GET['listing_orderby'] );
    }
    if ( isset( $_GET['listing_order'] ) ) {
        $url_params['listing_order'] = sanitize_text_field( $_GET['listing_order'] );
    }
    if ( isset( $_GET['listing_per_page'] ) ) {
        $url_params['listing_per_page'] = absint( $_GET['listing_per_page'] );
    }

    ?>
    <nav class="listings-pagination" aria-label="<?php esc_attr_e( 'Listings navigation', 'geotour' ); ?>">
        <ul class="listings-pagination__list">
            
            <?php if ( $paged > 1 ) : ?>
                <li class="listings-pagination__item listings-pagination__item--prev">
                    <?php
                    $prev_params = array_merge( $url_params, array( 'listing_page' => $paged - 1 ) );
                    ?>
                    <a href="<?php echo esc_url( add_query_arg( $prev_params, $current_url ) ); ?>" class="listings-pagination__link">
                        <span><?php esc_html_e( '&laquo; Previous', 'geotour' ); ?></span>
                    </a>
                </li>
            <?php endif; ?>

            <?php
            // Show page numbers
            for ( $i = 1; $i <= $total_pages; $i++ ) {
                
                // Show first page, last page, current page, and pages around current
                if ( $i == 1 || $i == $total_pages || ( $i >= $paged - 2 && $i <= $paged + 2 ) ) {
                    
                    $active_class = ( $i == $paged ) ? ' listings-pagination__item--active' : '';
                    
                    ?>
                    <li class="listings-pagination__item<?php echo esc_attr( $active_class ); ?>">
                        <?php if ( $i == $paged ) : ?>
                            <span class="listings-pagination__link listings-pagination__link--current" aria-current="page">
                                <span><?php echo esc_html( $i ); ?></span>
                            </span>
                        <?php else : ?>
                            <?php
                            $page_params = array_merge( $url_params, array( 'listing_page' => $i ) );
                            ?>
                            <a href="<?php echo esc_url( add_query_arg( $page_params, $current_url ) ); ?>" class="listings-pagination__link">
                                <span><?php echo esc_html( $i ); ?></span>
                            </a>
                        <?php endif; ?>
                    </li>
                    <?php
                    
                } elseif ( $i == $paged - 3 || $i == $paged + 3 ) {
                    // Show ellipsis
                    ?>
                    <li class="listings-pagination__item listings-pagination__item--ellipsis">
                        <span class="listings-pagination__ellipsis">&hellip;</span>
                    </li>
                    <?php
                }
            }
            ?>

            <?php if ( $paged < $total_pages ) : ?>
                <li class="listings-pagination__item listings-pagination__item--next">
                    <?php
                    $next_params = array_merge( $url_params, array( 'listing_page' => $paged + 1 ) );
                    ?>
                    <a href="<?php echo esc_url( add_query_arg( $next_params, $current_url ) ); ?>" class="listings-pagination__link">
                        <span><?php esc_html_e( 'Next &raquo;', 'geotour' ); ?></span>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </nav>
    <?php
}

/**
 * Generate a URL to view a specific listing in the listings list
 * 
 * This helper function can be used from single listing pages or anywhere
 * to create a link that will navigate to the listings list page and scroll to a specific listing.
 * 
 * @param int $listing_id The listing post ID to highlight and scroll to
 * @param string $list_page_url The URL of the page containing the listings_list shortcode
 * @param array $filters Optional array of filters to apply (category, region, content_type, orderby, order)
 * @return string The complete URL with all parameters
 * 
 * @example
 * // Basic usage
 * $url = geotour_get_listing_in_list_url( 123, home_url('/all-listings/') );
 * 
 * // With filters
 * $url = geotour_get_listing_in_list_url( 
 *     123, 
 *     home_url('/all-listings/'), 
 *     array(
 *         'category' => 'museums',
 *         'region' => 'heraklion',
 *         'orderby' => 'title',
 *         'order' => 'ASC'
 *     )
 * );
 */
function geotour_get_listing_in_list_url( $listing_id, $list_page_url, $filters = array() ) {
    
    if ( empty( $listing_id ) || empty( $list_page_url ) ) {
        return '';
    }

    // Build URL parameters
    $params = array(
        'highlight_post' => absint( $listing_id )
    );

    // Add filter parameters if provided
    if ( ! empty( $filters['category'] ) ) {
        $params['listing_category'] = sanitize_text_field( $filters['category'] );
    }
    if ( ! empty( $filters['region'] ) ) {
        $params['listing_region'] = sanitize_text_field( $filters['region'] );
    }
    if ( ! empty( $filters['content_type'] ) ) {
        $params['listing_content_type'] = sanitize_text_field( $filters['content_type'] );
    }
    if ( ! empty( $filters['orderby'] ) ) {
        $params['listing_orderby'] = sanitize_text_field( $filters['orderby'] );
    }
    if ( ! empty( $filters['order'] ) ) {
        $params['listing_order'] = sanitize_text_field( $filters['order'] );
    }
    if ( ! empty( $filters['per_page'] ) ) {
        $params['listing_per_page'] = absint( $filters['per_page'] );
    }

    // Build and return the URL
    return add_query_arg( $params, $list_page_url );
}

/**
 * Output a "View in List" link for a specific listing
 * 
 * @param int $listing_id The listing post ID
 * @param string $list_page_url The URL of the listings list page
 * @param array $filters Optional filters array
 * @param string $link_text The text for the link (default: "View in List")
 * @param string $link_class Optional CSS class for the link
 * 
 * @example
 * // In single-listing.php
 * geotour_the_listing_in_list_link( get_the_ID(), home_url('/all-listings/') );
 */
function geotour_the_listing_in_list_link( $listing_id, $list_page_url, $filters = array(), $link_text = '', $link_class = '' ) {
    
    if ( empty( $link_text ) ) {
        $link_text = __( 'View in List', 'geotour' );
    }

    $url = geotour_get_listing_in_list_url( $listing_id, $list_page_url, $filters );

    if ( empty( $url ) ) {
        return;
    }

    $class_attr = ! empty( $link_class ) ? ' class="' . esc_attr( $link_class ) . '"' : '';

    echo '<a href="' . esc_url( $url ) . '"' . $class_attr . '>' . esc_html( $link_text ) . '</a>';
}
