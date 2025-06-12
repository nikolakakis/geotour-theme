<?php
/**
 * Single Listing Template
 *
 * @package Geotour_Mobile_First
 */

get_header(); ?>

<!-- Listing-specific hero section (replaces global hero) -->
<?php get_template_part('template-parts/listing/hero', 'listing'); ?>

<!-- 100vw Map section -->
<?php get_template_part('template-parts/listing/map', 'single'); ?>

<!-- 100vw Details section -->
<?php include(get_template_directory() . '/template-parts/listing/details-sections.php'); ?>

<?php while (have_posts()) : the_post(); ?>
    
    <!-- Main listing content -->
    <div class="main-container">
        <?php get_template_part('template-parts/listing/content-listing', 'single'); ?>
        
        <?php
        // If comments are open or there are comments, load the comment template
        if (comments_open() || get_comments_number()) :
            comments_template();
        endif;
        ?>
    </div>
    
<?php endwhile; ?>

        </main><!-- #main -->
    </div><!-- #primary -->

<?php get_footer(); ?>