<?php
/**
 * Theme Setup and Support
 * * @package Geotour_Mobile_First
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup function
 */
function geotour_theme_setup() {
    // Add theme support features
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));
    add_theme_support('customize-selective-refresh-widgets');
    add_theme_support('responsive-embeds');
    add_theme_support('title-tag');
    
    // Navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'geotour'),
        'mobile'  => __('Mobile Menu', 'geotour'),
        'footer'  => __('Footer Menu', 'geotour'),
    ));

    // Custom image sizes for listings
    add_image_size('listing-thumbnail', 400, 300, true);
    add_image_size('listing-hero', 1200, 600, true);
    add_image_size('listing-card', 600, 400, true);
}
add_action('after_setup_theme', 'geotour_theme_setup');

/**
 * Load theme textdomain correctly.
 */
function geotour_load_textdomain() {
    // Make theme available for translation
    load_theme_textdomain('geotour', GEOTOUR_THEME_DIR . '/languages');
}
add_action('init', 'geotour_load_textdomain');


/**
 * Register widget areas
 */
function geotour_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'geotour'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here.', 'geotour'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widgets', 'geotour'),
        'id'            => 'footer-widgets',
        'description'   => __('Footer widget area.', 'geotour'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(
        array(
            'name'          => esc_html__('Blog Sidebar', 'geotour'),
            'id'            => 'sidebar-blog',
            'description'   => esc_html__('Add widgets here to appear in your blog sidebar.', 'geotour'),
            'before_widget' => '<section id="%1$s" class="widget %2$s sidebar-section">',
            'after_widget'  => '</section>',
            'before_title'  => '<h3 class="widget-title sidebar-title">',
            'after_title'   => '</h3>',
        )
    );
}
add_action('widgets_init', 'geotour_widgets_init');

/**
 * Custom document title function
 */
function geotour_document_title_parts($title) {
    // Customize titles for different page types
    if (is_singular('listing')) {
        // For listings, add location info if available
        $post_id = get_the_ID();
        $regions = wp_get_post_terms($post_id, 'listing-region');
        if (!empty($regions) && !is_wp_error($regions)) {
            $region_name = $regions[0]->name;
            $title['title'] = get_the_title() . ' - ' . $region_name;
        }
    } elseif (is_page_template('page-listing.php')) {
        // For the big map page
        $title['title'] = __('Discover Crete Map', 'geotour');
        $title['tagline'] = get_bloginfo('description');
    } elseif (is_page_template('page-homepage.php')) {
        // For homepage template
        $title['title'] = get_the_title();
        $title['tagline'] = get_bloginfo('description');
    } elseif (is_404()) {
        // For 404 pages
        $title['title'] = __('Page Not Found', 'geotour');
        $title['tagline'] = get_bloginfo('name');
    }
    
    return $title;
}
add_filter('document_title_parts', 'geotour_document_title_parts');

/**
 * Fallback title function for older WordPress versions
 */
function geotour_render_title() {
    echo '<title>' . wp_get_document_title() . '</title>' . "\n";
}

// Only add this if WordPress doesn't support title-tag
if (!current_theme_supports('title-tag')) {
    add_action('wp_head', 'geotour_render_title');
}