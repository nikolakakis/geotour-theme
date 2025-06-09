<?php
/**
 * Template part for displaying main content area
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Geotour_Mobile_First
 */
?>

<div class="main-container <?php echo (is_archive() || is_home()) ? 'archive-grid' : ''; ?>">
    <?php echo "<!-- DEBUG: content-main.php loaded. Checking have_posts(). -->"; ?>
    <?php if (have_posts()) : ?>
            <?php echo "<!-- DEBUG: have_posts() is true. -->"; ?>
            <?php if (is_home() && !is_front_page()) : ?>
                <header class="page-header">
                    <h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
                </header>
            <?php endif; ?>

            <?php if (is_archive()) : ?>
                <header class="page-header">
                    <?php
                    the_archive_title('<h1 class="page-title">', '</h1>');
                    the_archive_description('<div class="archive-description">', '</div>');
                    ?>
                </header>
            <?php endif; ?>

            <?php
            /* Start the Loop */
            while (have_posts()) :
                the_post();
                echo "<!-- DEBUG: In while loop. Post ID: " . get_the_ID() . ". Post Type: " . get_post_type() . " -->";
                /*
                 * Include the Post-Type-specific template for the content.
                 * If you want to override this in a child theme, then include a file
                 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
                 */
                if (is_singular()) {
                    echo "<!-- DEBUG: Calling get_template_part for content-single -->";
                    get_template_part('template-parts/content', 'single');
                } else {
                    echo "<!-- DEBUG: Calling get_template_part for content-" . get_post_type() . " -->";
                    get_template_part('template-parts/content', get_post_type());
                }

            endwhile;

           // Previous/next page navigation for archives
            if (!is_singular()) {
                the_posts_navigation(array(
                    'prev_text' => __('← Older posts', 'geotour'),
                    'next_text' => __('Newer posts →', 'geotour'),
                ));
            }
            ?>
    <?php else : // Corrected PHP syntax for else block
            echo "<!-- DEBUG: have_posts() is false. Calling content-none. -->";
            get_template_part('template-parts/content', 'none');
    endif; ?>
    <?php echo "<!-- DEBUG: End of content-main.php -->"; ?>
</div>
