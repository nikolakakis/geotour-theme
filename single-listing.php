<?php
/**
 * Single Listing Template
 *
 * @package Geotour_Mobile_First
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        
        <?php while (have_posts()) : the_post(); ?>
            
            <!-- Display the listing map below hero, above main content -->
            <?php get_template_part('template-parts/listing/map', 'single'); ?>
            
            <!-- Main listing content -->
            <div class="main-container">
                <?php get_template_part('template-parts/listing/content-listing', 'single'); ?>
            </div>
            
        <?php endwhile; ?>
        
    </main>
</div>

<?php get_footer(); ?>