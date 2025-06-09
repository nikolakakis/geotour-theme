<?php
/**
 * Geotour Mobile First Theme Functions
 * 
 * Main functions file - acts as a loader for organized includes
 * 
 * @package Geotour_Mobile_First
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('GEOTOUR_THEME_VERSION', '1.0.0');
define('GEOTOUR_THEME_DIR', get_template_directory());
define('GEOTOUR_THEME_URI', get_template_directory_uri());

// Core theme setup
require_once GEOTOUR_THEME_DIR . '/includes/theme-support.php';

// Theme utilities and helpers (MUST be included BEFORE scripts-styles.php)
require_once GEOTOUR_THEME_DIR . '/includes/utils.php';

// Scripts and styles management
require_once GEOTOUR_THEME_DIR . '/includes/scripts-styles.php';

// Navigation specific helpers (like custom walkers)
require_once GEOTOUR_THEME_DIR . '/includes/navigation.php';

// Custom post types and taxonomies
require_once GEOTOUR_THEME_DIR . '/includes/custom-post-types/listing.php';
// require_once GEOTOUR_THEME_DIR . '/includes/taxonomies/listing-taxonomies.php';

// Custom hooks and filters (Uncomment when ready)
// require_once GEOTOUR_THEME_DIR . '/includes/hooks.php'; // Note: your file is named hook.php, consider renaming to hooks.php for consistency or update here

// Admin customizations (Uncomment when ready)
// require_once GEOTOUR_THEME_DIR . '/includes/admin/admin-init.php';

// API endpoints for AJAX and custom functionality (Uncomment when ready)
// require_once GEOTOUR_THEME_DIR . '/includes/api/api-init.php';

/**
 * Temporary inclusion of the data migration tools.
 * REMOVE THIS AND THE /migration FOLDER AFTER MIGRATION IS COMPLETE.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once get_template_directory() . '/migration/cli-command.php';
}

add_action('after_setup_theme', function() {
    register_nav_menus([
        'primary' => __('Primary Menu', 'geotour-theme'),
    ]);
});