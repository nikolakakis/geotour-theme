<?php
/**
 * Legacy Shortcode Replacements
 * 
 * This file handles the replacement/removal of old BoldThemes shortcodes
 * to prevent them from displaying in the new theme.
 * 
 * @package Geotour_Mobile_First
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize legacy shortcode replacements
 */
function geotour_init_legacy_shortcode_replacements() {
    // BoldThemes layout shortcodes - return content only
    add_shortcode('bt_bb_section', 'geotour_replace_wrapper_shortcode');
    add_shortcode('bt_bb_row', 'geotour_replace_wrapper_shortcode');
    add_shortcode('bt_bb_column', 'geotour_replace_wrapper_shortcode');
    add_shortcode('bt_bb_text', 'geotour_replace_wrapper_shortcode');
    add_shortcode('bt_bb_content', 'geotour_replace_wrapper_shortcode');
    
    // BoldThemes component shortcodes - hide completely
    add_shortcode('bt_bb_headline', 'geotour_hide_shortcode');
    add_shortcode('bt_bb_button', 'geotour_hide_shortcode');
    add_shortcode('bt_bb_image', 'geotour_hide_shortcode');
    add_shortcode('bt_bb_separator', 'geotour_hide_shortcode');
    add_shortcode('bt_bb_spacer', 'geotour_hide_shortcode');
    add_shortcode('bt_bb_shortcode', 'geotour_handle_bt_bb_shortcode'); // Special handler for nested shortcodes
    
    // BoldThemes listing specific shortcodes
    add_shortcode('bt_bb_listing_field', 'geotour_replace_listing_field_shortcode');
    add_shortcode('bt_bb_listing_map', 'geotour_hide_shortcode'); // We have our own map
    add_shortcode('bt_bb_listing_gallery', 'geotour_hide_shortcode'); // Handle separately if needed
    
    // Add more shortcodes as you discover them in content
    add_shortcode('bt_bb_icon', 'geotour_hide_shortcode');
    add_shortcode('bt_bb_accordion', 'geotour_hide_shortcode');
    add_shortcode('bt_bb_tab_container', 'geotour_hide_shortcode');
    add_shortcode('bt_bb_tab_item', 'geotour_hide_shortcode');
    
    // Hide reviews-feed shortcodes directly (in case they're not wrapped)
    add_shortcode('reviews-feed', 'geotour_hide_shortcode');
    add_shortcode('bt_bb_raw_content', 'geotour_hide_shortcode');
}

/**
 * Replace wrapper shortcodes - return only the content inside
 * 
 * @param array $atts Shortcode attributes
 * @param string $content Shortcode content
 * @return string
 */
function geotour_replace_wrapper_shortcode($atts, $content = '') {
    // Process any nested shortcodes within the content
    return do_shortcode($content);
}

/**
 * Hide shortcodes completely - return empty string
 * 
 * @param array $atts Shortcode attributes
 * @param string $content Shortcode content
 * @return string
 */
function geotour_hide_shortcode($atts, $content = '') {
    return '';
}

/**
 * Handle bt_bb_shortcode with special logic for reviews-feed
 * 
 * @param array $atts Shortcode attributes
 * @param string $content Shortcode content
 * @return string
 */
function geotour_handle_bt_bb_shortcode($atts, $content = '') {
    $atts = shortcode_atts(array(
        'shortcode_content' => '',
    ), $atts);
    
    if (!empty($atts['shortcode_content'])) {
        // Decode the shortcode content (it's often encoded)
        $shortcode_content = $atts['shortcode_content'];
        
        // Replace backticks with square brackets to get proper shortcode format
        $shortcode_content = str_replace('`{`', '[', $shortcode_content);
        $shortcode_content = str_replace('`}`', ']', $shortcode_content);
        
        // Debug log to see what we're processing
        if (WP_DEBUG) {
            error_log('Processing bt_bb_shortcode content: ' . $shortcode_content);
        }
        
        // Check if this contains reviews-feed (more flexible check)
        if (strpos($shortcode_content, 'reviews-feed') !== false) {
            // Hide reviews-feed completely
            if (WP_DEBUG) {
                error_log('Hiding reviews-feed shortcode: ' . $shortcode_content);
            }
            return '';
        } else {
            // For other shortcodes, output them as shortcode text (don't execute)
            if (WP_DEBUG) {
                error_log('Outputting other shortcode: ' . $shortcode_content);
            }
            return $shortcode_content;
        }
    }
    
    return '';
}

/**
 * Replace listing field shortcodes with actual field content
 * 
 * @param array $atts Shortcode attributes
 * @param string $content Shortcode content
 * @return string
 */
function geotour_replace_listing_field_shortcode($atts, $content = '') {
    $atts = shortcode_atts(array(
        'field' => '',
        'display' => 'value',
    ), $atts);
    
    // Map common BoldThemes field names to ACF field names
    $field_mapping = array(
        'address' => 'address',
        'phone' => 'phone',
        'email' => 'email',
        'website' => 'website',
        'opening_hours' => 'opening_hours',
        // Add more mappings as needed
    );
    
    if (!empty($atts['field']) && isset($field_mapping[$atts['field']])) {
        $field_value = get_field($field_mapping[$atts['field']]);
        
        if ($field_value) {
            // Basic formatting based on field type
            switch ($atts['field']) {
                case 'email':
                    return '<a href="mailto:' . esc_attr($field_value) . '">' . esc_html($field_value) . '</a>';
                    
                case 'website':
                    return '<a href="' . esc_url($field_value) . '" target="_blank" rel="noopener">' . esc_html($field_value) . '</a>';
                    
                case 'phone':
                    return '<a href="tel:' . esc_attr($field_value) . '">' . esc_html($field_value) . '</a>';
                    
                default:
                    return esc_html($field_value);
            }
        }
    }
    
    return '';
}

/**
 * Log shortcodes found in content for debugging
 * This helps identify which shortcodes are still being used
 */
function geotour_log_unknown_shortcodes($content) {
    // Only run in development/debugging mode
    if (!WP_DEBUG) {
        return $content;
    }
    
    // Find all shortcodes in content
    preg_match_all('/\[([^\]]+)\]/', $content, $matches);
    
    if (!empty($matches[1])) {
        $shortcodes = array_unique($matches[1]);
        foreach ($shortcodes as $shortcode) {
            // Extract just the shortcode name (before any space/attributes)
            $shortcode_name = explode(' ', $shortcode)[0];
            
            // Log unknown shortcodes starting with 'bt_bb_'
            if (strpos($shortcode_name, 'bt_bb_') === 0 && !shortcode_exists($shortcode_name)) {
                error_log('Unknown BoldThemes shortcode found: [' . $shortcode_name . ']');
            }
        }
    }
    
    return $content;
}

/**
 * Initialize everything
 */
add_action('init', 'geotour_init_legacy_shortcode_replacements');

// Add content filter for logging (only in debug mode)
if (WP_DEBUG) {
    add_filter('the_content', 'geotour_log_unknown_shortcodes', 5);
}

/**
 * Helper function to completely remove shortcodes from content
 * Use this if you want to clean content permanently (be careful!)
 */
function geotour_strip_legacy_shortcodes($content) {
    // Remove all bt_bb_ shortcodes
    $content = preg_replace('/\[bt_bb_[^\]]*\]/', '', $content);
    $content = preg_replace('/\[\/bt_bb_[^\]]*\]/', '', $content);
    
    // Clean up extra whitespace
    $content = preg_replace('/\s+/', ' ', $content);
    $content = trim($content);
    
    return $content;
}