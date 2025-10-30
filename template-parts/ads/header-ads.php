<?php
// template-parts/ads/header-ads.php

// Get the ACF field value
$adtype = function_exists('get_field') ? get_field('adtype') : array();

if (!is_array($adtype)) {
    $adtype = empty($adtype) ? array() : array($adtype);
}

// Only proceed if at least one ad type has been selected
if (!empty($adtype)) :

    // --- 1. Load the Consent Message Script (if any Google ad type is used) ---
    if (in_array('adsense_auto', $adtype) || in_array('728_header_ad', $adtype) || in_array('listing-gam-01', $adtype)) :
?>
<script async src="https://fundingchoicesmessages.google.com/i/pub-7479927233059417?ers=1" nonce="..."></script>
<script>(function() {function signalGooglefcPresent() {if (!window.frames['googlefcPresent']) {if (document.body) {const iframe = document.createElement('iframe'); iframe.style = 'width: 0; height: 0; border: none; z-index: -1000; left: -1000px; top: -1000px;'; iframe.style.display = 'none'; iframe.name = 'googlefcPresent'; document.body.appendChild(iframe);} else {setTimeout(signalGooglefcPresent, 0);}}}signalGooglefcPresent();})();</script>
<?php 
    endif;

    // --- 2. Load Ad-System-Specific Scripts ---

    // AdSense Auto Ads
    if (in_array('adsense_auto', $adtype)) :
?>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7479927233059417" crossorigin="anonymous"></script>
<?php
    endif;
    
    // GetYourGuide Widget
    if (in_array('getyourguide', $adtype)) :
?>
<script async defer src="https://widget.getyourguide.com/dist/pa.umd.production.min.js" data-gyg-partner-id="QIP05M4"></script>
<?php
    endif;

    // --- 3. CONSOLIDATED GOOGLE AD MANAGER SCRIPT ---
    // Check if ANY GAM ad type is selected
    if (in_array('728_header_ad', $adtype) || in_array('listing-gam-01', $adtype) || in_array('listing-gam-anchor-bottom', $adtype)) :

        // Count H2 tags in post content (only if needed for the listing-gam-01 type)
        global $post;
        $h2_count = 0;
        if (in_array('listing-gam-01', $adtype) && isset($post) && !empty($post->post_content)) {
            $h2_count = substr_count(strtolower($post->post_content), '<h2');
        }
