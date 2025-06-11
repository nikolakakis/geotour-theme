<?php
/**
 * Template part for displaying a single listing map
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

// Prepare popup content
$popup_title = get_the_title();
$popup_excerpt = get_the_excerpt();
$popup_permalink = get_permalink();

// Build popup HTML
$popup_content = '<div class="listing-map-popup">';
$popup_content .= '<h5>' . esc_html($popup_title) . '</h5>';
if ($popup_excerpt) {
    $popup_content .= '<p>' . esc_html(wp_trim_words($popup_excerpt, 15)) . '</p>';
}
$popup_content .= '<a href="' . esc_url($popup_permalink) . '" class="popup-link">' . __('View Details', 'geotour') . '</a>';
$popup_content .= '</div>';
?>

<section class="listing-map-section">
    <div id="listing-map" 
         class="geotour-map-container listing-single-map"
         data-lat="<?php echo esc_attr($marker_lat); ?>"
         data-lng="<?php echo esc_attr($marker_lng); ?>"
         data-zoom="<?php echo esc_attr($zoom_level); ?>"
         data-title="<?php echo esc_attr($popup_title); ?>"
         data-popup="<?php echo esc_attr($popup_content); ?>"
         data-permalink="<?php echo esc_url($popup_permalink); ?>">
        
        <!-- Loading placeholder -->
        <div class="map-loading">
            <p><?php _e('Loading map...', 'geotour'); ?></p>
        </div>
    </div>
    
    <!-- Map caption/info -->
    <div class="map-info">
        <p class="map-location">
            <i class="map-icon"></i>
            <strong><?php _e('Location:', 'geotour'); ?></strong> 
            <?php echo esc_html($marker_label); ?>
        </p>
        
        <?php if (isset($position_data['address']) && $position_data['address']) : ?>
            <p class="map-address">
                <small><?php echo esc_html($position_data['address']); ?></small>
            </p>
        <?php endif; ?>
    </div>
</section>

<script>
// Pass listing map data to JavaScript
window.geotourListingMapData = window.geotourListingMapData || {};
window.geotourListingMapData.single = {
    coordinates: [<?php echo $marker_lat; ?>, <?php echo $marker_lng; ?>],
    zoomLevel: <?php echo $zoom_level; ?>,
    popupText: <?php echo json_encode($popup_content); ?>,
    title: <?php echo json_encode($popup_title); ?>,
    permalink: <?php echo json_encode($popup_permalink); ?>
};

console.log('Listing map data set:', window.geotourListingMapData.single);
</script>