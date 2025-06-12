<?php
/**
 * Content Gallery Shortcode Replacement
 * 
 * Modern replacement for [bt_bb_css_image_grid] shortcode
 * Creates responsive masonry-style galleries with Fancybox lightbox
 * 
 * @package Geotour_Mobile_First
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize content gallery shortcode
 */
function geotour_init_content_gallery_shortcode() {
    add_shortcode('bt_bb_css_image_grid', 'geotour_css_image_grid_shortcode');
}

/**
 * Handle bt_bb_css_image_grid shortcode
 * 
 * @param array $atts Shortcode attributes
 * @param string $content Shortcode content
 * @return string HTML output
 */
function geotour_css_image_grid_shortcode($atts, $content = '') {
    $atts = shortcode_atts(array(
        'images' => '',
        'columns' => '3',
        'format' => '',
        'gap' => '20',
        'border_radius' => '8',
    ), $atts);
    
    // Sanitize inputs
    $images = sanitize_text_field($atts['images']);
    $columns = absint($atts['columns']);
    $format = sanitize_text_field($atts['format']);
    $gap = absint($atts['gap']);
    $border_radius = absint($atts['border_radius']);
    
    // Validate required fields
    if (empty($images)) {
        return '';
    }
    
    // Parse image IDs
    $image_ids = array_map('absint', explode(',', $images));
    $image_ids = array_filter($image_ids); // Remove empty values
    
    if (empty($image_ids)) {
        return '';
    }
    
    // Parse format string
    $format_array = array();
    if (!empty($format)) {
        $format_array = array_map('sanitize_text_field', explode(',', $format));
    }
    
    // Set default columns if invalid
    if ($columns < 1) {
        $columns = 3;
    }
    
    // Generate unique gallery ID for this instance
    static $gallery_counter = 0;
    $gallery_counter++;
    $gallery_id = 'geotour-gallery-' . $gallery_counter;
    
    // Start building HTML
    $html = '<div class="geotour-image-grid" ';
    $html .= 'style="--grid-columns: ' . esc_attr($columns) . '; --grid-gap: ' . esc_attr($gap) . 'px; --border-radius: ' . esc_attr($border_radius) . 'px;" ';
    $html .= 'data-gallery-id="' . esc_attr($gallery_id) . '">';
    
    // Loop through images
    foreach ($image_ids as $index => $image_id) {
        // Get image URLs
        $full_url = wp_get_attachment_image_url($image_id, 'full');
        $display_url = wp_get_attachment_image_url($image_id, 'large');
        
        // Skip if image doesn't exist
        if (!$full_url || !$display_url) {
            continue;
        }
        
        // Get image metadata
        $image_title = get_the_title($image_id);
        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        $image_caption = wp_get_attachment_caption($image_id);
        
        // Get format for this image
        $item_format = isset($format_array[$index]) ? $format_array[$index] : '11';
        
        // Build caption for lightbox
        $lightbox_caption = '';
        if (!empty($image_title)) {
            $lightbox_caption = esc_attr($image_title);
        }
        if (!empty($image_caption) && $image_caption !== $image_title) {
            $lightbox_caption .= !empty($lightbox_caption) ? ' - ' . esc_attr($image_caption) : esc_attr($image_caption);
        }
        
        // Generate grid item
        $html .= '<a class="grid-item" ';
        $html .= 'href="' . esc_url($full_url) . '" ';
        $html .= 'data-fancybox="' . esc_attr($gallery_id) . '" ';
        $html .= 'data-caption="' . $lightbox_caption . '" ';
        $html .= 'data-format="' . esc_attr($item_format) . '" ';
        $html .= 'title="' . esc_attr($image_title) . '">';
        
        $html .= '<img src="' . esc_url($display_url) . '" ';
        $html .= 'alt="' . esc_attr($image_alt ?: $image_title) . '" ';
        $html .= 'loading="lazy" />';
        
        $html .= '</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Enqueue gallery assets
 */
function geotour_enqueue_gallery_assets() {
    // Only enqueue if we're using the shortcode
    global $post;
    
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'bt_bb_css_image_grid')) {
        // Assets will be handled by the main Vite build
        // This function can be used for future conditional loading if needed
    }
}

// Initialize the shortcode
add_action('init', 'geotour_init_content_gallery_shortcode');
add_action('wp_enqueue_scripts', 'geotour_enqueue_gallery_assets');