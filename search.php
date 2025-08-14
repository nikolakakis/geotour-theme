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
                        echo '<nav class="search-pagination" aria-label="' . esc_attr__('Search results pages', 'geotour') . '">';
                        
                        $pagination_args = array(
                            'prev_text' => '<span aria-hidden="true">←</span> ' . __('Previous Results', 'geotour'),
                            'next_text' => __('Next Results', 'geotour') . ' <span aria-hidden="true">→</span>',
                            'before_page_number' => '<span class="screen-reader-text">' . __('Page', 'geotour') . ' </span>',
                            'mid_size' => 2,
                            'end_size' => 1,
                        );
                        
                        // Use paginate_links for better pagination
                        $pagination = paginate_links(array_merge($pagination_args, array(
                            'total' => $wp_query->max_num_pages,
                            'current' => max(1, get_query_var('paged')),
                            'format' => '?paged=%#%',
                            'add_args' => array('s' => get_search_query()),
                            'type' => 'array'
                        )));
                        
                        if ($pagination) {
                            echo '<ul class="pagination-list">';
                            foreach ($pagination as $page_link) {
                                echo '<li class="pagination-item">' . $page_link . '</li>';
                            }
                            echo '</ul>';
                        }
                        
                        // Fallback to simple navigation if paginate_links fails
                        if (!$pagination) {
                            the_posts_navigation($pagination_args);
                        }
                        
                        echo '</nav>';
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