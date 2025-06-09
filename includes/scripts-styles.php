<?php
// filepath: e:\visualstudio\geotour-theme\includes\scripts-styles.php
/**
 * Scripts and Styles Management
 * 
 * @package Geotour_Mobile_First
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Conditionally enqueue Leaflet for map pages
 */
function geotour_enqueue_leaflet() {
    // Only load on pages that need maps
    // Example: if (is_page_template('template-map.php') || is_singular('location')) 
    // You need a reliable way to determine if a map is needed.
    // For now, let's assume a helper function geotour_is_map_page() exists or you'll implement it.
    if (function_exists('geotour_is_map_page') && geotour_is_map_page()) {
        // Leaflet CSS
        wp_enqueue_style(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            array(),
            '1.9.4'
        );
        
        // Leaflet JS
        wp_enqueue_script(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            array(),
            '1.9.4',
            true
        );
        
        // Custom Leaflet initialization (ensure this file exists and is built by Vite if necessary)
        // If leaflet-init.js is part of your Vite build and has a manifest entry, handle it via geotour_get_vite_assets
        // Otherwise, if it's a static file:
        // wp_enqueue_script(
        //     'geotour-leaflet-init',
        //     GEOTOUR_THEME_URI . '/src/js/leaflet/leaflet-init.js', // Adjust path if it's static or from build
        //     array('leaflet'), // Depends on your main script if it includes Leaflet logic
        //     GEOTOUR_THEME_VERSION,
        //     true
        // );
    }
}
add_action('wp_enqueue_scripts', 'geotour_enqueue_leaflet');

// Function to get the Vite manifest and asset URLs
function geotour_get_vite_assets() {
    static $manifest = null;
    static $is_dev = null;

    if ($is_dev === null) {
        // Check for Vite dev server indicator. Using WP_ENVIRONMENT_TYPE is more robust.
        $is_dev = (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'development' && file_exists(GEOTOUR_THEME_DIR . '/vite.config.js'));
        // Fallback if WP_ENVIRONMENT_TYPE is not set, but vite.config.js exists (less reliable for prod builds on dev server)
        if (!$is_dev && $is_dev === null) { // only if WP_ENVIRONMENT_TYPE wasn't 'development'
             $is_dev = file_exists(GEOTOUR_THEME_DIR . '/vite.config.js');
        }
    }

    if ($is_dev) {
        // Attempt to read Vite dev server URI from a .vite/dev.json file or similar if available
        // For now, hardcoding but this should be dynamic if possible (e.g., from a file Vite dev server touches)
        $vite_dev_server_uri = 'http://localhost:5173'; 
        return [
            'base_uri' => $vite_dev_server_uri,
            'js_src'   => $vite_dev_server_uri . '/src/js/main.js',
            // CSS is typically injected by the JS in Vite dev mode
        ];
    }

    if ($manifest === null) {
        $manifest_path = GEOTOUR_THEME_DIR . '/build/.vite/manifest.json';
        if (file_exists($manifest_path)) {
            $manifest = json_decode(file_get_contents($manifest_path), true);
        } else {
            $manifest = []; 
        }
    }

    return [
        'manifest' => $manifest,
        'base_uri' => GEOTOUR_THEME_URI . '/build/'
    ];
}

