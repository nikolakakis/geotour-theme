<?php
/**
 * Template part for displaying search results header with summary counts
 *
 * @package Geotour_Mobile_First
 */

// Get search query and results
$search_query = get_search_query();
$total_results = $wp_query->found_posts;

// Get counts by post type
$post_type_counts = geotour_get_search_results_by_type($search_query);
?>

<header class="search-results-header">
    <div class="search-query-info">
        <h1 class="search-title">
            <?php
            printf(
                /* translators: %s: search query. */
                esc_html__('Search Results for "%s"', 'geotour'),
                '<span class="search-query">' . esc_html($search_query) . '</span>'
            );
            ?>
        </h1>
        
        <div class="search-summary">
            <?php if ($total_results > 0) : ?>
                <p class="results-count">
                    <?php
                    printf(
                        /* translators: %d: number of search results. */
                        _n(
                            'Found %d result',
                            'Found %d results',
                            $total_results,
                            'geotour'
                        ),
                        $total_results
                    );
                    ?>
                </p>
                
                <!-- Content Type Breakdown -->
                <?php if (!empty($post_type_counts)) : ?>
                <div class="results-breakdown">
                    <h3 class="breakdown-title"><?php _e('Results by Content Type:', 'geotour'); ?></h3>
                    <ul class="content-type-counts">
                        <?php foreach ($post_type_counts as $post_type => $count) : 
                            if ($count > 0 && $post_type !== 'listing_pois') :
                                
                                // Handle special display for listings
                                if ($post_type === 'listing') {
                                    $pois_count = isset($post_type_counts['listing_pois']) ? $post_type_counts['listing_pois'] : 0;
                                    $all_listings_url = add_query_arg('search', urlencode($search_query), home_url('/listing/'));
                                    $pois_listings_url = add_query_arg(array(
                                        'listing-category' => 'pois',
                                        'search' => urlencode($search_query)
                                    ), home_url('/listing/'));
                        ?>
                            <li class="content-type-item content-type-listing">
                                <div class="listing-breakdown">
                                    <div class="listing-total">
                                        <a href="<?php echo esc_url($all_listings_url); ?>" class="content-type-link">
                                            <span class="content-type-name"><?php _e('Listings', 'geotour'); ?></span>
                                            <span class="content-type-count"><?php echo absint($count); ?></span>
                                        </a>
                                    </div>
                                    <?php if ($pois_count > 0) : ?>
                                    <div class="listing-pois">
                                        <a href="<?php echo esc_url($pois_listings_url); ?>" class="content-type-link pois-link">
                                            <span class="content-type-name"><?php _e('POIs', 'geotour'); ?></span>
                                            <span class="content-type-count"><?php echo absint($pois_count); ?></span>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php
                                } else {
                                    // Handle other post types
                                    $post_type_object = get_post_type_object($post_type);
                                    if ($post_type_object) {
                                        $post_type_name = $post_type_object->labels->name;
                                    } else {
                                        // Handle custom post types that might not be registered properly
                                        switch ($post_type) {
                                            case 'people':
                                                $post_type_name = __('People', 'geotour');
                                                break;
                                            case 'photos':
                                                $post_type_name = __('Photos', 'geotour');
                                                break;
                                            case 'events':
                                                $post_type_name = __('Events', 'geotour');
                                                break;
                                            case 'timeline':
                                                $post_type_name = __('Timeline', 'geotour');
                                                break;
                                            case 'accommodation':
                                                $post_type_name = __('Accommodation', 'geotour');
                                                break;
                                            default:
                                                $post_type_name = ucwords(str_replace(array('-', '_'), ' ', $post_type));
                                        }
                                    }
                        ?>
                            <li class="content-type-item content-type-<?php echo esc_attr($post_type); ?>">
                                <span class="content-type-name"><?php echo esc_html($post_type_name); ?></span>
                                <span class="content-type-count"><?php echo absint($count); ?></span>
                            </li>
                        <?php 
                                }
                            endif;
                        endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
            <?php else : ?>
                <p class="no-results-message">
                    <?php _e('No results found for your search.', 'geotour'); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Search Form -->
    <div class="search-form-container">
        <form role="search" method="get" class="search-form refined-search" action="<?php echo esc_url(home_url('/')); ?>">
            <label for="search-field-header" class="screen-reader-text"><?php _e('Search for:', 'geotour'); ?></label>
            <input type="search" 
                   id="search-field-header" 
                   name="s" 
                   value="<?php echo esc_attr($search_query); ?>" 
                   placeholder="<?php esc_attr_e('Refine your search...', 'geotour'); ?>"
                   class="search-field">
            <button type="submit" class="search-submit">
                <span class="screen-reader-text"><?php _e('Search', 'geotour'); ?></span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
            </button>
        </form>
    </div>
</header>