<?php
/**
 * Template part for displaying a single listing map in 100vw section
 * 
 * @package Geotour_Mobile_First
 */

// Get the ACF position field data
$position_data = get_field('position');

// Check if we have position data and markers
if (!$position_data || !isset($position_data['markers']) || empty($position_data['markers'])) {
    return; // Exit if no position data
}

// Get the first marker (as specified, always use markers[0])
$marker = $position_data['markers'][0];

// Extract coordinates from the first marker
$marker_lat = isset($marker['lat']) ? floatval($marker['lat']) : null;
$marker_lng = isset($marker['lng']) ? floatval($marker['lng']) : null;
$marker_label = isset($marker['label']) ? $marker['label'] : get_the_title();

// Ensure we have valid coordinates
if (!$marker_lat || !$marker_lng) {
    return; // Exit if no valid coordinates
}

// Get zoom level from position data, default to 13 for single listings
$zoom_level = isset($position_data['zoom']) ? intval($position_data['zoom']) : 13;

// Reduce zoom level by 2 for better overview
$zoom_level = max(1, $zoom_level - 2); // Ensure minimum zoom of 1

// Get category-based map icon
$map_icon_config = geotour_get_listing_map_icon(get_the_ID());

// Get the listing excerpt for the content area
$listing_excerpt = get_the_excerpt();
if (empty($listing_excerpt)) {
    $listing_excerpt = get_the_content();
    $listing_excerpt = wp_trim_words(strip_tags($listing_excerpt), 50);
}

// ACF Fields for conditional icons
$post_id = get_the_ID();

// Corrected ACF Fields for 3D Video Path and 3D Tour
$video_path_value = get_field('video_path', $post_id); // ACF field: video_path
$tour_3d_value = get_field('3d_tour', $post_id);       // ACF field: 3d_tour

// Original ACF fields for other icons (will be handled later or if still needed)
// $video_3d_path_url = get_field('acf_3d_video_path_url', $post_id); // Kept for reference, replaced by video_path
// $tour_3d_url = get_field('acf_3d_tour_url', $post_id);             // Kept for reference, replaced by 3d_tour
$tour_2d_url = get_field('acf_2d_virtual_tour_url', $post_id);
$map_2d_custom_url = get_field('acf_2d_leaflet_map_url', $post_id);

// Base URLs
$google_maps_base_url = 'https://www.google.com/maps?q=';
$route_planner_base_url = 'https://www.geotour.gr/listing/?route_listings=';
$cesium_3d_map_base_url = 'https://tour.geotour.gr/3dmap/'; // Base for Cesium

?>

