<?php
/**
 * The main template file for the blog and archives.
 *
 * @package Geotour_Mobile_First
 */

get_header();
?>

<main id="primary" class="site-main">
    <?php
    // Load the main content template part which contains the loop and sidebar layout.
    get_template_part('template-parts/content', 'main');
    ?>
</main><!-- #primary -->

<?php
get_footer();