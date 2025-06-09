<?php
/**
 * Geotour Theme Data Migration WP-CLI Command
 *
 * Defines the WP-CLI command for migrating data.
 *
 * @package Geotour_Mobile_First
 * @subpackage Migration
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/migration-logic.php';

/**
 * Implements the WP-CLI command for migrating Geotour theme meta data.
 */
class Geotour_Migration_Command {
    /**
     * Migrates custom field data from old theme meta keys to new ACF fields.
     *
     * ## OPTIONS
     *
     * [--cleanup]
     * : If set, the command will attempt to delete the old meta keys after successful migration.
     *
     * [--map-file=<file>]
     * : Path to a custom JSON field map file. Defaults to 'field-map.json' in the migration folder.
     *
     * [--dry-run]
     * : Perform a dry run. Outputs the actions that would be taken without actually changing any data.
     *
     * ## EXAMPLES
     *
     *     # Perform a dry run to see what would happen
     *     wp geotour migrate-meta --dry-run
     *
     *     # Perform the actual migration
     *     wp geotour migrate-meta
     *
     *     # Perform the migration and cleanup old meta keys
     *     wp geotour migrate-meta --cleanup
     *
     *     # Perform migration using a custom field map and cleanup
     *     wp geotour migrate-meta --map-file=/path/to/your/custom-map.json --cleanup
     *
     * @when after_wp_load
     */
    public function migrate_meta( $args, $assoc_args ) {
        $start_time = microtime(true); // Record start time

        $cleanup = isset( $assoc_args['cleanup'] );
        $dry_run = isset( $assoc_args['dry-run'] );
        $map_file_path = isset( $assoc_args['map-file'] ) ? $assoc_args['map-file'] : dirname( __FILE__ ) . '/field-map.json';

        if ( ! file_exists( $map_file_path ) ) {
            WP_CLI::error( "Field map file not found: {$map_file_path}" );
            return;
        }

        $field_map_json = file_get_contents( $map_file_path );
        if ( $field_map_json === false ) {
            WP_CLI::error( "Could not read field map file: {$map_file_path}" );
            return;
        }

        $field_map = json_decode( $field_map_json, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            WP_CLI::error( "Invalid JSON in field map file ({$map_file_path}): " . json_last_error_msg() );
            return;
        }

        if ( empty( $field_map ) ) {
            WP_CLI::warning( "Field map is empty. No data to migrate. ({$map_file_path})" );
            return;
        }

        if ( $dry_run ) {
            WP_CLI::line( '\\033[1;33mDRY RUN MODE ENABLED\\033[0m' );
        } else {
            WP_CLI::line( "Starting data migration..." );
            if ( $cleanup ) {
                WP_CLI::confirm( "Are you sure you want to delete the old meta keys after migration? This action cannot be undone." );
            }
        }
        
        if ( $cleanup ) {
            WP_CLI::line( "Cleanup flag is set. Old meta keys will be processed for deletion after migration." );
        }
        WP_CLI::line( "Using field map: {$map_file_path}" );

        geotour_migrate_meta_data( $field_map, $cleanup, $dry_run );

        $end_time = microtime(true); // Record end time
        $duration = round($end_time - $start_time, 2); // Calculate duration
        WP_CLI::line( "--------------------------------------------------" );
        WP_CLI::line( "Command execution time: {$duration} seconds" );
        WP_CLI::line( "--------------------------------------------------" );
    }
}

// Register the command
WP_CLI::add_command( 'geotour', 'Geotour_Migration_Command' );
?>
