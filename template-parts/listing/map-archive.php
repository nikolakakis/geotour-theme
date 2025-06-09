<?php
/**
 * Template part for displaying Leaflet map for listing archives
 *
 * @package Geotour_Mobile_First
 */

// Only show map on listing archive pages
if (is_post_type_archive('listing') || is_tax(array('listing-category', 'listing-region', 'listing-tag'))) :
?>
<div class="archive-map-section">
    <h2><?php _e('Map View', 'geotour'); ?></h2>
    <div id="archive-map" 
         class="geotour-map-container listing-archive-map"
         data-post-type="listing">
        <p><?php _e('Loading map...', 'geotour'); ?></p>
    </div>
</div>
<?php 
endif;
?>