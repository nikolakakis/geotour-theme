<?php
// template-parts/ads/header-ads.php

// Get the ACF field value (now returns array for multi-select)
$adtype = function_exists('get_field') ? get_field('adtype') : array();

// Ensure we have an array (in case of empty or single value)
if (!is_array($adtype)) {
    $adtype = empty($adtype) ? array() : array($adtype);
}

// Only proceed if at least one ad type has been selected
if (!empty($adtype)) :

    // --- 1. Load the Consent Message Script (required for Google ad types) ---
    if (in_array('adsense_auto', $adtype) || in_array('728_header_ad', $adtype) || in_array('listing-gam-01', $adtype)) :
?>
<script async src="https://fundingchoicesmessages.google.com/i/pub-7479927233059417?ers=1" nonce="..."></script>
<script>(function() {function signalGooglefcPresent() {if (!window.frames['googlefcPresent']) {if (document.body) {const iframe = document.createElement('iframe'); iframe.style = 'width: 0; height: 0; border: none; z-index: -1000; left: -1000px; top: -1000px;'; iframe.style.display = 'none'; iframe.name = 'googlefcPresent'; document.body.appendChild(iframe);} else {setTimeout(signalGooglefcPresent, 0);}}}signalGooglefcPresent();})();</script>
<?php 
    endif;

    // --- 2. Conditionally load the correct ad scripts ---
    if (in_array('adsense_auto', $adtype)) :
        // --- OPTION A: AdSense Auto Ads ---
?>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7479927233059417" crossorigin="anonymous"></script>

<?php
    endif;
    
    // --- Load GPT library once for all GAM ad types ---
    if (in_array('728_header_ad', $adtype) || in_array('listing-gam-01', $adtype)) :
?>
<script async src="https://securepubads.g.doubleclick.net/tag/js/gpt.js" crossorigin="anonymous"></script>
<?php
    endif;
    
    if (in_array('728_header_ad', $adtype)) :
        // --- OPTION B: GAM Homepage Header Ad ---
?>
<script>
  window.googletag = window.googletag || {cmd: []};
  googletag.cmd.push(function() {
    googletag.defineSlot('/23317265155/geotour-homepage-01', [728, 90], 'div-gpt-ad-1758305678423-0').addService(googletag.pubads());
    googletag.pubads().collapseEmptyDivs();
    googletag.pubads().enableSingleRequest();
    googletag.enableServices();
  });
</script>

<?php
    endif;
    
    // --- START: COMBINED SCRIPT ---
if (in_array('listing-gam-01', $adtype)) :
    // --- OPTION C: GAM Listing Page Ads (Sidebar and Mobile) ---
    
    // Count H2 tags in post content to determine which ad slots to define
    global $post;
    $h2_count = 0;
    if (isset($post) && !empty($post->post_content)) {
        $h2_count = substr_count(strtolower($post->post_content), '<h2');
    }
