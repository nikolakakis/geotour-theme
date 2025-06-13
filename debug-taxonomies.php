<?php
/**
 * Debug Homepage - shows taxonomy information
 * Remove this file after debugging
 */

// Check if taxonomies exist
echo '<h3>Debug Info:</h3>';
echo '<p><strong>Listing Region taxonomy exists:</strong> ' . (taxonomy_exists('listing-region') ? 'YES' : 'NO') . '</p>';
echo '<p><strong>Listing Category taxonomy exists:</strong> ' . (taxonomy_exists('listing-category') ? 'YES' : 'NO') . '</p>';

// Get terms for both taxonomies
$regions = get_terms(['taxonomy' => 'listing-region', 'hide_empty' => false]);
$categories = get_terms(['taxonomy' => 'listing-category', 'hide_empty' => false]);

echo '<p><strong>Regions found:</strong> ' . (is_wp_error($regions) ? 'ERROR: ' . $regions->get_error_message() : count($regions)) . '</p>';
echo '<p><strong>Categories found:</strong> ' . (is_wp_error($categories) ? 'ERROR: ' . $categories->get_error_message() : count($categories)) . '</p>';

if (!empty($regions) && !is_wp_error($regions)) {
    echo '<p><strong>Region names:</strong> ';
    foreach ($regions as $region) {
        echo esc_html($region->name) . ' (' . esc_html($region->slug) . ', parent: ' . $region->parent . '), ';
    }
    echo '</p>';
}

if (!empty($categories) && !is_wp_error($categories)) {
    echo '<p><strong>Category names:</strong> ';
    foreach ($categories as $category) {
        echo esc_html($category->name) . ' (' . esc_html($category->slug) . ', parent: ' . $category->parent . '), ';
    }
    echo '</p>';
}

echo '<p><strong>Hierarchical function exists:</strong> ' . (function_exists('geotour_build_hierarchical_options') ? 'YES' : 'NO') . '</p>';

// Test the hierarchical function with regions
if (!empty($regions) && !is_wp_error($regions) && function_exists('geotour_build_hierarchical_options')) {
    echo '<h4>Hierarchical Regions Test:</h4>';
    echo '<select style="width: 100%; background: white; color: black; padding: 5px;">';
    echo '<option value="">All Regions</option>';
    echo geotour_build_hierarchical_options($regions, 0);
    echo '</select>';
}

// Test with categories
if (!empty($categories) && !is_wp_error($categories) && function_exists('geotour_build_hierarchical_options')) {
    echo '<h4>Hierarchical Categories Test:</h4>';
    echo '<select style="width: 100%; background: white; color: black; padding: 5px;">';
    echo '<option value="">All Categories</option>';
    echo geotour_build_hierarchical_options($categories, 0);
    echo '</select>';
}
?>