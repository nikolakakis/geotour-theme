<?php
/**
 * Map Icon Management
 * 
 * Handles dynamic map icon selection based on listing categories
 * and their associated ACF category_map_icon field
 * 
 * @package Geotour_Mobile_First
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the appropriate map icon for a listing based on its categories
 * 
 * @param int $listing_id The listing post ID
 * @return array Map icon configuration with URL and settings
 */
function geotour_get_listing_map_icon($listing_id) {
    // Default fallback icon
    $default_icon = array(
        'url' => '/wp-content/themes/geotour-theme/assets/graphics/map-pins/pin-archaeological-site.svg',
        'size' => array(64, 64),
        'anchor' => array(32, 64),
        'popup_anchor' => array(0, -64)
    );
    
    // Get listing categories
    $categories = get_the_terms($listing_id, 'listing-category');
    
    if (!$categories || is_wp_error($categories)) {
        return $default_icon;
    }
    
    // Find the most specific (child) category with an icon
    $selected_category = geotour_select_most_specific_category($categories);
    
    if (!$selected_category) {
        return $default_icon;
    }
    
    // Get the category map icon from ACF
    $category_icon = get_field('category_map_icon', 'listing-category_' . $selected_category->term_id);
    
    if (!$category_icon || !isset($category_icon['url'])) {
        return $default_icon;
    }
    
    // Return category-specific icon configuration
    return array(
        'url' => $category_icon['url'],
        'size' => array(64, 64), // Standard size for consistency
        'anchor' => array(32, 64), // Bottom center anchor
        'popup_anchor' => array(0, -64), // Popup above icon
        'category' => $selected_category->name,
        'category_id' => $selected_category->term_id
    );
}

/**
 * Select the most specific (child) category from a list of categories
 * 
 * @param array $categories Array of term objects
 * @return object|null The most specific category or null
 */
function geotour_select_most_specific_category($categories) {
    if (empty($categories)) {
        return null;
    }
    
    // If only one category, return it
    if (count($categories) === 1) {
        return $categories[0];
    }
    
    // Find the category with the highest level (most specific/child)
    $most_specific = null;
    $highest_level = -1;
    
    foreach ($categories as $category) {
        $level = geotour_get_category_level($category->term_id);
        
        if ($level > $highest_level) {
            $highest_level = $level;
            $most_specific = $category;
        }
    }
    
    // If no clear hierarchy, return the first category with an icon
    if (!$most_specific) {
        foreach ($categories as $category) {
            $category_icon = get_field('category_map_icon', 'listing-category_' . $category->term_id);
            if ($category_icon && isset($category_icon['url'])) {
                return $category;
            }
        }
        
        // Final fallback - return first category
        return $categories[0];
    }
    
    return $most_specific;
}

/**
 * Get the hierarchy level of a category (0 = top level, 1 = child, etc.)
 * 
 * @param int $term_id The term ID
 * @return int The hierarchy level
 */
function geotour_get_category_level($term_id) {
    $level = 0;
    $current_term_id = $term_id;
    
    while (true) {
        $term = get_term($current_term_id, 'listing-category');
        
        if (!$term || is_wp_error($term) || $term->parent == 0) {
            break;
        }
        
        $level++;
        $current_term_id = $term->parent;
        
        // Prevent infinite loops
        if ($level > 10) {
            break;
        }
    }
    
    return $level;
}

/**
 * Generate JavaScript icon configuration for Leaflet
 * 
 * @param array $icon_config Icon configuration from geotour_get_listing_map_icon()
 * @return string JavaScript object string
 */
function geotour_generate_js_icon_config($icon_config) {
    return json_encode(array(
        'iconUrl' => $icon_config['url'],
        'iconSize' => $icon_config['size'],
        'iconAnchor' => $icon_config['anchor'],
        'popupAnchor' => $icon_config['popup_anchor']
    ));
}

/**
 * Get all available category icons for debugging/admin purposes
 * 
 * @return array Array of categories with their icon configurations
 */
function geotour_get_all_category_icons() {
    $categories = get_terms(array(
        'taxonomy' => 'listing-category',
        'hide_empty' => false
    ));
    
    $category_icons = array();
    
    if (!is_wp_error($categories)) {
        foreach ($categories as $category) {
            $icon = get_field('category_map_icon', 'listing-category_' . $category->term_id);
            $category_icons[$category->term_id] = array(
                'name' => $category->name,
                'slug' => $category->slug,
                'parent' => $category->parent,
                'level' => geotour_get_category_level($category->term_id),
                'has_icon' => !empty($icon),
                'icon_url' => !empty($icon) ? $icon['url'] : null
            );
        }
    }
    
    return $category_icons;
}