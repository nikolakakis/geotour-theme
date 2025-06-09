<?php
/**
 * Template part for displaying a single listing
 *
 * @package Geotour_Mobile_First
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('content-article listing-single'); ?>>
    
    <header class="entry-header">
        <div class="listing-meta">
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
            ?>
        </div>
    </header>

    <div class="entry-content">
        <?php the_content(); ?>
    </div>

    <?php
    // Display the map for this listing
    get_template_part('template-parts/listing/map-single');
    ?>

    <footer class="entry-footer">
        <?php
        // Display listing regions and tags
        $regions = get_the_terms(get_the_ID(), 'listing-region');
        if ($regions && !is_wp_error($regions)) {
            echo '<div class="listing-regions">';
            echo '<strong>' . __('Regions:', 'geotour') . '</strong> ';
            $region_names = array();
            foreach ($regions as $region) {
                $region_names[] = '<a href="' . get_term_link($region) . '">' . esc_html($region->name) . '</a>';
            }
            echo implode(', ', $region_names);
            echo '</div>';
        }

        $tags = get_the_terms(get_the_ID(), 'listing-tag');
        if ($tags && !is_wp_error($tags)) {
            echo '<div class="listing-tags">';
            echo '<strong>' . __('Tags:', 'geotour') . '</strong> ';
            $tag_names = array();
            foreach ($tags as $tag) {
                $tag_names[] = '<a href="' . get_term_link($tag) . '">' . esc_html($tag->name) . '</a>';
            }
            echo implode(', ', $tag_names);
            echo '</div>';
        }

        if (get_edit_post_link()) {
            edit_post_link(
                sprintf(
                    wp_kses(
                        __('Edit <span class="screen-reader-text">"%s"</span>', 'geotour'),
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
        }
        ?>
    </footer>

</article>