<?php
/**
 * Map Controls Popup Shortcode
 * 
 * Creates a compact floating menu of map controls for use in popups
 * Usage: [geotour_map_controls_popup]
 * 
 * @package Geotour_Mobile_First
 */

// Register the shortcode
function geotour_map_controls_popup_shortcode($atts) {
    // Only work on single listing pages
    if (!is_singular('listing')) {
        return '';
    }
    
    $post_id = get_the_ID();
    
    // Get the ACF position field data
    $position_data = get_field('position', $post_id);
    
    // Check if we have position data and markers
    if (!$position_data || !isset($position_data['markers']) || empty($position_data['markers'])) {
        return '';
    }
    
    // Get the first marker
    $marker = $position_data['markers'][0];
    
    // Extract coordinates from the first marker
    $marker_lat = isset($marker['lat']) ? floatval($marker['lat']) : null;
    $marker_lng = isset($marker['lng']) ? floatval($marker['lng']) : null;
    
    // Ensure we have valid coordinates
    if (!$marker_lat || !$marker_lng) {
        return '';
    }
    
    // Get ACF fields for conditional icons
    $video_path_value = get_field('video_path', $post_id);
    $tour_3d_value = get_field('3d_tour', $post_id);
    $vtour_link = get_field('vtour_link', $post_id);
    $tour_2d_url = get_field('acf_2d_virtual_tour_url', $post_id);
    $map_2d_custom_url = get_field('acf_2d_leaflet_map_url', $post_id);
    
    // Base URLs
    $google_maps_base_url = 'https://www.google.com/maps?q=';
    $route_planner_base_url = 'https://www.geotour.gr/listing/?route_listings=';
    $cesium_3d_map_base_url = 'https://www.geotour.gr/vt/3dmap/';
    
    // Start output buffering
    ob_start();
    ?>
    
    <div class="map-controls-popup-container">
        <div class="map-controls-popup-grid">
            
            <?php if ($marker_lat && $marker_lng) : ?>
                <!-- Google Maps -->
                <a href="<?php echo esc_url($google_maps_base_url . $marker_lat . ',' . $marker_lng); ?>" 
                   target="_blank" 
                   class="map-control-popup-button" 
                   title="<?php esc_attr_e('Open in Google Maps', 'geotour'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                </a>
                
                <!-- Route Planner -->
                <a href="<?php echo esc_url($route_planner_base_url . $post_id); ?>" 
                   class="map-control-popup-button" 
                   title="<?php esc_attr_e('Open Route Planner', 'geotour'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.43 10.59l-9.01-9.01c-.75-.75-2.07-.75-2.83 0l-9.01 9.01c-.75.75-.75 2.07 0 2.83l9.01 9.01c.75.75 2.07.75 2.83 0l9.01-9.01c.76-.75.76-2.07 0-2.83zm-10.42 8.09V14H8v-4h4V5.32l5.69 5.69-5.69 5.67z"/>
                    </svg>
                </a>
                
                <!-- 3D Map -->
                <a href="<?php echo esc_url(add_query_arg(array('lon' => $marker_lng, 'lat' => $marker_lat, 'heading' => '0.0', 'pitch' => '-45'), $cesium_3d_map_base_url)); ?>" 
                   class="map-control-popup-button" 
                   title="<?php esc_attr_e('Open 3D Map', 'geotour'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L3 7v10l9 5 9-5V7l-9-5zm0 2.24L19.5 8 12 11.76 4.5 8 12 4.24zM4 16.17V9.83L11 14v6.34L4 16.17zm16 0L13 20.34V14l7-4.17v6.34z"/>
                    </svg>
                </a>
            <?php endif; ?>

            <?php // Virtual Tour - ACF: vtour_link
            if (!empty($vtour_link)) : ?>
                <a href="<?php echo esc_url($vtour_link); ?>" 
                   class="map-control-popup-button map-control-vr" 
                   title="<?php esc_attr_e('Open Virtual Tour', 'geotour'); ?>">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/graphics/map-pins/panoramas.svg" 
                         alt="Virtual Tour" 
                         width="18" 
                         height="18">
                </a>
            <?php endif; ?>

            <?php // 3D Video Path - ACF: video_path
            if (!empty($video_path_value) && $marker_lat && $marker_lng) :
                $video_path_url = add_query_arg(array('videopath' => $video_path_value), $cesium_3d_map_base_url); ?>
                <a href="<?php echo esc_url($video_path_url); ?>" 
                   class="map-control-popup-button map-control-3d" 
                   title="<?php esc_attr_e('Play 3D Video Path', 'geotour'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7L8 5z"/>
                    </svg>
                </a>
            <?php endif; ?>

            <?php // 3D Tour - ACF: 3d_tour
            if (!empty($tour_3d_value) && $marker_lat && $marker_lng) :
                $tour_3d_url_constructed = add_query_arg(array('tour' => $tour_3d_value), $cesium_3d_map_base_url); ?>
                <a href="<?php echo esc_url($tour_3d_url_constructed); ?>" 
                   class="map-control-popup-button map-control-3d" 
                   title="<?php esc_attr_e('Start 3D Tour', 'geotour'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zm0 8c-1.65 0-3-1.35-3-3s1.35-3 3-3 3 1.35 3 3-1.35 3-3 3zm0-12C6.48 3 2 7.48 2 13s4.48 10 10 10 10-4.48 10-10S17.52 3 12 3zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                    </svg>
                </a>
            <?php endif; ?>

            <?php // Future 2D Tour (commented for future use)
            if (!empty($tour_2d_url)) : ?>
                <!-- Future 2D Tour implementation -->
            <?php endif; ?>

            <?php // Future 2D Custom Map (commented for future use)
            if (!empty($map_2d_custom_url)) : ?>
                <!-- Future 2D Custom Map implementation -->
            <?php endif; ?>
            
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('geotour_map_controls_popup', 'geotour_map_controls_popup_shortcode');