<section class="listing-map-full-section">
    <div class="listing-map-container-wrapper">
        
        <!-- Left side - Content overlap area (hidden on mobile) -->
        <div class="listing-map-content-area">
            <div class="listing-excerpt-section">
                <h3 class="excerpt-title"><?php _e('Summary About This Location', 'geotour'); ?></h3>
                <?php if ($listing_excerpt && strlen($listing_excerpt) > 10) : ?>
                    <div class="listing-excerpt-content">
                        <p><?php echo esc_html($listing_excerpt); ?></p>
                    </div>
                <?php else : ?>
                    <div class="listing-excerpt-content">
                        <p><?php _e('Discover this fascinating location and its rich history.', 'geotour'); ?></p>
                    </div>
                <?php endif; ?>
                  <!-- Additional metadata could go here -->                <div class="scroll-to-content">
                    <a href="#listing-content" class="scroll-to-content-btn">
                        <span>Full text below</span>
                        <div class="scroll-arrow" style="cursor: pointer;">
                            
                        </div>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Right side - Map area -->
        <div class="listing-map-area">
            <div id="listing-map" 
                 class="geotour-map-container listing-single-map"
                 data-lat="<?php echo esc_attr($marker_lat); ?>"
                 data-lng="<?php echo esc_attr($marker_lng); ?>"
                 data-zoom="<?php echo esc_attr($zoom_level); ?>"
                 data-static="true"
                 data-no-popup="true">
                
                <!-- Loading placeholder -->
                <div class="map-loading">
                    <p><?php _e('Loading map...', 'geotour'); ?></p>
                </div>
            </div>
            
            <!-- Map controls area -->
            <div class="listing-map-controls">
                <?php if ($marker_lat && $marker_lng) : ?>
                    <a href="<?php echo esc_url($google_maps_base_url . $marker_lat . ',' . $marker_lng); ?>" target="_blank" class="map-control-button" title="<?php esc_attr_e('Open in Google Maps', 'geotour'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    </a>
                    <a href="<?php echo esc_url($route_planner_base_url . $post_id); ?>" class="map-control-button" title="<?php esc_attr_e('Open Route Planner', 'geotour'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M22.43 10.59l-9.01-9.01c-.75-.75-2.07-.75-2.83 0l-9.01 9.01c-.75.75-.75 2.07 0 2.83l9.01 9.01c.75.75 2.07.75 2.83 0l9.01-9.01c.76-.75.76-2.07 0-2.83zm-10.42 8.09V14H8v-4h4V5.32l5.69 5.69-5.69 5.67z"/></svg>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg(array('lon' => $marker_lng, 'lat' => $marker_lat, 'heading' => '0.0', 'pitch' => '-45'), $cesium_3d_map_base_url)); ?>" class="map-control-button" title="<?php esc_attr_e('Open 3D Map', 'geotour'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L3 7v10l9 5 9-5V7l-9-5zm0 2.24L19.5 8 12 11.76 4.5 8 12 4.24zM4 16.17V9.83L11 14v6.34L4 16.17zm16 0L13 20.34V14l7-4.17v6.34z"/></svg>
                    </a>
                <?php endif; ?>

                <?php // 3D Video Path - ACF: video_path
                if (!empty($video_path_value) && $marker_lat && $marker_lng) :
                    $video_path_url = add_query_arg(array('videopath' => $video_path_value), $cesium_3d_map_base_url); ?>
                    <a href="<?php echo esc_url($video_path_url); ?>" target="_blank" class="map-control-button map-control-3d" title="<?php esc_attr_e('Play 3D Video Path', 'geotour'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7L8 5z"/></svg>
                    </a>
                <?php endif; ?>

                <?php // 3D Tour - ACF: 3d_tour
                if (!empty($tour_3d_value) && $marker_lat && $marker_lng) :
                    $tour_3d_url_constructed = add_query_arg(array('tour' => $tour_3d_value), $cesium_3d_map_base_url); ?>
                    <a href="<?php echo esc_url($tour_3d_url_constructed); ?>" target="_blank" class="map-control-button map-control-3d" title="<?php esc_attr_e('Start 3D Tour', 'geotour'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zm0 8c-1.65 0-3-1.35-3-3s1.35-3 3-3 3 1.35 3 3-1.35 3-3 3zm0-12C6.48 3 2 7.48 2 13s4.48 10 10 10 10-4.48 10-10S17.52 3 12 3zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                    </a>
                <?php endif; ?>

                <?php /* Placeholder for future 2D Tour Icon */ ?>
                <?php if (!empty($tour_2d_url)) : ?>
                    <!-- <a href="<?php echo esc_url($tour_2d_url); ?>" target="_blank" class="map-control-button" title="<?php esc_attr_e('View 2D Virtual Tour', 'geotour'); ?>"> -->
                        <?php /* SVG: 2D Tour Icon */ ?> <!-- 2DT -->
                    <!-- </a> -->
                <?php endif; ?>

                <?php /* Placeholder for future 2D Custom Map Icon */ ?>
                <?php if (!empty($map_2d_custom_url)) : ?>
                    <!-- <a href="<?php echo esc_url($map_2d_custom_url); ?>" target="_blank" class="map-control-button" title="<?php esc_attr_e('View Custom Map', 'geotour'); ?>"> -->
                        <?php /* SVG: 2D Map Icon */ ?> <!-- 2DM -->
                    <!-- </a> -->
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</section>

<script>
// Pass listing map data to JavaScript
window.geotourListingMapData = window.geotourListingMapData || {};
window.geotourListingMapData.single = {
    coordinates: [<?php echo $marker_lat; ?>, <?php echo $marker_lng; ?>],
    zoomLevel: <?php echo $zoom_level; ?>,
    iconConfig: <?php echo geotour_generate_js_icon_config($map_icon_config); ?>,
    isStatic: true,
    showPopup: false
};

console.log('Listing map data set:', window.geotourListingMapData.single);
</script>