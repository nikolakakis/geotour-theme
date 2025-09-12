<?php
// template-parts/ads/header-ads.php
// Output AdSense script if ACF field 'adtype' is 'adsense_auto'
$adtype = function_exists('get_field') ? get_field('adtype') : '';
if ($adtype === 'adsense_auto') : ?>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7479927233059417" crossorigin="anonymous"></script>
<?php endif; ?>
