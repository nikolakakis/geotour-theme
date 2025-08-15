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
        // Leaflet CSS - This is now handled in geotour_enqueue_theme_assets()
        // wp_enqueue_style(
        //     'leaflet-css',
        //     'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
        //     array(),
        //     '1.9.4'
        // );
        
        // Leaflet JS - This is now handled by Vite bundling (imported in src/js/modules/maps/main.js)
        // wp_enqueue_script(
        //     'leaflet',
        //     'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        //     array(),
        //     '1.9.4',
        //     true
        // );
        
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
// add_action('wp_enqueue_scripts', 'geotour_enqueue_leaflet'); // Commented out as its functionality is handled elsewhere or by Vite

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

    // Enqueue Leaflet CSS (JS is bundled by Vite) - TEMPORARILY DISABLED TO AVOID CONFLICTS
    // wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');

    // TEMPORARILY SIMPLIFIED - Localize script with basic data
    $map_data = [];
    if (is_singular('listing')) {
        $map_data['single'] = [
            'coordinates' => [35.2401, 24.8093], // Default Crete coordinates
            'popupText' => '<h5>Test Location</h5><p>This is a test marker.</p>',
            'zoomLevel' => 10
        ];
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
        wp_localize_script($main_js_handle, 'geotourMapData', $map_data); // Use the dynamic $main_js_handle
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

// Add type="module" and defer to the main Vite-generated JavaScript file
function geotour_add_type_module_to_script($tag, $handle, $src) {
    $module_handles = ['geotour-vite-main-js', 'geotour-main-js']; 

    if (in_array($handle, $module_handles)) {
        // If type="module" is already correctly set, do nothing for type.
        if (strpos($tag, 'type="module"') !== false) {
            // But still check for defer
            if (strpos($tag, 'defer') === false) {
                $tag = str_replace('<script', '<script defer', $tag);
            }
            return $tag;
        }

        // Remove any existing type="text/javascript" or other type attributes.
        $tag = preg_replace('/\s+type=(["\'])(?:(?!\1).)*\1/', '', $tag);

        // Add type="module" and defer
        if (strpos($tag, 'type=') === false) {
            $tag = str_replace('<script', '<script type="module" defer', $tag);
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

/**
 * Defer non-critical scripts for better LCP performance
 * This is much safer than deferring jQuery
 */
function geotour_defer_non_critical_scripts($tag, $handle, $src) {
    // Scripts that can be safely deferred (add more as needed)
    $scripts_to_defer = array(
        'geotour-main-js',
        'geotour-vite-main-js',
        'wp-embed',
        'comment-reply'
    );
    
    // Don't defer in admin or during AJAX
    if (is_admin() || wp_doing_ajax()) {
        return $tag;
    }
    
    // Check if this script should be deferred
    if (in_array($handle, $scripts_to_defer)) {
        // Only add defer if it's not already there
        if (strpos($tag, 'defer') === false) {
            $tag = str_replace('<script', '<script defer', $tag);
        }
    }
    
    return $tag;
}
add_filter('script_loader_tag', 'geotour_defer_non_critical_scripts', 5, 3);

/**
 * Additional LCP optimizations for critical resource preloading
 */
function geotour_preload_critical_resources() {
    // Preload hero images with high priority
    if (is_front_page() || is_home()) {
        $homepage_id = get_option('page_on_front');
        if ($homepage_id) {
            $hero_image = get_the_post_thumbnail_url($homepage_id, 'full');
            if ($hero_image) {
                echo '<link rel="preload" as="image" href="' . esc_url($hero_image) . '" fetchpriority="high">' . "\n";
            }
        }
    } elseif (is_singular()) {
        // Preload featured images on single posts/pages
        $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
        if ($featured_image) {
            echo '<link rel="preload" as="image" href="' . esc_url($featured_image) . '" fetchpriority="high">' . "\n";
        }
    }
    
    // Preload Google Fonts
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    
    // Preload critical CSS if you have inline critical CSS
    $vite_assets = geotour_get_vite_assets();
    if (!empty($vite_assets['manifest'])) {
        $manifest = $vite_assets['manifest'];
        $base_uri = $vite_assets['base_uri'];
        
        // Look for main CSS file to preload
        $css_entry_keys = ['src/js/main.js', 'main.js', 'index.html'];
        foreach ($css_entry_keys as $key) {
            if (isset($manifest[$key]['css'])) {
                foreach ($manifest[$key]['css'] as $css_asset) {
                    echo '<link rel="preload" as="style" href="' . esc_url($base_uri . $css_asset) . '">' . "\n";
                    break 2; // Only preload the first critical CSS file
                }
            }
        }
    }
}
add_action('wp_head', 'geotour_preload_critical_resources', 1);