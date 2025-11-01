<?php
/**
 * Listings List - Usage Examples
 * 
 * This file demonstrates various ways to use the listings list shortcode
 * and its helper functions.
 * 
 * @package Geotour_Mobile_First
 */

// ============================================================================
// EXAMPLE 1: Basic "View in List" button in single-listing.php
// ============================================================================
?>

<!-- Add this to your single-listing.php template -->
<div class="listing-navigation">
    <?php
    // Simple usage - just needs the listing ID and the list page URL
    geotour_the_listing_in_list_link( 
        get_the_ID(), 
        home_url('/all-listings/')
    );
    ?>
</div>

<?php
// ============================================================================
// EXAMPLE 2: Styled "View in List" button with custom class
// ============================================================================
?>

<div class="listing-actions">
    <?php
    geotour_the_listing_in_list_link( 
        get_the_ID(), 
        home_url('/all-listings/'),
        array(), // No filters
        'Back to All Listings', // Custom text
        'btn btn-primary' // CSS class
    );
    ?>
</div>

<?php
// ============================================================================
// EXAMPLE 3: Category-specific "View in List" link
// ============================================================================

// Get the first category of the current listing
$categories = get_the_terms( get_the_ID(), 'listing-category' );
if ( $categories && ! is_wp_error( $categories ) ) {
    $category_slug = $categories[0]->slug;
    $category_name = $categories[0]->name;
    ?>
    <div class="listing-category-nav">
        <?php
        // Link back to the filtered list showing only this category
        geotour_the_listing_in_list_link( 
            get_the_ID(), 
            home_url('/all-listings/'),
            array(
                'category' => $category_slug,
                'orderby' => 'title',
                'order' => 'ASC'
            ),
            sprintf( 'View all %s', $category_name ),
            'btn-view-category'
        );
        ?>
    </div>
    <?php
}

// ============================================================================
// EXAMPLE 4: Region-specific navigation
// ============================================================================

$regions = get_the_terms( get_the_ID(), 'listing-region' );
if ( $regions && ! is_wp_error( $regions ) ) {
    ?>
    <div class="listing-region-nav">
        <h3>Explore More in This Region</h3>
        <?php
        foreach ( $regions as $region ) {
            geotour_the_listing_in_list_link( 
                get_the_ID(), 
                home_url('/all-listings/'),
                array(
                    'region' => $region->slug,
                    'orderby' => 'date',
                    'order' => 'DESC'
                ),
                sprintf( 'Latest in %s', $region->name ),
                'btn-region'
            );
            echo ' ';
        }
        ?>
    </div>
    <?php
}

// ============================================================================
// EXAMPLE 5: Building custom navigation with multiple filter options
// ============================================================================
?>

<div class="listing-quick-filters">
    <h4>Quick Filters</h4>
    
    <!-- View this listing in the full list -->
    <?php
    geotour_the_listing_in_list_link( 
        get_the_ID(), 
        home_url('/all-listings/'),
        array(),
        'View in Full List',
        'filter-btn'
    );
    ?>
    
    <!-- View in alphabetical list -->
    <?php
    geotour_the_listing_in_list_link( 
        get_the_ID(), 
        home_url('/all-listings/'),
        array(
            'orderby' => 'title',
            'order' => 'ASC'
        ),
        'View Alphabetically',
        'filter-btn'
    );
    ?>
    
    <!-- View in newest first list -->
    <?php
    geotour_the_listing_in_list_link( 
        get_the_ID(), 
        home_url('/all-listings/'),
        array(
            'orderby' => 'date',
            'order' => 'DESC'
        ),
        'View Newest First',
        'filter-btn'
    );
    ?>
</div>

<?php
// ============================================================================
// EXAMPLE 6: Creating a custom URL for use in JavaScript or meta tags
// ============================================================================

$listing_url = geotour_get_listing_in_list_url( 
    get_the_ID(), 
    home_url('/all-listings/'),
    array(
        'category' => 'museums',
        'orderby' => 'title'
    )
);
?>

<!-- Use in a data attribute -->
<div class="listing-card" data-list-url="<?php echo esc_attr( $listing_url ); ?>">
    <!-- Your content -->
</div>

<!-- Use in Open Graph meta tag -->
<meta property="og:see_also" content="<?php echo esc_url( $listing_url ); ?>">

<!-- Use in JavaScript -->
<script>
const listingUrl = <?php echo json_encode( $listing_url ); ?>;
console.log('View this listing in list:', listingUrl);
</script>

<?php
// ============================================================================
// EXAMPLE 7: Dynamic listing page URL based on listing type
// ============================================================================

