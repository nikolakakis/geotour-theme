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
    // Default fallback icon - use a data URI or existing icon
    $default_icon = array(
        'url' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#3b82f6"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>'),
        'size' => array(32, 32),
        'anchor' => array(16, 32),
        'popup_anchor' => array(0, -32)
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
    
    // Get the category map icon from ACF using the correct taxonomy term format
    $category_icon = get_field('category_map_icon', 'listing-category_' . $selected_category->term_id);
    
    if (!$category_icon) {
        return $default_icon;
    }
    
    // Handle different ACF field return formats
    $icon_url = '';
    if (is_array($category_icon) && isset($category_icon['url'])) {
        // ACF Image field returns array with 'url' key
        $icon_url = $category_icon['url'];
    } elseif (is_string($category_icon)) {
        // ACF URL field returns string
        $icon_url = $category_icon;
    } elseif (is_numeric($category_icon)) {
        // ACF returns attachment ID
        $icon_url = wp_get_attachment_url($category_icon);
    }
    
    if (empty($icon_url)) {
        return $default_icon;
    }
    
    // Return category-specific icon configuration
    return array(
        'url' => $icon_url,
        'size' => array(32, 32), // Consistent size
        'anchor' => array(16, 32), // Bottom center anchor
        'popup_anchor' => array(0, -32), // Popup above icon
        'category' => $selected_category->name,
        'category_id' => $selected_category->term_id
    );
}

/**
 * Get just the map icon URL for a listing (for REST API use)
 * 
 * @param int $listing_id The listing post ID
 * @return string Map icon URL
 */
function geotour_get_listing_map_icon_url($listing_id) {
    $icon_config = geotour_get_listing_map_icon($listing_id);
    return $icon_config['url'];
}

/**
 * Select the most specific (child) category from a list of categories
 * Prioritizes categories with icons that are lowest in hierarchy (most specific)
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
    
    $selected_category = null;
    $highest_level = -1;
    
    // First pass: Find categories with icons and get the most specific one
    foreach ($categories as $category) {
        $category_icon = get_field('category_map_icon', 'listing-category_' . $category->term_id);
        
        // Only consider categories that have icons
        if (!empty($category_icon)) {
            $level = geotour_get_category_level($category->term_id);
            
            // If this is the first category with an icon, or it's more specific (higher level)
            if ($selected_category === null || $level > $highest_level) {
                $highest_level = $level;
                $selected_category = $category;
            }
        }
    }
    
    // If we found a category with an icon, return it
    if ($selected_category) {
        return $selected_category;
    }
    
    // Fallback: If no categories have icons, return the most specific category anyway
    $highest_level = -1;
    foreach ($categories as $category) {
        $level = geotour_get_category_level($category->term_id);
        if ($level > $highest_level) {
            $highest_level = $level;
            $selected_category = $category;
        }
    }
    
    // Final fallback - return first category
    return $selected_category ?: $categories[0];
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