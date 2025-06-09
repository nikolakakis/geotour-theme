<?php
/**
 * Geotour Theme Data Migration Logic
 *
 * This file contains the core functions for migrating data
 * from old meta keys to new ACF fields.
 *
 * @package Geotour_Mobile_First
 * @subpackage Migration
 * 
 *  Use examples:
 * wp geotour migrate_meta --map-file=e:\visualstudio\geotour-theme\migration\field-map-partial01.json --dry-run
 * wp geotour migrate_meta --map-file=e:\visualstudio\geotour-theme\migration\field-map.json --dry-run --benchmark
 * 
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Migrates post meta data based on a field map.
 *
 * @param array $field_map The mapping of old meta keys to new ACF fields.
 * @param bool  $cleanup   Whether to delete old meta keys after migration.
 * @param bool  $dry_run   Whether to perform a dry run (log actions without making changes).
 */
function geotour_migrate_meta_data( $field_map, $cleanup = false, $dry_run = false ) {
    if ( $dry_run ) {
        WP_CLI::line( '\\033[1;33mDRY RUN MODE ENABLED: No actual data changes will be made.\\033[0m' );
    }

    if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
        WP_CLI::error( 'ACF plugin is not active. Please activate ACF and try again.' );
        return;
    }

    $args = array(
        'post_type'      => 'listing',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => 'any',
    );
    $all_listing_ids = get_posts( $args );

    if ( empty( $all_listing_ids ) ) {
        WP_CLI::warning( "No \'listing\' posts found to process." );
        return;
    }

    $migrated_count = 0;
    $updated_posts_count = 0;
    $cleaned_keys_count = 0;
    $total_posts_processed_overall = 0;

    WP_CLI::line( sprintf( "Found %d \'listing\' posts to check.", $total_posts_processed_overall ) );

    wp_suspend_cache_addition(true);

    foreach ( $field_map as $map_item ) {
        $old_key = $map_item['old_meta_key'];
        $new_key = $map_item['new_acf_field_name'];

        foreach ( $all_listing_ids as $listing_id ) {
            $old_value = get_post_meta( $listing_id, $old_key, true );
            $transformed_value = $old_value; // Initialize with old value, will be overwritten for position

            if ( $old_value !== '' && $old_value !== null ) {
                WP_CLI::log( "  Post ID {$listing_id}: Found old meta key '{$old_key}' with raw value: '{$old_value}'" );

                // Special handling for the 'position' field
                if ( $new_key === 'position' ) {
                    if ( is_string($old_value) ) {
                        $parts = explode(',', $old_value);
                        if (count($parts) >= 2) { // Need at least lat and lng
                            $parsed_lat = trim($parts[0]);
                            $parsed_lng = trim($parts[1]);
                            $parsed_zoom = isset($parts[2]) ? (int)trim($parts[2]) : 16; // Default zoom is 16

                            $transformed_value = [
                                'lat'        => $parsed_lat,
                                'lng'        => $parsed_lng,
                                'zoom'       => $parsed_zoom,
                                'markers'    => [
                                    [
                                        'label'         => '', // Will be auto-filled by ACF or can be manually set post-migration
                                        'default_label' => '',
                                        'lat'           => $parsed_lat, // Crucial: Marker's latitude
                                        'lng'           => $parsed_lng, // Crucial: Marker's longitude
                                        'geocode'       => [], 
                                        'uuid'          => 'marker_' . uniqid(),
                                    ]
                                ],
                                'address'    => '', // Top-level address, ACF might auto-populate this
                                'layers'     => ['OpenStreetMap.Mapnik'], // Common default
                                'version'    => '1.5.7', // Check your OSM plugin version, this is a common one
                                'center_lat' => $parsed_lat,
                                'center_lng' => $parsed_lng
                            ];
                            WP_CLI::log( "    Transformed data for 'position' field." );
                        } else {
                            WP_CLI::warning( "    Post ID {$listing_id}: Could not parse old value '{$old_value}' for 'position' field. Skipping transformation." );
                            // Decide if you want to skip this field entirely for this post if parsing fails
                            // For now, it will try to update with the original $old_value if not transformed, which is likely not what you want for 'position'.
                            // To skip, you could 'continue;' here or ensure $transformed_value remains $old_value and rely on type differences.
                            // A safer bet is to explicitly skip if transformation fails for 'position':
                            // continue; // Skips to the next listing_id for this $map_item
                        }
                    } else {
                         WP_CLI::warning( "    Post ID {$listing_id}: Old value for 'position' is not a string as expected. Value: " . print_r($old_value, true) );
                         // continue; // Consider skipping
                    }
                }
                // End of special handling for 'position'

                $current_acf_value = get_field( $new_key, $listing_id );

                // Comparison logic
                $needs_update = true;
                if ( $new_key === 'position' && is_array($current_acf_value) && is_array($transformed_value) ) {
                    // More specific check for position field, comparing critical marker coordinates
                    if ( isset($current_acf_value['markers'][0]['lat'], $current_acf_value['markers'][0]['lng']) &&
                         isset($transformed_value['markers'][0]['lat'], $transformed_value['markers'][0]['lng']) &&
                         (string)$current_acf_value['markers'][0]['lat'] == (string)$transformed_value['markers'][0]['lat'] &&
                         (string)$current_acf_value['markers'][0]['lng'] == (string)$transformed_value['markers'][0]['lng'] ) {
                        WP_CLI::log( "    ACF field '{$new_key}' (position) already has the same marker coordinates. No update needed." );
                        $needs_update = false;
                    }
                } else if ( $current_acf_value == $transformed_value ) { // Generic comparison for other fields
                    WP_CLI::log( "    ACF field '{$new_key}' already has the same value. No update needed." );
                    $needs_update = false;
                }
                
                if ( $needs_update ) {
                    if ( $dry_run ) {
                        WP_CLI::log( "    [DRY RUN] Would update ACF field '{$new_key}' for post ID {$listing_id}." );
                        if ($new_key === 'position') {
                             WP_CLI::log( "      [DRY RUN] With transformed position data (first marker lat/lng): {$transformed_value['markers'][0]['lat']},{$transformed_value['markers'][0]['lng']}" );
                        } else {
                             WP_CLI::log( "      [DRY RUN] From: " . (is_array($current_acf_value) ? json_encode($current_acf_value) : $current_acf_value) . " To: " . (is_array($transformed_value) ? json_encode($transformed_value) : $transformed_value) );
                        }
                        $migrated_count++; // Count as if it were migrated for reporting
                    } else {
                        // Use $transformed_value for the update
                        $update_result = update_field( $new_key, $transformed_value, $listing_id );
                        if ( $update_result ) {
                            WP_CLI::success( "    Successfully migrated '{$old_key}' to '{$new_key}' for post ID {$listing_id}." );
                            $migrated_count++;
                        } else {
                            WP_CLI::warning( "    Failed to migrate '{$old_key}' to '{$new_key}' for post ID {$listing_id}. update_field returned false (value might be the same or an error occurred)." );
                        }
                    }
                }

                if ( $cleanup ) {
                    if ( $dry_run ) {
                        WP_CLI::log( "  [DRY RUN] Would delete old meta key '{$old_key}'." );
                        $cleaned_keys_count++;
                    } else {
                        if ( delete_post_meta( $listing_id, $old_key ) ) {
                            WP_CLI::log( "  Cleaned up old meta key '{$old_key}' for post ID {$listing_id}." );
                            $cleaned_keys_count++;
                        } else {
                            WP_CLI::warning( "  Could not clean up old meta key '{$old_key}' for post ID {$listing_id}." );
                        }
                    }
                }
            } else {
                // WP_CLI::log( "  Old meta key '{$old_key}' not found or empty for post ID {$listing_id}." );
            }
        }
    }

    wp_suspend_cache_addition(false);

    // Overall Summary
    WP_CLI::line( '--------------------------------------------------' );
    if ( $dry_run ) {
        WP_CLI::line( 'DRY RUN SUMMARY:' );
        WP_CLI::line( "  Total posts processed: " . count( $all_listing_ids ) );
        WP_CLI::line( "  Total posts that would have been updated: " . $updated_posts_count );
        WP_CLI::line( "  Total individual fields that would have been migrated: " . $migrated_count );
        if ( $cleanup ) {
            WP_CLI::line( "  Total old meta keys that would have been cleaned up: " . $cleaned_keys_count );
        }
    } else {
        WP_CLI::success( "Migration process completed." );
        WP_CLI::line( "  Total posts processed: " . count( $all_listing_ids ) );
        WP_CLI::line( "  Total posts updated: " . $updated_posts_count );
        WP_CLI::line( "  Total individual fields migrated: " . $migrated_count );
        if ( $cleanup ) {
            WP_CLI::line( "  Total old meta keys cleaned up: " . $cleaned_keys_count );
        }
    }
    WP_CLI::line( '--------------------------------------------------' );
}
?>
