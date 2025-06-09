<?php
/**
 * Template part for displaying related listings
 *
 * @package Geotour_Mobile_First
 */

// Get related listings based on categories or regions
$related_args = array(
    'post_type' => 'listing',
    'posts_per_page' => 3,
    'post__not_in' => array(get_the_ID()),
    'meta_query' => array(
        array(
            'key' => 'latitude',
            'compare' => 'EXISTS'
        ),
        array(
            'key' => 'longitude', 
            'compare' => 'EXISTS'
        )
    )
);

// Try to get listings from same categories first
$categories = get_the_terms(get_the_ID(), 'listing-category');
if ($categories && !is_wp_error($categories)) {
    $related_args['tax_query'] = array(
        array(
            'taxonomy' => 'listing-category',
            'field' => 'term_id',
            'terms' => wp_list_pluck($categories, 'term_id')
        )
    );
}

$related_query = new WP_Query($related_args);

if ($related_query->have_posts()) :
?>
<section class="related-listings-section">
    <h3><?php _e('Related Listings', 'geotour'); ?></h3>
    <div class="related-listings-grid">
        <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
            <?php get_template_part('template-parts/listing/content-listing-archive'); ?>
        <?php endwhile; ?>
    </div>
</section>
<?php
wp_reset_postdata();
endif;
?>