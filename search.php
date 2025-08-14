<?php
/**
 * The template for displaying search results pages
 * 
 * @package Geotour_Mobile_First
 */

get_header();

// Get the search query
$search_query = get_search_query();
?>

<!-- Search Hero Section -->
<?php get_template_part('template-parts/search/search-hero'); ?>

<div class="content-wrapper">
    <div class="content-no-sidebar">
        <div class="main-content">
            
            <!-- Search Results Header -->
            <?php get_template_part('template-parts/search/search-results-header'); ?>
            
            <main id="primary" class="site-main">
                
                <?php if (have_posts()) : ?>
                    
                    <div class="search-results-container">
                        <?php
                        /* Start the Loop */
                        while (have_posts()) :
                            the_post();
                            
                            // Use different template parts based on post type
                            if (get_post_type() === 'listing') {
                                get_template_part('template-parts/search/content-search-listing');
                            } else {
                                get_template_part('template-parts/search/content-search-post');
                            }
                            
                        endwhile;
                        
                        // Pagination
                        the_posts_navigation(array(
                            'prev_text' => __('← Previous Results', 'geotour'),
                            'next_text' => __('Next Results →', 'geotour'),
                        ));
                        ?>
                    </div>
                    
                <?php else : ?>
                    
                    <?php get_template_part('template-parts/search/content-search-none'); ?>
                    
                <?php endif; ?>
                
            </main><!-- #main -->
            
            <!-- Search Results Footer Summary -->
            <?php get_template_part('template-parts/search/search-results-footer'); ?>
            
        </div>
    </div>
</div>

<?php
get_footer();
?>