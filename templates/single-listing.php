<?php
/**
 * The template for displaying single listing posts
 *
 * @package Geotour_Mobile_First
 */

get_header();
?>

<div class="main-container">
    <!-- Simple test map container -->
    <div style="margin: 2rem 0;">
        <h2>Test Map</h2>
        <div id="listing-map" class="geotour-map-container"></div>
    </div>
    
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php get_template_part('template-parts/listing/content-listing-single'); ?>
            
            <?php
            // If comments are open or there are comments, load the comment template
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            ?>
            
        <?php endwhile; ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</div>

<?php
get_footer();
?>