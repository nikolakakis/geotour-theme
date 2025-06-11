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

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        
        <?php while (have_posts()) : the_post(); ?>
            
            <!-- Main listing content -->
            <div class="main-container">
                <?php get_template_part('template-parts/listing/content-listing', 'single'); ?>
            </div>
            
        <?php endwhile; ?>
        
    </main>
</div>

<?php get_footer(); ?>