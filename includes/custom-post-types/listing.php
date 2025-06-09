<?php
/**
 * Register Listing Custom Post Type and Taxonomies
 *
 * @package Geotour_Mobile_First
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! function_exists( 'bt_create_listing' ) ) {
    /**
     * Register Listing CPT and associated taxonomies.
     */
    function bt_create_listing() {
        
        register_post_type( 'listing',
            array(
                'labels' => array(
                    'name'          => __( 'Listing', 'geotour' ), // Changed text domain
                    'singular_name' => __( 'Listing Item', 'geotour' ) // Changed text domain
                ),
                'public'        => true,
                'has_archive'   => true,
                'menu_position' => 5,
                'supports'      => array( 'title', 'editor', 'revisions', 'thumbnail', 'author', 'comments', 'excerpt' ),
                'rewrite'       => array( 'with_front' => false, 'slug' => 'listing' )
            )
        );

        register_taxonomy( 
            'listing-category', 
            'listing', 
            array( 
                'hierarchical' => true, 
                'label' => __( 'Categories', 'geotour' ), // Changed text domain
                'singular_name' => __( 'Category', 'geotour' ), // Changed text domain
                'show_admin_column' => true,
                'rewrite' => array( 'slug' => 'listing-category' ) // Added rewrite slug
            ) 
        );

        register_taxonomy( 
            'listing-region', 
            'listing', 
            array( 
                'hierarchical' => true, 
                'label' => __( 'Regions', 'geotour' ), // Changed text domain
                'singular_name' => __( 'Region', 'geotour' ), // Changed text domain
                'show_admin_column' => true,
                'rewrite' => array( 'slug' => 'listing-region' ) // Added rewrite slug
            ) 
        );

        register_taxonomy( 
            'listing-tag', 
            'listing', 
            array( 
                'hierarchical' => false, 
                'label' => __( 'Tags', 'geotour' ), // Changed text domain
                'singular_name' => __( 'Tag', 'geotour' ), // Changed text domain
                'rewrite' => array( 'slug' => 'listing-tag' ) // Added rewrite slug
            ) 
        );

        // Note: The RWMB_Core related class extension is specific to MetaBox plugin.
        // If MetaBox is not a planned part of this theme, this part might be irrelevant
        // or need to be adapted if using a different custom fields solution like ACF.
        if ( class_exists( 'RWMB_Core' ) ) {
            class BT_RWMB_Core extends RWMB_Core {
                public function init() {
                    $this->register_meta_boxes();
                }
            }
            // It's unusual to instantiate this class directly here.
            // MetaBox typically handles its initialization.
            // Consider if this instantiation is necessary or if it was part of
            // a specific setup in the old theme.
            // new BT_RWMB_Core(); 
        }
    }
}
add_action( 'init', 'bt_create_listing' );