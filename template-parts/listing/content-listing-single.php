<?php
/**
 * Template part for displaying single listing content
 *
 * @package Geotour_Mobile_First
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('content-article listing-single'); ?>>
    
    <header class="entry-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>
        
        <div class="entry-meta">
            <?php
            // Display listing categories
            $categories = get_the_terms(get_the_ID(), 'listing-category');
            if ($categories && !is_wp_error($categories)) {
                echo '<div class="listing-categories">';
                foreach ($categories as $category) {
                    echo '<span class="listing-category">' . esc_html($category->name) . '</span>';
                }
                echo '</div>';
            }
            
            // Display listing regions
            $regions = get_the_terms(get_the_ID(), 'listing-region');
            if ($regions && !is_wp_error($regions)) {
                echo '<div class="listing-regions">';
                echo '<strong>' . __('Region:', 'geotour') . '</strong> ';
                $region_names = array();
                foreach ($regions as $region) {
                    $region_names[] = esc_html($region->name);
                }
                echo implode(', ', $region_names);
                echo '</div>';
            }
            ?>
        </div>
    </header>

    <?php if (has_post_thumbnail()) : ?>
        <div class="post-thumbnail">
            <?php the_post_thumbnail('listing-hero', array('class' => 'listing-featured-image')); ?>
        </div>
    <?php endif; ?>

    <div class="entry-content">
        <?php the_content(); ?>
    </div>

    <footer class="entry-footer">
        <?php
        // Display listing tags
        $tags = get_the_terms(get_the_ID(), 'listing-tag');
        if ($tags && !is_wp_error($tags)) {
            echo '<div class="listing-tags">';
            echo '<strong>' . __('Tags:', 'geotour') . '</strong> ';
            foreach ($tags as $tag) {
                echo '<span class="listing-tag">' . esc_html($tag->name) . '</span>';
            }
            echo '</div>';
        }
        
        // Edit link for authorized users  
        edit_post_link(
            sprintf(
                wp_kses(
                    __('Edit <span class="screen-reader-text">%s</span>', 'geotour'),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post(get_the_title())
            ),
            '<span class="edit-link">',
            '</span>'
        );
        ?>
    </footer>

</article>