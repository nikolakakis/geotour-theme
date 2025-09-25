<?php
// template-parts/ads/header-ads.php

// Get the ACF field value
$adtype = function_exists('get_field') ? get_field('adtype') : '';

// Only proceed if an ad type has been selected
if ( ! empty( $adtype ) ) :

    // --- 1. Load the Consent Message Script (required for any ad type) ---
?>
<script async src="https://fundingchoicesmessages.google.com/i/pub-7479927233059417?ers=1" nonce="..."></script>
<script>(function() {function signalGooglefcPresent() {if (!window.frames['googlefcPresent']) {if (document.body) {const iframe = document.createElement('iframe'); iframe.style = 'width: 0; height: 0; border: none; z-index: -1000; left: -1000px; top: -1000px;'; iframe.style.display = 'none'; iframe.name = 'googlefcPresent'; document.body.appendChild(iframe);} else {setTimeout(signalGooglefcPresent, 0);}}}signalGooglefcPresent();})();</script>

<?php
    // --- 2. Conditionally load the correct ad script ---
    if ($adtype === 'adsense_auto') :
        // --- OPTION A: AdSense Auto Ads ---
?>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7479927233059417" crossorigin="anonymous"></script>

<?php
    elseif ($adtype === '728_header_ad') :
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
    // --- START: NEWLY ADDED CODE ---
    elseif ($adtype === 'listing-gam-01') :
        // --- OPTION C: GAM Listing Page Ads ---
?>
<script async src="https://securepubads.g.doubleclick.net/tag/js/gpt.js" crossorigin="anonymous"></script>
<script>
  window.googletag = window.googletag || {cmd: []};
  googletag.cmd.push(function() {
    googletag.defineSlot('/23317265155/listing-sidebar-01', [[336, 280], [300, 600], [300, 250]], 'div-gpt-ad-1758826394692-0').addService(googletag.pubads());
    googletag.pubads().enableSingleRequest();
    googletag.pubads().collapseEmptyDivs();
    googletag.enableServices();
  });
</script>
<?php
    // --- END: NEWLY ADDED CODE ---
    endif; // End ad type conditional

endif; // End master conditional (!empty($adtype))
?>