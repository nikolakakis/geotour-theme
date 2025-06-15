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
            
            <!-- Map controls area (reserved for future icons) -->
            <div class="listing-map-controls">
                <!-- Reserved for future map-related icons -->
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