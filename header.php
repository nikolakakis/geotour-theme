<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Geotour_Mobile_First
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php
    // Add proper title tag support
    if (!function_exists('_wp_render_title_tag')) {
        // Fallback for older WordPress versions
        echo '<title>' . wp_get_document_title() . '</title>' . "\n";
    }
    
    // The following is an example of how to correctly get the Yoast meta description.
    // You should find the similar code in your header and replace it.
    $meta_desc = '';
    if (function_exists('YoastSEO')) {
        // This is the new, correct way to get the meta description.
        $meta_desc = YoastSEO()->meta->for_current_page()->description;
    }
    
    // Fallback if Yoast description is not available.
    if (empty($meta_desc)) {
        if (is_singular()) {
            $meta_desc = get_the_excerpt();
        } else {
            $meta_desc = get_bloginfo('description');
        }
    }

    // Only output the tag if a description exists.
    if (!empty($meta_desc)) {
        echo '<meta name="description" content="' . esc_attr(strip_tags($meta_desc)) . '">';
    }
    
    // Add Open Graph tags for better social sharing
    if (is_singular()) {
        $post_id = get_the_ID();
        echo '<meta property="og:title" content="' . esc_attr(get_the_title($post_id)) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($meta_desc) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_attr(get_permalink($post_id)) . '">' . "\n";
        echo '<meta property="og:type" content="article">' . "\n";
        
        // Featured image for OG
        $featured_image = get_the_post_thumbnail_url($post_id, 'large');
        if ($featured_image) {
            echo '<meta property="og:image" content="' . esc_attr($featured_image) . '">' . "\n";
        }
    }
    ?>
    
    <?php
    // Output header ad scripts if needed
    get_template_part('template-parts/ads/header-ads');
    ?>
    
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- SVG Clip Path Definition for Header -->
<svg width="0" height="0" class="absolute" style="position: absolute; width: 0; height: 0;">
    <defs>
        <clipPath id="header-clip-path" clipPathUnits="objectBoundingBox">
            <path d="M 0,0 H 1 V 0.75 C 0.75,1 0.25,1 0,0.75 Z"></path>
        </clipPath>
    </defs>
</svg>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'geotour'); ?></a>    <!-- New Main Header -->
    <header class="main-header" data-no-auto-ads="true">
        <div class="header-container">            <!-- Left Section: Social Icons -->
            

            <!-- Center Section: Logo -->
            <div class="header-center-section animate-on-load">
                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                    <img src="/wp-content/uploads/2024/10/geotour-logo-landscape.png" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="logo-image">
                </a>
            </div>

            <!-- Right Section: Hamburger Menu Button -->
            
        </div> <!-- .header-container -->
        <?php if ( !wp_is_mobile() ) { get_template_part('template-parts/header/desktop-mega-menu'); } ?>
    </header>
    <div class="header-right-section">
        <button id="hamburger-icon" class="hamburger-button" aria-label="<?php esc_attr_e('Open Menu', 'geotour'); ?>" aria-expanded="false" aria-controls="fullscreen-menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
    <!-- Full Screen Menu -->
    <nav id="fullscreen-menu" class="fullscreen-menu" aria-hidden="true" data-no-auto-ads="true">
        <?php
        // Optional: Branding inside the menu (Example)
        if (get_bloginfo('name')) :
            echo '<div class="site-branding-menu">';
            if (is_front_page() && is_home()) :
                echo '<h1 class="site-title"><a href="' . esc_url(home_url('/')) . '" rel="home">' . esc_html(get_bloginfo('name')) . '</a></h1>';
            else :
                echo '<p class="site-title"><a href="' . esc_url(home_url('/')) . '" rel="home">' . esc_html(get_bloginfo('name')) . '</a></p>';
            endif;
            $site_description = get_bloginfo('description', 'display');
            if ($site_description || is_customize_preview()) :
                echo '<p class="site-description">'. esc_html($site_description) .'</p>';
            endif;
            echo '</div>';
        endif;
        
        // Always output the mobile menu - CSS will control visibility
        wp_nav_menu([
            'theme_location' => 'primary',
            'menu_class'     => 'menu',
            'container'      => false,
            'walker'         => new Geotour_Accordion_Menu_Walker()
        ]);
        ?>
        
        <!-- Search Form at Bottom of Menu -->
        <div class="menu-search-section">
            <h3 class="menu-search-title"><?php esc_html_e('Search', 'geotour'); ?></h3>
            <form role="search" method="get" class="menu-search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="text" name="s" placeholder="<?php esc_attr_e('Search the website...', 'geotour'); ?>">
                <button type="submit" class="search-submit" aria-label="<?php esc_attr_e('Search', 'geotour'); ?>" title="<?php esc_attr_e('Search the website', 'geotour'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </button>
                <?php if (function_exists('pll_current_language')) : ?>
                    <input type="hidden" name="lang" value="<?php echo esc_attr(function_exists('pll_current_language') ? pll_current_language() : 'en'); ?>">
                <?php endif; ?>
            </form>
        </div>
    </nav>
    <?php
    // Include hero section on appropriate pages (but not on single listings or homepage template)
    if ((is_home() || is_front_page() || is_singular() || is_archive()) 
        && !is_singular('listing') 
        && !is_page_template('page-homepage.php')) {
        get_template_part('template-parts/hero');
    }
    ?>

<div id="primary" class="content-area">
        <main id="main" class="site-main">
            <?php // Main page content starts here ?>