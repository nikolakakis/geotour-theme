<?php
/**
 * Template part for displaying listing in archive loops
 *
 * @package Geotour_Mobile_First
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('content-article archive-item listing-archive'); ?> data-no-auto-ads="true">
    
    <?php if (has_post_thumbnail()) : ?>
        <div class="post-thumbnail">
            <a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                <?php the_post_thumbnail('medium', array('alt' => get_the_title())); ?>
            </a>
        </div>
    <?php endif; ?>
    
    <header class="entry-header">
        <?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>'); ?>
        
        <div class="listing-meta">
            <?php
            // Display listing categories
            $categories = get_the_terms(get_the_ID(), 'listing-category');
            if ($categories && !is_wp_error($categories)) {
                echo '<div class="listing-categories">';
                foreach ($categories as $category) {
                    echo '<a href="' . esc_url(geotour_get_taxonomy_listing_url('listing-category', $category->slug)) . '" class="listing-category">' . esc_html($category->name) . '</a>';
                }
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

    <div class="entry-summary">
        <?php the_excerpt(); ?>
    </div>

    <footer class="entry-footer">
        <a href="<?php the_permalink(); ?>" class="read-more-link">
            <?php _e('Read More', 'geotour'); ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14m-7-7 7 7-7 7"/>
            </svg>
        </a>
    </footer>
    
</article>