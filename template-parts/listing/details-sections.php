<?php
/**
 * Template part for displaying listing details in 100vw section
 * Over the content 2 - Site access and other category-based information
 * 
 * @package Geotour_Mobile_First
 */

// Get the listing ID
$listing_id = get_the_ID();

// Get site access information
$siteinfo_pavedroad = get_field('siteinfo_pavedroad', $listing_id);
$siteinfo_earthroad = get_field('siteinfo_earthroad', $listing_id);
$siteinfo_fourwheel = get_field('siteinfo_fourwheel', $listing_id);
$siteinfo_hike = get_field('siteinfo_hike', $listing_id);
$siteinfo_notes = get_field('siteinfo_notes', $listing_id);

// Check if we have any access information to display
$has_access_info = $siteinfo_pavedroad || $siteinfo_earthroad || $siteinfo_fourwheel || $siteinfo_hike || !empty($siteinfo_notes);

// Only display the section if we have access information
if (!$has_access_info) {
    return;
}
?>

<section class="listing-details-full-section">
    <div class="listing-details-container-wrapper">
        
        <!-- Column 1: Site Access Information -->
        <div class="details-column details-column-1">
            <div class="column-content">
                <h4 class="column-heading"><?php _e('Site Access', 'geotour'); ?></h4>
                
                <div class="site-access-info">
                    <?php
                    $access_methods = array();
                    
                    if ($siteinfo_pavedroad == '1') {
                        $access_methods[] = __('Paved road', 'geotour');
                    }
                    if ($siteinfo_earthroad == '1') {
                        $access_methods[] = __('Earth road', 'geotour');
                    }
                    if ($siteinfo_fourwheel == '1') {
                        $access_methods[] = __('4WD vehicle required', 'geotour');
                    }
                    if ($siteinfo_hike == '1') {
                        $access_methods[] = __('Hiking required', 'geotour');
                    }
                    
                    if (!empty($access_methods)) :
                    ?>
                        <div class="access-methods">
                            <ul>
                                <?php foreach ($access_methods as $method) : ?>
                                    <li><?php echo esc_html($method); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($siteinfo_notes)) : ?>
                        <div class="access-notes">
                            <?php echo wp_kses_post($siteinfo_notes); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Column 2: Additional Info -->
        <div class="details-column details-column-2">
            <div class="column-content">
                <h4 class="column-heading"><?php _e('Additional Info', 'geotour'); ?></h4>
                <div class="column-placeholder">
                    <!-- Future content based on listing categories -->
                </div>
            </div>
        </div>
        
        <!-- Column 3: Weather Forecast -->
        <div class="details-column details-column-3">
            <div class="column-content">
                <h4 class="column-heading"><?php _e('Weather Forecast', 'geotour'); ?></h4>
                <div id="openmeteo"></div>
            </div>
        </div>
        
    </div>
</section>