// Enqueue scripts and styles using Vite assets
function geotour_enqueue_theme_assets() {
    $vite_assets = geotour_get_vite_assets();
    $main_js_handle = 'geotour-main-js'; // Default production handle

    if (isset($vite_assets['js_src'])) { // Development mode
        $main_js_handle = 'geotour-vite-main-js'; // Dev handle
        wp_enqueue_script($main_js_handle, $vite_assets['js_src'], array(), GEOTOUR_THEME_VERSION, true);

    } elseif (!empty($vite_assets['manifest'])) { // Production mode
        $manifest = $vite_assets['manifest'];
        $base_uri = $vite_assets['base_uri'];
        $js_entry_keys = ['src/js/main.js', 'main.js', 'index.html']; // Common Vite entry points
        $js_file = '';
        $css_files = [];

        foreach ($js_entry_keys as $key) {
            if (isset($manifest[$key])) {
                if (isset($manifest[$key]['file'])) {
                    $js_file = $base_uri . $manifest[$key]['file'];
                }
                if (isset($manifest[$key]['css'])) {
                    foreach ($manifest[$key]['css'] as $css_asset) {
                        $css_files[] = $base_uri . $css_asset;
                    }
                }
                // Also check for dynamic imports that might have CSS
                if (isset($manifest[$key]['dynamicImports'])) {
                    foreach ($manifest[$key]['dynamicImports'] as $dynamic_import_key) {
                        if (isset($manifest[$dynamic_import_key]['css'])) {
                            foreach ($manifest[$dynamic_import_key]['css'] as $dynamic_css_asset) {
                                $css_files[] = $base_uri . $dynamic_css_asset;
                            }
                        }
                    }
                }
                break; 
            }
        }

        if (!empty($js_file)) {
            wp_enqueue_script($main_js_handle, $js_file, array(), GEOTOUR_THEME_VERSION, true);
        }

        $css_files = array_unique($css_files); // Ensure unique CSS files
        foreach ($css_files as $index => $css_file) {
            wp_enqueue_style('geotour-main-css-' . $index, $css_file, array(), GEOTOUR_THEME_VERSION);
        }
    }

    // Enqueue Google Fonts (Syne)
    wp_enqueue_style('geotour-google-font-syne', 'https://fonts.googleapis.com/css2?family=Syne:wght@400;700&display=swap', array(), null);

    // Enqueue Leaflet CSS (JS is bundled by Vite)
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
    // wp_enqueue_style('leaflet-markercluster-css', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css', array('leaflet-css'), '1.5.3');
    // wp_enqueue_style('leaflet-markercluster-default-css', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css', array('leaflet-markercluster-css'), '1.5.3');

    // Localize script with data for maps if needed
    // Example: Pass listing coordinates for a single listing page
    $map_data = [];
    if (is_singular('listing')) {
        $coordinates = geotour_get_listing_coordinates(get_the_ID());
        if ($coordinates) {
            $map_data['single'] = [
                'coordinates' => [$coordinates['lat'], $coordinates['lng']],
                'popupText' => '<h5>' . get_the_title() . '</h5><p>Location details.</p>',
                'zoomLevel' => 15
            ];
        }
    }
    // Example: Pass data for an archive page (you'll need to build the $listings_data array)
    // elseif (is_post_type_archive('listing') || is_tax('listing_category')) {
    //     global $wp_query;
    //     $listings_data = geotour_generate_geojson($wp_query); // Assuming this returns suitable data
    //     $map_data['archive'] = [
    //         'listings' => $listings_data 
    //     ];
    // }

    if (!empty($map_data)) {
        wp_localize_script('vite-main-js', 'geotourMapData', $map_data);
    }

    // Localize script with theme data - always use the determined main JS handle
    wp_localize_script($main_js_handle, 'geotour_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('geotour_nonce'),
        'theme_uri' => GEOTOUR_THEME_URI,
        // Add other data your JS might need
    ));
}
add_action('wp_enqueue_scripts', 'geotour_enqueue_theme_assets');

// Add type="module" to the main Vite-generated JavaScript file
function geotour_add_type_module_to_script($tag, $handle, $src) {
    $module_handles = ['geotour-vite-main-js', 'geotour-main-js']; 

    if (in_array($handle, $module_handles)) {
        // If type="module" is already correctly set, do nothing.
        if (strpos($tag, 'type="module"') !== false) {
            return $tag;
        }

        // Remove any existing type="text/javascript" or other type attributes.
        // Corrected preg_replace pattern
        $tag = preg_replace('/\s+type=(["\'])(?:(?!\1).)*\1/', '', $tag);

        // Add type="module". This assumes the script tag starts with "<script "
        // Ensure there's a space before adding type attribute
        if (strpos($tag, 'type=') === false) { // Check again if type was removed or not present
            $tag = str_replace('<script', '<script type="module"', $tag);
        } else { // If another type attribute somehow persists or was added, try to replace it or ensure module is there
            // This case should ideally not be hit if preg_replace works
            // For safety, if a type attribute exists, we might need a more robust replacement
            // but for now, let's assume preg_replace clears it.
        }
    }
    return $tag;
}
add_filter('script_loader_tag', 'geotour_add_type_module_to_script', 10, 3);

// Optional: Add crossorigin attribute if Vite dev server needs it for module scripts
function geotour_add_crossorigin_to_dev_script($tag, $handle, $src) {
    if ('geotour-vite-main-js' === $handle) {
        if (strpos($src, 'localhost:5173') !== false || strpos($src, '127.0.0.1:5173') !== false) {
            if (strpos($tag, 'crossorigin') === false) {
                 $tag = str_replace('<script ', '<script crossorigin ', $tag);
            }
        }
    }
    return $tag;
}
// add_filter('script_loader_tag', 'geotour_add_crossorigin_to_dev_script', 10, 3); // Uncomment if CORS issues arise with Vite dev server