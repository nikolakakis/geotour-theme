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
            if ($hero_image && !wp_is_mobile()) : ?>
                <img src="<?php echo esc_url($hero_image); ?>" 
                     alt="<?php echo esc_attr(get_the_title()); ?>"
                     fetchpriority="high"
                     loading="eager"
                     class="hero-background-image">
                <div class="hero-overlay"></div>
            <?php endif; ?>
            
            <div class="hero-content">
                <div class="container">
                    <h1 class="hero-title"><?php the_title(); ?></h1>
                    
                    <?php 
                    // Get Yoast meta description or fall back to excerpt
                    $meta_description = '';
                    if (class_exists('WPSEO_Meta')) {
                        $meta_description = get_post_meta(get_the_ID(), '_yoast_wpseo_metadesc', true);
                    }
                    if (empty($meta_description) && has_excerpt()) {
                        $meta_description = get_the_excerpt();
                    }
                    
                    if (!empty($meta_description)) : ?>
                        <p class="hero-description"><?php echo esc_html($meta_description); ?></p>
                    <?php endif; ?>
                    
                    <div class="homepage-search-form">
                        <form method="get" action="<?php echo esc_url(home_url('/listing')); ?>" class="listing-search-form">
                            <div class="search-fields">
                                
                                <!-- Region Select -->
                                <div class="search-field">
                                    <label for="listing-region"><?php _e('Region', 'geotour'); ?></label>
                                    <select name="listing-region" id="listing-region">
                                        <option value=""><?php _e('All Regions', 'geotour'); ?></option>
                                        <?php
                                        $regions = get_terms([
                                            'taxonomy' => 'listing-region',
                                            'hide_empty' => false, // Show empty terms for testing
                                            'orderby' => 'name',
                                            'order' => 'ASC'
                                        ]);
                                        
                                        // Debug: Check if we have regions
                                        if (current_user_can('manage_options')) {
                                            echo '<!-- Debug: Found ' . (is_array($regions) ? count($regions) : 0) . ' regions -->';
                                        }
                                        
                                        if (!empty($regions) && !is_wp_error($regions)) :
                                            // Use the global function
                                            echo geotour_build_hierarchical_options($regions, 0);
                                        else :
                                            echo '<option disabled>No regions found</option>';
                                        endif;
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
                                            'hide_empty' => false, // Show empty terms for testing
                                            'orderby' => 'name',
                                            'order' => 'ASC'
                                        ]);
                                        
                                        // Debug: Check if we have categories
                                        if (current_user_can('manage_options')) {
                                            echo '<!-- Debug: Found ' . (is_array($categories) ? count($categories) : 0) . ' categories -->';
                                        }
                                        
                                        if (!empty($categories) && !is_wp_error($categories)) :
                                            // Define excluded category slugs and their children
                                            $excluded_categories = [
                                                '_listing_root',
                                                'accommodation-en',
                                                'restaurant', 
                                                'shopping',
                                                'services'
                                            ];
                                            
                                            // Use the global function
                                            echo geotour_build_hierarchical_options($categories, 0, 0, $excluded_categories);
                                        else :
                                            echo '<option disabled>No categories found</option>';
                                        endif;
                                        ?>
                                    </select>
                                </div>
                                
                                <!-- Text Search -->
                                <div class="search-field search-text">
                                    <label for="search-text"><?php _e('Search', 'geotour'); ?></label>
                                    <input type="text" name="search" id="search-text" placeholder="<?php _e('Keywords...', 'geotour'); ?>">
                                </div>
                                
                            </div>
                            
                            <button type="submit" class="search-submit">
                                <span><?php _e('Explore Map', 'geotour'); ?></span>
                                <i class="search-icon">🔍</i>
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