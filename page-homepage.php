<?php
/**
 * Template Name: Homepage
 * Special homepage with fullscreen hero and search form
 *
 * @package Geotour_Mobile_First
 */

// Hierarchical options function (temporary - should be in functions.php)
function geotour_build_hierarchical_options_temp($terms, $parent_id = 0, $level = 0, $excluded_terms = []) {
    $output = '';
    $indent = str_repeat('&mdash;&nbsp;', $level);
    
    foreach ($terms as $term) {
        if ((int)$term->parent === (int)$parent_id) {
            // Skip if this term is in the excluded list
            if (in_array($term->slug, $excluded_terms)) {
                continue;
            }
            
            // Skip if this term is a child of any excluded term
            if (geotour_is_child_of_excluded_temp($term, $terms, $excluded_terms)) {
                continue;
            }
            
            $output .= '<option value="' . esc_attr($term->slug) . '">';
            $output .= $indent . esc_html($term->name);
            $output .= '</option>';
            
            // Recursively add child terms
            $child_output = geotour_build_hierarchical_options_temp($terms, $term->term_id, $level + 1, $excluded_terms);
            if (!empty($child_output)) {
                $output .= $child_output;
            }
        }
    }
    
    return $output;
}

// Helper function to check if a term is a descendant of excluded terms
function geotour_is_child_of_excluded_temp($term, $all_terms, $excluded_slugs) {
    if ($term->parent == 0) {
        return false; // Top level term, not a child
    }
    
    // Find parent term
    foreach ($all_terms as $parent_term) {
        if ($parent_term->term_id == $term->parent) {
            // Check if parent is excluded
            if (in_array($parent_term->slug, $excluded_slugs)) {
                return true;
            }
            // Recursively check if parent is child of excluded
            return geotour_is_child_of_excluded_temp($parent_term, $all_terms, $excluded_slugs);
        }
    }
    
    return false;
}

get_header(); ?>

<main id="primary" class="site-main homepage homepage-template">
    <?php while (have_posts()) : the_post(); ?>
        
        <section class="homepage-hero">
            <?php 
            $hero_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
            if ($hero_image) : ?>
                <div class="hero-background" style="background-image: url('<?php echo esc_url($hero_image); ?>');">
                    <div class="hero-overlay"></div>
                </div>
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
                                        
                                        if (!empty($regions) && !is_wp_error($regions)) :
                                            // Try hierarchical first, fallback to simple
                                            if (function_exists('geotour_build_hierarchical_options')) {
                                                echo geotour_build_hierarchical_options($regions, 0);
                                            } else {
                                                // Use temporary function
                                                echo geotour_build_hierarchical_options_temp($regions, 0);
                                            }
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
                                        
                                        if (!empty($categories) && !is_wp_error($categories)) :
                                            // Define excluded category slugs and their children
                                            $excluded_categories = [
                                                '_listing_root',
                                                'accommodation-en',
                                                'restaurant', 
                                                'shopping',
                                                'services'
                                            ];
                                            
                                            // Try hierarchical first, fallback to simple
                                            if (function_exists('geotour_build_hierarchical_options')) {
                                                echo geotour_build_hierarchical_options($categories, 0, 0, $excluded_categories);
                                            } else {
                                                // Use temporary function with exclusions
                                                echo geotour_build_hierarchical_options_temp($categories, 0, 0, $excluded_categories);
                                            }
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
        
    <?php endwhile; ?>
</main>

<?php
get_footer();
?>