<?php
/**
 * Template part for displaying a listing in search results
 *
 * @package Geotour_Mobile_First
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('search-result search-result-listing'); ?>>
    
    <?php if (has_post_thumbnail()) : ?>
        <div class="search-result-thumbnail">
            <a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                <?php the_post_thumbnail('medium', array('alt' => get_the_title())); ?>
            </a>
        </div>
    <?php endif; ?>
    
    <div class="search-result-content">
        <header class="search-result-header">
            <div class="post-type-label">
                <span class="post-type-badge post-type-listing"><?php _e('Listing', 'geotour'); ?></span>
            </div>
            
            <?php the_title('<h2 class="search-result-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>'); ?>
            
            <div class="listing-meta">
                <?php
                // Display listing categories
                $categories = get_the_terms(get_the_ID(), 'listing-category');
                if ($categories && !is_wp_error($categories)) {
                    echo '<div class="listing-categories">';
                    $category_links = array();
                    foreach ($categories as $category) {
                        $category_links[] = '<a href="' . esc_url(geotour_get_taxonomy_listing_url('listing-category', $category->slug)) . '" class="listing-category">' . esc_html($category->name) . '</a>';
                    }
                    echo implode(', ', $category_links);
                    echo '</div>';
                }

                // Display region
                $regions = get_the_terms(get_the_ID(), 'listing-region');
                if ($regions && !is_wp_error($regions)) {
                    echo '<div class="listing-region">';
                    echo '<strong>' . __('Region:', 'geotour') . '</strong> ';
                    echo '<a href="' . esc_url(geotour_get_taxonomy_listing_url('listing-region', $regions[0]->slug)) . '">' . esc_html($regions[0]->name) . '</a>';
                    echo '</div>';
                }
                ?>
            </div>
        </header>

        <div class="search-result-excerpt">
            <?php 
            if (has_excerpt()) {
                the_excerpt();
            } else {
                echo '<p>' . wp_trim_words(get_the_content(), 30, '...') . '</p>';
            }
            ?>
        </div>

        <footer class="search-result-footer">
            <a href="<?php the_permalink(); ?>" class="read-more-link">
                <?php _e('View Listing', 'geotour'); ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14m-7-7 7 7-7 7"/>
                </svg>
            </a>
        </footer>
    </div>
    
</article>