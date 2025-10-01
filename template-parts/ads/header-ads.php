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
    
    if (in_array('728_header_ad', $adtype)) :
        // --- OPTION B: GAM Homepage Header Ad ---
?>
<script async src="https://securepubads.g.doubleclick.net/tag/js/gpt.js" crossorigin="anonymous"></script>
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
?>
<script async src="https://securepubads.g.doubleclick.net/tag/js/gpt.js" crossorigin="anonymous"></script>
<script>
  window.googletag = window.googletag || {cmd: []};
  googletag.cmd.push(function() {
    
    // Defines the Sidebar Ad Slot (Visible on all devices)
    googletag.defineSlot('/23317265155/listing-sidebar-01', [[336, 280], [300, 600], [300, 250]], 'div-gpt-ad-1758826394692-0').addService(googletag.pubads());

    // Defines the Under-Hero Mobile Ad Slot
    googletag.defineSlot('/23317265155/Geotour_Listing_UnderHero_Mobile_Box', [[336, 280], [300, 250]], 'div-gpt-ad-1758916182848-0').addService(googletag.pubads());
    // Defines the Responsive Article Body Ad Slot above the first h2 heading for wider screens    
    googletag.defineSlot('/23317265155/Geotour_Article_Body1_Responsive', [300, 250], 'div-gpt-ad-1759076403160-0').addService(googletag.pubads());
    // Defines the Responsive Article Body Ad Slot above the first h2 heading for narrower screens
    googletag.defineSlot('/23317265155/mobile_in_content_one', [300, 75], 'div-gpt-ad-1758969680865-0').addService(googletag.pubads());
    // Defines the Responsive Article Body Ad Slot above the second h2 heading for wider screens
    googletag.defineSlot('/23317265155/Geotour_Article_Body2_banner', [728, 90], 'div-gpt-ad-1758981125616-0').addService(googletag.pubads());
    // Defines the Responsive Article Body Ad Slot above the second h2 heading for narrower screens
    googletag.defineSlot('/23317265155/mobile_Geotour_Article_Body2_banner', [[300, 75], [300, 100], [300, 250]], 'div-gpt-ad-1758987712673-0').addService(googletag.pubads());
    // Defines the Responsive Article Body Ad Slot above the third h2 heading for wider screens
    //3a
    googletag.defineSlot('/23317265155/Geotour_Article_Body3a_banner', [300, 250], 'div-gpt-ad-1759074904699-0').addService(googletag.pubads());
    //3b
    googletag.defineSlot('/23317265155/Geotour_Article_Body3b_banner', [300, 250], 'div-gpt-ad-1759075083666-0').addService(googletag.pubads());
    //3
    //googletag.defineSlot('/23317265155/Geotour_Article_Body3_banner', [728, 90], 'div-gpt-ad-1758981576053-0').addService(googletag.pubads());
    // Defines the Responsive Article Body Ad Slot above the third h2 heading for narrower screens
    googletag.defineSlot('/23317265155/mobile_Geotour_Article_Body3_banner', [[300, 100], [300, 250], [300, 75]], 'div-gpt-ad-1758987837272-0').addService(googletag.pubads());
    // Defines the Responsive Article Body Ad Slot above the fourth h2 heading for wider screens
    googletag.defineSlot('/23317265155/Geotour_Article_Body4_banner', [728, 90], 'div-gpt-ad-1758981672253-0').addService(googletag.pubads());
    // Defines the Responsive Article Body Ad Slot above the fourth h2 heading for narrower screens
    googletag.defineSlot('/23317265155/mobile_Geotour_Article_Body4_banner', [[300, 100], [300, 250], [300, 75]], 'div-gpt-ad-1758987927787-0').addService(googletag.pubads());







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