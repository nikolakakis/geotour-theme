<?php
// template-parts/ads/header-ads.php
// Output AdSense script if ACF field 'adtype' is 'adsense_auto'
$adtype = function_exists('get_field') ? get_field('adtype') : '';
if ($adtype === 'adsense_auto') : ?>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7479927233059417" crossorigin="anonymous"></script>
<script async src="https://fundingchoicesmessages.google.com/i/pub-7479927233059417?ers=1"></script><script>(function() {function signalGooglefcPresent() {if (!window.frames['googlefcPresent']) {if (document.body) {const iframe = document.createElement('iframe'); iframe.style = 'width: 0; height: 0; border: none; z-index: -1000; left: -1000px; top: -1000px;'; iframe.style.display = 'none'; iframe.name = 'googlefcPresent'; document.body.appendChild(iframe);} else {setTimeout(signalGooglefcPresent, 0);}}}signalGooglefcPresent();})();</script>
<?php endif; ?>
