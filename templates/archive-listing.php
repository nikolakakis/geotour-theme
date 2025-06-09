<?php
/**
 * The template for displaying listing archives
 *
 * @package Geotour_Mobile_First
 */

get_header();

// Add archive-grid class to body for grid layout
add_filter('body_class', function($classes) {
    $classes[] = 'archive-grid';
    return $classes;
});
?>

<div class="main-container archive-grid">
    <?php if (have_posts()) : ?>
        
        <header class="page-header">
            <?php
            the_archive_title('<h1 class="page-title">', '</h1>');
            the_archive_description('<div class="archive-description">', '</div>');
            ?>
        </header>

        <?php
        // Include the archive map
        get_template_part('template-parts/listing/map-archive');
        ?>

        <?php
        /* Start the Loop */
        while (have_posts()) :
            the_post();
            get_template_part('template-parts/listing/content-listing-archive');
        endwhile;

        // Previous/next page navigation
        the_posts_navigation(array(
            'prev_text' => __('← Older listings', 'geotour'),
            'next_text' => __('Newer listings →', 'geotour'),
        ));
        ?>
        
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</div>

<?php
get_footer();
?>