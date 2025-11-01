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

    // Get current page number from URL
    $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
    if ( isset( $_GET['listing_page'] ) ) {
        $paged = absint( $_GET['listing_page'] );
    }

    // Get highlight post ID from URL (for scroll-to functionality)
    $highlight_id = isset( $_GET['highlight_post'] ) ? absint( $_GET['highlight_post'] ) : 0;

    // Build query arguments
    $query_args = array(
        'post_type' => 'listing',
        'posts_per_page' => intval( $atts['posts_per_page'] ),
        'paged' => $paged,
        'orderby' => sanitize_text_field( $atts['orderby'] ),
        'order' => sanitize_text_field( $atts['order'] ),
        'post_status' => 'publish',
    );

    // Add tax query if filters are set
    $tax_query = array();

    if ( ! empty( $atts['category'] ) ) {
        $tax_query[] = array(
            'taxonomy' => 'listing-category',
            'field' => 'slug',
            'terms' => explode( ',', sanitize_text_field( $atts['category'] ) ),
        );
    }

    if ( ! empty( $atts['region'] ) ) {
        $tax_query[] = array(
            'taxonomy' => 'listing-region',
            'field' => 'slug',
            'terms' => explode( ',', sanitize_text_field( $atts['region'] ) ),
        );
    }

    if ( ! empty( $atts['content_type'] ) ) {
        $tax_query[] = array(
            'taxonomy' => 'listing-content-type',
            'field' => 'slug',
            'terms' => explode( ',', sanitize_text_field( $atts['content_type'] ) ),
        );
    }

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

    if ( $listings_query->have_posts() ) {
        ?>
        <div class="listings-list-container">
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
            geotour_render_listings_pagination( $listings_query, $paged );
            ?>
        </div>
        <?php
    } else {
        ?>
        <div class="listings-list-container">
            <p class="listings-list-no-results"><?php esc_html_e( 'No listings found.', 'geotour' ); ?></p>
        </div>
        <?php
    }

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
                        echo '<div class="listing-item__thumbnail listing-item__thumbnail--placeholder"></div>';
                    }
                    ?>
                </a>
            <?php else : ?>
                <?php 
                if ( has_post_thumbnail( $post_id ) ) {
                    echo get_the_post_thumbnail( $post_id, 'medium', array( 'class' => 'listing-item__thumbnail' ) );
                } else {
                    echo '<div class="listing-item__thumbnail listing-item__thumbnail--placeholder"></div>';
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
 * Render pagination for listings
 * 
 * @param WP_Query $query The query object
 * @param int $paged Current page number
 */
function geotour_render_listings_pagination( $query, $paged ) {
    
    $total_pages = $query->max_num_pages;

    if ( $total_pages <= 1 ) {
        return;
    }

    $current_url = add_query_arg( null, null );
    $current_url = remove_query_arg( array( 'listing_page', 'paged' ), $current_url );

    ?>
    <nav class="listings-pagination" aria-label="<?php esc_attr_e( 'Listings navigation', 'geotour' ); ?>">
        <ul class="listings-pagination__list">
            
            <?php if ( $paged > 1 ) : ?>
                <li class="listings-pagination__item listings-pagination__item--prev">
                    <a href="<?php echo esc_url( add_query_arg( 'listing_page', $paged - 1, $current_url ) ); ?>" class="listings-pagination__link">
                        <?php esc_html_e( '&laquo; Previous', 'geotour' ); ?>
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
                                <?php echo esc_html( $i ); ?>
                            </span>
                        <?php else : ?>
                            <a href="<?php echo esc_url( add_query_arg( 'listing_page', $i, $current_url ) ); ?>" class="listings-pagination__link">
                                <?php echo esc_html( $i ); ?>
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
                    <a href="<?php echo esc_url( add_query_arg( 'listing_page', $paged + 1, $current_url ) ); ?>" class="listings-pagination__link">
                        <?php esc_html_e( 'Next &raquo;', 'geotour' ); ?>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </nav>
    <?php
}
