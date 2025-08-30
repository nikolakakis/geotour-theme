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

// Get zoom level from position data, default to 12 for single listings
$zoom_level = isset($position_data['zoom']) ? intval($position_data['zoom']) : 12;

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

<section class="listing-map-full-section" id="summary">
    <div class="listing-map-container-wrapper">
        
        <!-- Left side - Content overlap area (hidden on mobile) -->
        <div class="listing-map-content-area">
            <div class="listing-excerpt-section">
                <div class="summary-content-box">
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
                </div>
                <!-- Additional metadata could go here -->
                <div class="scroll-to-content">
                    <a href="#listing-content" class="scroll-to-content-btn">
                        <span>Full text below</span>
                        <div class="scroll-arrow" style="cursor: pointer;"></div>
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
                 data-zoom="14"
                 data-static="false"
                 data-no-popup="true">
                
                <!-- Loading placeholder -->
                <div class="map-loading">
                    <p><?php _e('Loading map...', 'geotour'); ?></p>
                </div>
            </div>
            
            <!-- Map controls area -->
            <?php echo do_shortcode('[geotour_map_controls_popup]'); ?>
        </div>
        
    </div>
</section>

<script>
// Pass listing map data to JavaScript
window.geotourListingMapData = window.geotourListingMapData || {};
window.geotourListingMapData.single = {
    coordinates: [<?php echo $marker_lat; ?>, <?php echo $marker_lng; ?>],
    zoomLevel: 14,
    iconConfig: <?php echo geotour_generate_js_icon_config($map_icon_config); ?>,
    isStatic: false,
    showPopup: false
};

console.log('Listing map data set:', window.geotourListingMapData.single);
</script>