<?php
/**
 * The template for displaying single listing posts
 * 
 * @package Geotour_Mobile_First
 */

get_header();
?>

<main id="primary" class="site-main listing-single-page">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            
            <!-- 1. hero-section listing-hero-section -->
            <?php get_template_part('template-parts/listing/hero-listing'); ?>
            
            <!-- 2. listing-map-full-section - 📍 Over-the-content Section - Excerpt + map overlay -->
            <?php get_template_part('template-parts/listing/map-single'); ?>
            
            <!-- 3. listing-details-full-section - Details Sections - Site access, details, weather forecast (100vw) -->
            <?php get_template_part('template-parts/listing/details-sections'); ?>
            
            <!-- Custom map section from ACF field -->
            <?php get_template_part('template-parts/listing/map-custom'); ?>
            
            <!-- 4. Main Content - Full content, virtual tour, nearest listings -->
            <div class="main-container">
                <?php get_template_part('template-parts/listing/content-listing-single'); ?>
            </div>
            
        <?php endwhile; ?>
    <?php else : ?>
        <div class="main-container">
            <?php get_template_part('template-parts/content', 'none'); ?>
        </div>
    <?php endif; ?>
</main>

<?php
get_footer();
?>