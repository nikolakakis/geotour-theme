<?php
/**
 * The template for displaying single listing posts
 * 
 * @package Geotour_Mobile_First
 */

get_header();
?>

<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        
        <!-- Hero Section -->
        <?php get_template_part('template-parts/listing/hero-listing'); ?>
        
        <!-- Map Section (100vw) -->
        <?php get_template_part('template-parts/listing/map-single'); ?>
        
        <!-- Listing Details Sections (100vw) - Over the content 2 -->
        <?php get_template_part('template-parts/listing/details-sections'); ?>
        
        <!-- Custom Map Section (100vw) - ACF controlled -->
        <?php get_template_part('template-parts/listing/map-custom'); ?>
        
        <!-- Main Content Container with Sidebar -->
        <?php get_template_part('template-parts/listing/content-listing-single'); ?>
        
        <?php
        // If comments are open or there are comments, load the comment template
        if (comments_open() || get_comments_number()) :
            comments_template();
        endif;
        ?>
        
    <?php endwhile; ?>
<?php else : ?>
    <div class="content-wrapper">
        <div class="content-no-sidebar">
            <div class="main-content">
                <?php get_template_part('template-parts/content', 'none'); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
get_footer();
?>