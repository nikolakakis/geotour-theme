# Listings List Shortcode

This directory contains the listings list shortcode implementation with advanced filtering, pagination, and navigation capabilities.

## Quick Start

Add this shortcode to any page or post:

```
[listings_list]
```

## Files

- `listings-list.php` - Main shortcode logic, rendering, and asset enqueueing
- `usage-examples.php` - Comprehensive code examples (NOT loaded automatically)

## Related Files

- `/src/js/listings-list.js` - JavaScript functionality
- `/src/scss/listings-list.scss` - Styles

## Documentation

See `/LISTINGS_LIST_DOCUMENTATION.md` for complete documentation.

## Key Features

- ✅ Paginated listing display with smart page calculation
- ✅ URL-based pagination, filtering, and highlighting
- ✅ Auto-calculates which page a listing is on
- ✅ Responsive layout
- ✅ Content type filtering (simple listings have no links)
- ✅ Scroll-to functionality with smooth animations
- ✅ Multiple taxonomy filtering
- ✅ URL parameter support for deep linking
- ✅ Helper functions for creating "View in List" links

## Helper Functions

### `geotour_the_listing_in_list_link()`

Outputs a link to view a specific listing in the list:

```php
geotour_the_listing_in_list_link( 
    get_the_ID(), 
    home_url('/all-listings/'),
    array(), // Optional filters
    'View in List', // Link text
    'btn btn-primary' // CSS class
);
```

### `geotour_get_listing_in_list_url()`

Returns the URL (doesn't output):

```php
$url = geotour_get_listing_in_list_url( 
    get_the_ID(), 
    home_url('/all-listings/'),
    array(
        'category' => 'museums',
        'orderby' => 'title'
    )
);
```

## URL Parameters

All parameters are optional and can be combined:

- `highlight_post` - Post ID to scroll to (auto-calculates page)
- `listing_page` - Manual page number
- `listing_category` - Filter by category slug(s)
- `listing_region` - Filter by region slug(s)
- `listing_content_type` - Filter by content type
- `listing_orderby` - Sort field (date, title, etc.)
- `listing_order` - Sort direction (ASC/DESC)
- `listing_per_page` - Items per page

## Usage Examples

### Basic

```php
[listings_list]
```

### With Filters

```php
[listings_list content_type="simple" posts_per_page="20"]
```

### Multiple Filters

```php
[listings_list category="museums" region="heraklion" order="ASC"]
```

### Link to Specific Listing (from single-listing.php)

```php
<!-- Simple -->
<?php geotour_the_listing_in_list_link( get_the_ID(), home_url('/all-listings/') ); ?>

<!-- With Filters -->
<?php 
geotour_the_listing_in_list_link( 
    get_the_ID(), 
    home_url('/all-listings/'),
    array(
        'category' => 'museums',
        'orderby' => 'title'
    ),
    'View in Museums List'
);
?>
```

### Direct URL

```
/all-listings/?highlight_post=123&listing_category=museums&listing_orderby=title&listing_order=ASC
```

This will:
1. Filter to show only museums
2. Sort by title
3. Find which page listing #123 is on
4. Navigate to that page
5. Scroll to and highlight listing #123

## How Auto-Page Calculation Works

When you use `highlight_post` in the URL:

1. System builds query with all current filters
2. Retrieves all matching post IDs
3. Finds target post's position in results
4. Calculates: `page = floor(position / posts_per_page) + 1`
5. Displays that page
6. JavaScript scrolls to the listing

This means it works perfectly even when:
- Listings are added or removed
- Filters change
- Sorting changes
- Posts per page changes
