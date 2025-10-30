<?php
/**
 * The template for displaying the footer
 *
 * @package Geotour_Mobile_First
 */

// Check if we're on the listing map page
$is_map_page = is_page_template('page-listing.php');
?>

</div><!-- #content -->

<?php if (!$is_map_page): ?>
    <?php get_template_part('template-parts/footer/modern'); ?>
<?php endif; ?>

<?php
// Include anchor ad if enabled
get_template_part('template-parts/ads/anchor-ad');
?>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>