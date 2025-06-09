<?php
/**
 * Template part for displaying Leaflet map for a single listing
 *
 * @package Geotour_Mobile_First
 */

// Get listing coordinates from ACF or post meta
$coordinates = geotour_get_listing_coordinates(get_the_ID());

if ($coordinates) :
?>
<div class="listing-map-section">
    <h3><?php _e('Location', 'geotour'); ?></h3>
    <div id="listing-map" 
         class="geotour-map-container listing-single-map" 
         data-lat="<?php echo esc_attr($coordinates['lat']); ?>"
         data-lng="<?php echo esc_attr($coordinates['lng']); ?>"
         data-title="<?php echo esc_attr(get_the_title()); ?>"
         data-permalink="<?php echo esc_url(get_permalink()); ?>">
        <p><?php _e('Loading map...', 'geotour'); ?></p>
    </div>
</div>
<?php 
endif;
?>