?>
<!-- Load GPT library once for all GAM ads -->
<script async src="https://securepubads.g.doubleclick.net/tag/js/gpt.js" crossorigin="anonymous"></script>
<script>
  window.googletag = window.googletag || {cmd: []};

  googletag.cmd.push(function() {
    
    /* --- START: Slot Definitions --- */

    <?php // Homepage Header Ad
    if (in_array('728_header_ad', $adtype)) : ?>
    googletag.defineSlot('/23317265155/geotour-homepage-01', [728, 90], 'div-gpt-ad-1758305678423-0').addService(googletag.pubads());
    <?php endif; ?>

    <?php // Listing Page Ads
    if (in_array('listing-gam-01', $adtype)) : ?>

        // Sidebar slots (all devices)
        googletag.defineSlot('/23317265155/listing-sidebar-01', [[300, 600], [300, 250], [336, 280]], 'div-gpt-ad-1761554388211-0').addService(googletag.pubads());
        googletag.defineSlot('/23317265155/Geotour_Listing_Sidebar_Box_bottom', [[300, 100], [300, 250], [300, 600]], 'div-gpt-ad-1761461807399-0').addService(googletag.pubads());

        <?php if ( ! wp_is_mobile() ) : // DESKTOP ONLY ADS ?>
        
            // Side Rails
            googletag.defineSlot('/23317265155/Geotour_desktop_SideRail_Right', [[160, 600], [120, 600]], 'div-gpt-ad-1761467879664-0').addService(googletag.pubads());
            googletag.defineSlot('/23317265155/Geotour_desktop_SideRail_Left', [[160, 600], [120, 600]], 'div-gpt-ad-1761468110147-0').addService(googletag.pubads());
            
            <?php // Content ads based on H2 count
            if ($h2_count >= 1) : ?>
            googletag.defineSlot('/23317265155/Geotour_Article_Body1_Responsive', [300, 250], 'div-gpt-ad-1759438267926-0').addService(googletag.pubads());
            <?php endif; ?>
            
            <?php if ($h2_count >= 3) : ?>
            googletag.defineSlot('/23317265155/Geotour_Article_Body2_banner', [[980, 120], [960, 90], [970, 90], [728, 90], [750, 200], [970, 250], [950, 90], [750, 100], [750, 300], [930, 180]], 'div-gpt-ad-1760784178571-0').addService(googletag.pubads());
            <?php endif; ?>
            
            <?php if ($h2_count >= 5) : ?>
            googletag.defineSlot('/23317265155/Geotour_Article_Body3a_banner', [300, 250], 'div-gpt-ad-1761555513510-0').addService(googletag.pubads());
            googletag.defineSlot('/23317265155/Geotour_Article_Body3b_banner', [300, 250], 'div-gpt-ad-1761555634118-0').addService(googletag.pubads());
            <?php endif; ?>
            
            <?php if ($h2_count >= 7) : ?>
            googletag.defineSlot('/23317265155/Geotour_Article_Body4_banner', [728, 90], 'div-gpt-ad-1758981672253-0').addService(googletag.pubads());
            <?php endif; ?>

        <?php else : // MOBILE ONLY ADS ?>

            googletag.defineSlot('/23317265155/Geotour_Listing_UnderHero_Mobile_Box', [[336, 280], [320, 50], [320, 100], [300, 250]], 'div-gpt-ad-1761686117928-0').addService(googletag.pubads());
            
            <?php // Content ads based on H2 count
            if ($h2_count >= 1) : ?>
            googletag.defineSlot('/23317265155/mobile_in_content_one', [300, 75], 'div-gpt-ad-1758969680865-0').addService(googletag.pubads());
            <?php endif; ?>
            
            <?php if ($h2_count >= 3) : ?>
            googletag.defineSlot('/23317265155/mobile_Geotour_Article_Body2_banner', [[300, 75], [300, 100], [300, 250]], 'div-gpt-ad-1759346647953-0').addService(googletag.pubads());
            <?php endif; ?>
            
            <?php if ($h2_count >= 5) : ?>
            googletag.defineSlot('/23317265155/mobile_Geotour_Article_Body3_banner', [[300, 75], [300, 100], [300, 250]], 'div-gpt-ad-1758987837272-0').addService(googletag.pubads());
            <?php endif; ?>
            
            <?php if ($h2_count >= 7) : ?>
            googletag.defineSlot('/23317265155/mobile_Geotour_Article_Body4_banner', [[300, 100], [300, 250], [300, 75]], 'div-gpt-ad-1758987927787-0').addService(googletag.pubads());
            <?php endif; ?>

        <?php endif; // End mobile/desktop check ?>
    <?php endif; // End listing-gam-01 check ?>

    <?php // Anchor Ad (Bottom Sticky)
    if (in_array('listing-gam-anchor-bottom', $adtype)) : ?>
        <?php if ( ! wp_is_mobile() ) : // Desktop Anchor Ad ?>
        googletag.defineSlot('/23317265155/Geotour_tour_Anchor_Ad_desktop', [[728, 90], [970, 90]], 'div-gpt-ad-1761765099446-0').addService(googletag.pubads());
        <?php else : // Mobile Anchor Ad ?>
        googletag.defineSlot('/23317265155/Geotour_tour_Anchor_Ad_mobile', [[320, 100], [320, 50]], 'div-gpt-ad-1761765177186-0').addService(googletag.pubads());
        <?php endif; ?>
    <?php endif; ?>

    /* --- END: Slot Definitions --- */


    /* --- START: Global Page-Level Settings (called only once) --- */

    // Key-Value Targeting
    <?php
    $page_topic = function_exists('get_field') ? get_field('page_topic_value') : null;
    if (!empty($page_topic)) {
        echo "googletag.pubads().setTargeting('page_topic', " . json_encode($page_topic) . ");\n";
    }
    ?>
    
    // Global enabling functions
    googletag.pubads().enableSingleRequest();
    googletag.pubads().collapseEmptyDivs();
    googletag.enableServices();

    /* --- END: Global Page-Level Settings --- */
  });
</script>
<?php
    endif; // End check for ANY GAM ad type

endif; // End master conditional (!empty($adtype))
?>