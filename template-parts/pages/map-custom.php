<?php
/**
 * Template part for displaying custom post map from ACF field
 * 
 * Displays a 100vw section with shortcode content from post_map ACF field
 * Only shows if field contains what appears to be a shortcode
 *
 * @package Geotour_Mobile_First
 */

// Get the post_map ACF field
$post_map_content = get_field('post_map');

// Check if field has content and appears to be a shortcode
if (!empty($post_map_content) && is_string($post_map_content)) {
    $trimmed_content = trim($post_map_content);
    
    // Check if content contains shortcode brackets and looks like a geotour_map shortcode
    if (strpos($trimmed_content, '[') !== false && 
        strpos($trimmed_content, ']') !== false &&
        strpos($trimmed_content, 'geotour_map') !== false) {
        
        // Process the shortcode
        $processed_content = do_shortcode($trimmed_content);
        
        // Only display if shortcode processing returned content different from original
        // and the processed content is not empty
        if (!empty($processed_content) && 
            $processed_content !== $trimmed_content && 
            strlen(trim(strip_tags($processed_content))) > 0) { ?>
            
            <section class="post-custom-map-section">
                <div class="custom-map-container">
                    <?php echo $processed_content; ?>
                </div>
            </section>
            
        <?php } elseif (current_user_can('edit_posts')) { ?>
            <!-- Debug info for editors/admins -->
            <section class="post-custom-map-section error">
                <div class="custom-map-container">
                    <p><strong>Map Error:</strong> Shortcode could not be processed.</p>
                    <p><small>Raw content: <code><?php echo esc_html($trimmed_content); ?></code></small></p>
                </div>
            </section>
        <?php }
    } elseif (current_user_can('edit_posts') && !empty($trimmed_content)) { ?>
        <!-- Debug info for editors/admins when content doesn't look like a shortcode -->
        <section class="post-custom-map-section error">
            <div class="custom-map-container">
                <p><strong>Map Field Notice:</strong> Content doesn't appear to be a valid geotour_map shortcode.</p>
                <p><small>Expected format: <code>[geotour_map map_id="12345" ...]</code></small></p>
                <p><small>Current content: <code><?php echo esc_html($trimmed_content); ?></code></small></p>
            </div>
        </section>
    <?php }
}
?>
