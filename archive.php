<?php
/**
 * The template for displaying archive pages
 *
 * @package Geotour_Mobile_First
 */

get_header();
echo "<!-- DEBUG: archive.php - After get_header() -->";

// Add archive-grid class to body for grid layout
add_filter('body_class', function($classes) {
    $classes[] = 'archive-grid';
    return $classes;
});

echo "<!-- DEBUG: archive.php - Before get_template_part('template-parts/content', 'main') -->";
// Include main content template part
get_template_part('template-parts/content', 'main');
echo "<!-- DEBUG: archive.php - After get_template_part('template-parts/content', 'main') -->";

get_footer();