?>
<script>
  window.googletag = window.googletag || {cmd: []};
  googletag.cmd.push(function() {
    
    // --- START: Key-Value Targeting Logic ---
    <?php
    // Get the value from your ACF text field 'page_topic_value'.
    $page_topic = function_exists('get_field') ? get_field('page_topic_value') : null;

    // If a value exists in the field, print the setTargeting script.
    if (!empty($page_topic)) {
        // We use json_encode() to safely handle the string and ensure it has the correct quotes.
        echo "googletag.pubads().setTargeting('page_topic', " . json_encode($page_topic) . ");\n";
    }
    ?>
    // --- END: Key-Value Targeting Logic ---
    // Defines the Top Sidebar Ad Slot (Visible on all devices)
    googletag.defineSlot('/23317265155/listing-sidebar-01', [[300, 250], [336, 280], [300, 600]], 'div-gpt-ad-1761463070811-0').addService(googletag.pubads());    

    // Defines the Bottom Sidebar Ad Slot (Visible on all devices)
    googletag.defineSlot('/23317265155/Geotour_Listing_Sidebar_Box_bottom', [[300, 100], [300, 250], [300, 600]], 'div-gpt-ad-1761461807399-0').addService(googletag.pubads());

    <?php if ( ! wp_is_mobile() ) : // DESKTOP ONLY ADS ?>
    // SIDE RAIL ADS for desktop
    // right side
    googletag.defineSlot('/23317265155/Geotour_desktop_SideRail_Right', [[160, 600], [120, 600]], 'div-gpt-ad-1761467879664-0').addService(googletag.pubads());
    // left side
    googletag.defineSlot('/23317265155/Geotour_desktop_SideRail_Left', [[160, 600], [120, 600]], 'div-gpt-ad-1761468110147-0').addService(googletag.pubads());
    
    <?php if ($h2_count >= 1) : ?>
    // Defines the Responsive Article Body Ad Slot above the first h2 heading for wider screens      
    googletag.defineSlot('/23317265155/Geotour_Article_Body1_Responsive', [300, 250], 'div-gpt-ad-1759438267926-0').addService(googletag.pubads());
    <?php endif; ?>
    
    <?php if ($h2_count >= 3) : ?>
    // Defines the Responsive Article Body Ad Slot above the third h2 heading for wider screens            
    googletag.defineSlot('/23317265155/Geotour_Article_Body2_banner', [[980, 120], [960, 90], [970, 90], [728, 90], [750, 200], [970, 250], [950, 90], [750, 100], [750, 300], [930, 180]], 'div-gpt-ad-1760784178571-0').addService(googletag.pubads());
    <?php endif; ?>
    
    <?php if ($h2_count >= 5) : ?>
    // Defines the Responsive Article Body Ad Slot above the fifth h2 heading for wider screens (Body3a)
    googletag.defineSlot('/23317265155/Geotour_Article_Body3a_banner', [300, 250], 'div-gpt-ad-23318416161-0').addService(googletag.pubads());
    // Defines the Responsive Article Body Ad Slot above the fifth h2 heading for wider screens (Body3b)
    googletag.defineSlot('/23317265155/Geotour_Article_Body3b_banner', [300, 250], 'div-gpt-ad-23318416257-0').addService(googletag.pubads());
    <?php endif; ?>
    
    <?php if ($h2_count >= 7) : ?>
    // Defines the Responsive Article Body Ad Slot above the seventh h2 heading for wider screens
    googletag.defineSlot('/23317265155/Geotour_Article_Body4_banner', [728, 90], 'div-gpt-ad-1758981672253-0').addService(googletag.pubads());
    <?php endif; ?>
    <?php endif; // End DESKTOP ONLY ?>

    <?php if ( wp_is_mobile() ) : // MOBILE ONLY ADS ?>
    // Defines the Under-Hero Mobile Ad Slot
    googletag.defineSlot('/23317265155/Geotour_Listing_UnderHero_Mobile_Box', [[336, 280], [300, 250]], 'div-gpt-ad-1758916182848-0').addService(googletag.pubads());
    
    <?php if ($h2_count >= 1) : ?>
    // Defines the Responsive Article Body Ad Slot above the first h2 heading for narrower screens
    googletag.defineSlot('/23317265155/mobile_in_content_one', [300, 75], 'div-gpt-ad-1758969680865-0').addService(googletag.pubads());
    <?php endif; ?>
    
    <?php if ($h2_count >= 3) : ?>
    // mobile 2 - above the third h2 heading
    googletag.defineSlot('/23317265155/mobile_Geotour_Article_Body2_banner', [[300, 75], [300, 100], [300, 250]], 'div-gpt-ad-1759346647953-0').addService(googletag.pubads());
    <?php endif; ?>
    
    <?php if ($h2_count >= 5) : ?>
    // Defines the Responsive Article Body Ad Slot above the fifth h2 heading for narrower screens (mobile Body3)
    googletag.defineSlot('/23317265155/mobile_Geotour_Article_Body3_banner', [[300, 75], [300, 100], [300, 250]], 'div-gpt-ad-23318356914-0').addService(googletag.pubads());
    <?php endif; ?>
    
    <?php if ($h2_count >= 7) : ?>
    // Defines the Responsive Article Body Ad Slot above the seventh h2 heading for narrower screens
    googletag.defineSlot('/23317265155/mobile_Geotour_Article_Body4_banner', [[300, 100], [300, 250], [300, 75]], 'div-gpt-ad-1758987927787-0').addService(googletag.pubads());
    <?php endif; ?>
    <?php endif; // End MOBILE ONLY ?>

    // --- Global settings for all ads on the page (defined only once) ---
    googletag.pubads().enableSingleRequest();
    googletag.pubads().collapseEmptyDivs();
    googletag.enableServices();
  });
</script>
<?php
// --- END: COMBINED SCRIPT ---
endif; // End listing-gam-01 conditional
    
    // --- 3. GetYourGuide Widget ---
    if (in_array('getyourguide', $adtype)) :
?>
<!-- GetYourGuide Widget -->
<!-- GetYourGuide Analytics -->

<script async defer src="https://widget.getyourguide.com/dist/pa.umd.production.min.js" data-gyg-partner-id="QIP05M4"></script>
<?php
    endif; // End getyourguide conditional

endif; // End master conditional (!empty($adtype))
?>