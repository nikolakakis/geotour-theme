<?php
/**
 * Simple map test page
 * Access via: your-site.com/wp-content/themes/geotour-mobile-first/map-test.php
 */

// Include WordPress
require_once('../../../wp-load.php');
get_header();
?>

<style>
.test-map-container {
    width: 100%;
    height: 400px;
    border: 2px solid #ddd;
    margin: 2rem 0;
    background: #f0f0f0;
}
</style>

<div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <h1>Map Test Page</h1>
    <p>This is a simple test to verify the map is working.</p>
    
    <div id="listing-map" class="test-map-container geotour-map-container"></div>
    
    <script>
        console.log('Map test page loaded');
        console.log('Leaflet available:', typeof L !== 'undefined');
    </script>
</div>

<?php get_footer(); ?>