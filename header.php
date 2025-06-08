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
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'geotour'); ?></a>

    <?php /*
    <header id="masthead" class="site-header">
        <div class="site-branding">
            <?php
            if (is_front_page() && is_home()) :
                ?>
                <h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
                <?php
            else :
                ?>
                <p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></p>
                <?php
            endif;
            $description = get_bloginfo('description', 'display');
            if ($description || is_customize_preview()) :
                ?>
                <p class="site-description"><?php echo $description; ?></p>
            <?php endif; ?>
        </div><!-- .site-branding -->

        <nav id="site-navigation" class="main-navigation">
            <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e('Primary Menu', 'geotour'); ?></button>
            <?php
            // wp_nav_menu(
            //     array(
            //         'theme_location' => 'primary',
            //         'menu_id'        => 'primary-menu',
            //     )
            // );
            ?>
        </nav><!-- #site-navigation -->
    </header><!-- #masthead -->
    */ ?>

    <!-- Hamburger Menu Icon -->
    <button id="hamburger-icon" class="hamburger-button" aria-label="<?php esc_attr_e('Open Menu', 'geotour'); ?>" aria-expanded="false" aria-controls="fullscreen-menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Full Screen Menu -->
    <nav id="fullscreen-menu" class="fullscreen-menu" aria-hidden="true">
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

        wp_nav_menu(
            array(
                'theme_location' => 'primary', // Make sure this menu location is registered
                'menu_id'        => 'primary-menu-list', // ID for the UL
                'container'      => false, // No div container around the ul
                'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                'walker'         => new Geotour_Accordion_Menu_Walker(),
            )
        );
        ?>
    </nav>
    
    <!-- Animated Footer for Social Media Icons -->
    <footer class="menu-footer">
        <a href="#" class="social-icon" aria-label="Facebook">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
        </a>
        <a href="#" class="social-icon" aria-label="Instagram">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.011 3.584-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.85s.011-3.584.069-4.85c.149-3.225 1.664-4.771 4.919 4.919 1.266-.057 1.644-.069 4.85-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 6.78-2.618 6.98-6.98.059-1.281.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98-1.281-.059-1.689-.073-4.948-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.162 6.162 6.162 6.162-2.759 6.162-6.162-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4s1.791-4 4-4 4 1.79 4 4-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44 1.441-.645 1.441-1.44-.645-1.44-1.441-1.44z"/></svg>
        </a>
         <a href="#" class="social-icon" aria-label="TripAdvisor">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm0 1c6.071 0 11 4.929 11 11s-4.929 11-11 11-11-4.929-11-11 4.929-11 11-11zm-4.391 7.288c-.825 0-1.491.667-1.491 1.491s.667 1.491 1.491 1.491c.825 0 1.491-.667 1.491-1.491s-.667-1.491-1.491-1.491zm4.353 0c-.825 0-1.491.667-1.491 1.491s.667 1.491 1.491 1.491c.825 0 1.491-.667 1.491-1.491s-.667-1.491-1.491-1.491zm4.429 0c-.825 0-1.491.667-1.491 1.491s.667 1.491 1.491 1.491c.825 0 1.491-.667 1.491-1.491s-.667-1.491-1.491-1.491zm-8.82 5.712c-1.375 2.945-1.375 4.544 0 7.429h3.084c.642-1.267 1.056-2.585 1.056-3.714s-.414-2.448-1.056-3.714h-3.084zm8.857 0c-1.375 2.945-1.375 4.544 0 7.429h3.084c.642-1.267 1.056-2.585 1.056-3.714s-.414-2.448-1.056-3.714h-3.084z"/></svg>
        </a>
    </footer>

    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <?php // Main page content starts here ?>