function get_listing_page_url_by_type( $listing_id ) {
    $categories = get_the_terms( $listing_id, 'listing-category' );
    
    if ( ! $categories || is_wp_error( $categories ) ) {
        return home_url('/all-listings/');
    }
    
    $category_slug = $categories[0]->slug;
    
    // Different list pages for different categories
    $page_map = array(
        'museums' => '/museums-list/',
        'beaches' => '/beaches-list/',
        'restaurants' => '/dining-list/',
    );
    
    if ( isset( $page_map[ $category_slug ] ) ) {
        return home_url( $page_map[ $category_slug ] );
    }
    
    return home_url('/all-listings/');
}

// Usage
geotour_the_listing_in_list_link( 
    get_the_ID(), 
    get_listing_page_url_by_type( get_the_ID() ),
    array(),
    'View in Category List'
);

// ============================================================================
// EXAMPLE 8: Creating filter links (without specific post highlighting)
// ============================================================================
?>

<div class="listings-filters-menu">
    <h3>Browse Listings</h3>
    
    <!-- All listings -->
    <a href="<?php echo esc_url( home_url('/all-listings/') ); ?>">
        All Listings
    </a>
    
    <!-- Filtered by category -->
    <a href="<?php echo esc_url( add_query_arg('listing_category', 'museums', home_url('/all-listings/')) ); ?>">
        Museums
    </a>
    
    <a href="<?php echo esc_url( add_query_arg('listing_category', 'beaches', home_url('/all-listings/')) ); ?>">
        Beaches
    </a>
    
    <!-- Multiple filters -->
    <a href="<?php echo esc_url( add_query_arg(
        array(
            'listing_category' => 'restaurants',
            'listing_region' => 'heraklion',
            'listing_orderby' => 'title',
            'listing_order' => 'ASC'
        ),
        home_url('/all-listings/')
    ) ); ?>">
        Heraklion Restaurants (A-Z)
    </a>
</div>

<?php
// ============================================================================
// EXAMPLE 9: Creating a "Similar Listings" navigation
// ============================================================================

$current_id = get_the_ID();
$categories = get_the_terms( $current_id, 'listing-category' );
$regions = get_the_terms( $current_id, 'listing-region' );

if ( $categories && $regions ) {
    $category_slug = $categories[0]->slug;
    $region_slug = $regions[0]->slug;
    ?>
    <div class="similar-listings-nav">
        <h3>Find Similar Listings</h3>
        
        <!-- Same category and region -->
        <a href="<?php echo esc_url( add_query_arg(
            array(
                'listing_category' => $category_slug,
                'listing_region' => $region_slug
            ),
            home_url('/all-listings/')
        ) ); ?>">
            More <?php echo esc_html( $categories[0]->name ); ?> in <?php echo esc_html( $regions[0]->name ); ?>
        </a>
        
        <!-- Navigate to this listing within that filtered list -->
        <?php
        geotour_the_listing_in_list_link( 
            $current_id, 
            home_url('/all-listings/'),
            array(
                'category' => $category_slug,
                'region' => $region_slug,
                'orderby' => 'date',
                'order' => 'DESC'
            ),
            'Find This in Similar Listings',
            'btn-similar'
        );
        ?>
    </div>
    <?php
}

// ============================================================================
// EXAMPLE 10: AJAX-based listing navigation
// ============================================================================
?>

<button 
    class="view-in-list-ajax" 
    data-listing-id="<?php echo esc_attr( get_the_ID() ); ?>"
    data-list-page="<?php echo esc_attr( home_url('/all-listings/') ); ?>"
    data-filters='<?php echo esc_attr( json_encode( array(
        'category' => 'museums',
        'orderby' => 'title'
    ) ) ); ?>'>
    View in List (AJAX)
</button>

<script>
document.querySelector('.view-in-list-ajax').addEventListener('click', function() {
    const listingId = this.dataset.listingId;
    const listPage = this.dataset.listPage;
    const filters = JSON.parse(this.dataset.filters);
    
    // Build URL parameters
    const params = new URLSearchParams({
        highlight_post: listingId,
        ...filters
    });
    
    // Navigate or load via AJAX
    window.location.href = `${listPage}?${params.toString()}`;
});
</script>

<?php
// ============================================================================
// EXAMPLE 11: Breadcrumb-style navigation showing current position in list
// ============================================================================
?>

<nav class="listing-breadcrumb">
    <a href="<?php echo esc_url( home_url() ); ?>">Home</a>
    <span class="separator">/</span>
    <a href="<?php echo esc_url( home_url('/all-listings/') ); ?>">All Listings</a>
    <span class="separator">/</span>
    <span class="current"><?php the_title(); ?></span>
    <span class="separator">|</span>
    <?php
    geotour_the_listing_in_list_link( 
        get_the_ID(), 
        home_url('/all-listings/'),
        array(),
        'Find in List',
        'breadcrumb-action'
    );
    ?>
</nav>
