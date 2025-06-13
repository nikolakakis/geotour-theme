<?php
/**
 * The template for displaying single listing posts
 * 
 * MOVED TO ROOT: This file should be moved to /single-listing.php
 * This template is deprecated - use the root single-listing.php instead
 *
 * @package Geotour_Mobile_First
 */

// Redirect to root template if it exists
if (file_exists(get_template_directory() . '/single-listing.php')) {
    include(get_template_directory() . '/single-listing.php');
    return;
}

get_header();
?>

<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        
        <!-- Hero Section -->
        <?php get_template_part('template-parts/listing/hero-listing'); ?>
        
        <!-- Main Content Container -->
        <div class="main-container">
            <?php get_template_part('template-parts/listing/content-listing-single'); ?>
            
            <?php
            // If comments are open or there are comments, load the comment template
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            ?>
        </div>
        
        <!-- Map Section (100vw) -->
        <?php get_template_part('template-parts/listing/map-single'); ?>
        
        <!-- Listing Details Sections (100vw) - Over the content 2 -->
        <?php get_template_part('template-parts/listing/details-sections'); ?>
        
    <?php endwhile; ?>
<?php else : ?>
    <div class="main-container">
        <?php get_template_part('template-parts/content', 'none'); ?>
    </div>
<?php endif; ?>

<?php
get_footer();
?>