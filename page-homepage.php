<?php
/**
 * Template Name: Homepage
 * Special homepage with fullscreen hero and search form
 *
 * @package Geotour_Mobile_First
 */

get_header(); ?>

<main id="primary" class="site-main homepage homepage-template">
    <?php while (have_posts()) : the_post(); ?>
        <section class="homepage-hero">
            <?php 
            $hero_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
            if ($hero_image) : ?>
                <img src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" fetchpriority="high" loading="eager" class="hero-background-image">
            <?php endif; ?>
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <div class="container">
                    <h1 class="hero-title"><?php bloginfo('name'); ?></h1>
                    <p class="hero-description"><?php bloginfo('description'); ?></p>
                    
                    <!-- Listings Search -->
                    <h2 class="search-section-title"><?php _e('Search Listings', 'geotour'); ?></h2>
                    <p class="search-section-description"><?php _e('Explore points of interest on the interactive map', 'geotour'); ?></p>
                    <!-- Modern Search Form -->
                    <div class="homepage-search-form">
                        <form method="get" action="<?php echo esc_url(home_url('/listing')); ?>" class="listing-search-form">
                            <!-- Region Select -->
                            <div class="search-field">
                                <label for="listing-region"><?php _e('Region', 'geotour'); ?></label>
                                <select name="listing-region" id="listing-region">
                                    <option value=""><?php _e('All Regions', 'geotour'); ?></option>
                                    <?php
                                    $regions = get_terms([
                                        'taxonomy' => 'listing-region',
                                        'hide_empty' => false,
                                        'orderby' => 'name',
                                        'order' => 'ASC'
                                    ]);
                                    if (!empty($regions) && !is_wp_error($regions)) {
                                        echo geotour_build_hierarchical_options($regions, 0);
                                    }
                                    ?>
                                </select>
                            </div>
                            <!-- Category Select -->
                            <div class="search-field">
                                <label for="listing-category"><?php _e('Category', 'geotour'); ?></label>
                                <select name="listing-category" id="listing-category">
                                    <option value=""><?php _e('All Categories', 'geotour'); ?></option>
                                    <?php
                                    $categories = get_terms([
                                        'taxonomy' => 'listing-category',
                                        'hide_empty' => false,
                                        'orderby' => 'name',
                                        'order' => 'ASC'
                                    ]);
                                    $excluded_categories = [
                                        '_listing_root',
                                        'accommodation-en',
                                        'restaurant',
                                        'shopping',
                                        'services'
                                    ];
                                    if (!empty($categories) && !is_wp_error($categories)) {
                                        echo geotour_build_hierarchical_options($categories, 0, 0, $excluded_categories);
                                    }
                                    ?>
                                </select>
                            </div>
                            <!-- Text Search -->
                            <div class="search-field search-text">
                                <label for="search-text"><?php _e('Search', 'geotour'); ?></label>
                                <input type="text" name="search" id="search-text" placeholder="<?php _e('Keywords...', 'geotour'); ?>">
                            </div>
                            <button type="submit" class="search-submit" title="<?php _e('Explore Map', 'geotour'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z"/></svg>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Website Search -->
                    <h2 class="search-section-title"><?php _e('Search Website', 'geotour'); ?></h2>
                    <p class="search-section-description"><?php _e('Find articles, people, photos and all content', 'geotour'); ?></p>
                    <div class="homepage-search-form">
                        <form method="get" action="<?php echo esc_url(home_url('/')); ?>" class="listing-search-form">
                            <!-- Text Search -->
                            <div class="search-field search-text">
                                <label for="website-search-text" class="screen-reader-text"><?php _e('Search the website', 'geotour'); ?></label>
                                <input type="search" name="s" id="website-search-text" placeholder="<?php _e('Search articles, people, photos...', 'geotour'); ?>">
                            </div>
                            <button type="submit" class="search-submit" title="<?php _e('Search Website', 'geotour'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                    
                </div>
            </div>
        </section>
        <section class="homepage-content-area">
            <div class="container">
                <?php the_content(); ?>
            </div>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
?>