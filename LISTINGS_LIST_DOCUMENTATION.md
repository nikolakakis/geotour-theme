# Listings List Shortcode Documentation

## Overview

This document describes the new `listings-list` shortcode system that displays a paginated list of listing posts with advanced filtering and navigation capabilities.

## New Taxonomy: listing-content-type

A new taxonomy `listing-content-type` has been added to the listing custom post type. This allows you to categorize listings by their content completeness.

### Suggested Terms

- **simple**: Basic listings with minimal information (no "Read More" button or links)
- **moderate**: Listings with moderate information
- **complete**: Full listings with all information

### Adding Terms

1. Go to WordPress Admin → Listings → Content Types
2. Add your terms (simple, moderate, complete, etc.)
3. Assign the appropriate term to each listing

## Shortcode Usage

### Basic Usage

```php
[listings_list]
```

This will display all published listings with default settings (10 per page).

### Shortcode Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `posts_per_page` | integer | 10 | Number of listings to show per page |
| `category` | string | '' | Filter by listing-category slug(s), comma-separated |
| `region` | string | '' | Filter by listing-region slug(s), comma-separated |
| `content_type` | string | '' | Filter by listing-content-type slug(s), comma-separated |
| `orderby` | string | 'date' | Sort field (date, title, modified, etc.) |
| `order` | string | 'DESC' | Sort order (ASC or DESC) |

### Examples

#### Show 20 listings per page

```php
[listings_list posts_per_page="20"]
```

#### Show only simple listings

```php
[listings_list content_type="simple"]
```

#### Show listings from specific category and region

```php
[listings_list category="museums" region="heraklion"]
```

#### Show moderate and complete listings only

```php
[listings_list content_type="moderate,complete"]
```

#### Combined filters

```php
[listings_list posts_per_page="15" category="beaches" region="rethymno" orderby="title" order="ASC"]
```

## URL Parameters

The shortcode supports URL parameters for pagination, highlighting, and filtering. This allows for deep linking and dynamic list control.

### Pagination Parameter

- `listing_page`: The current page number
- Example: `https://yoursite.com/listings/?listing_page=2`

### Highlight Parameter

- `highlight_post`: The post ID to scroll to and highlight
- Example: `https://yoursite.com/listings/?highlight_post=123`

**Important**: When using `highlight_post`, the system automatically calculates which page the listing is on based on current filters and sorting. You don't need to manually specify the page number!

### Filter Parameters (Override Shortcode Attributes)

These URL parameters will override the shortcode's default attributes:

- `listing_category`: Filter by category slug(s), comma-separated
  - Example: `?listing_category=museums,beaches`

- `listing_region`: Filter by region slug(s), comma-separated
  - Example: `?listing_region=heraklion`

- `listing_content_type`: Filter by content type slug(s)
  - Example: `?listing_content_type=simple`

- `listing_orderby`: Sort field (date, title, modified, etc.)
  - Example: `?listing_orderby=title`

- `listing_order`: Sort direction (ASC or DESC)
  - Example: `?listing_order=ASC`

- `listing_per_page`: Items per page
  - Example: `?listing_per_page=20`

### Combined Example

Navigate to a specific listing with filters applied:

```
https://yoursite.com/listings/?highlight_post=123&listing_category=museums&listing_region=heraklion&listing_orderby=title&listing_order=ASC
```

The system will:
1. Apply the category and region filters
2. Sort by title ascending
3. Find which page listing #123 is on
4. Navigate to that page
5. Scroll to and highlight listing #123

This works even as you add or remove listings, because the page is calculated dynamically!

## Features

### 1. Listing Item Display

Each listing in the list shows:

- **Featured Image** (left side, clickable if not "simple")
- **Title** (clickable if not "simple")
- **Category** (from listing-category taxonomy)
- **Region** (from listing-region taxonomy)
- **Content Type** (from listing-content-type taxonomy)
- **Excerpt**
- **Read More Button** (only if NOT "simple" content type)

### 2. Simple Listings Behavior

Listings marked with `listing-content-type = simple`:

- Title is NOT clickable
- Featured image is NOT clickable
- NO "Read More" button
- Still displays all metadata and excerpt

### 3. Pagination

- Shows page numbers with previous/next links
- Smart ellipsis for long page lists (shows first, last, and pages around current)
- URL-based pagination for bookmarking and sharing
- Smooth scroll to list top after pagination

### 4. Scroll-to-Listing

When a `highlight_post` parameter is in the URL:

- Page automatically scrolls to that listing
- Listing is highlighted with a yellow background
- Brief flash animation draws attention
- Useful for "view in list" functionality

### 5. Responsive Design

- Desktop: Image on left, content on right
- Mobile/Tablet: Stacked layout (image on top)
- Touch-friendly buttons and links

## File Structure

```
includes/shortcodes/listings-list/
└── listings-list.php          # Main shortcode logic and rendering

src/js/
└── listings-list.js           # JavaScript for scroll-to and interactions

src/scss/
└── listings-list.scss         # Styles for the list and items
```

## Building Assets

After making changes to JavaScript or SCSS files, rebuild with:

```bash
npm run build
```

For development with watch mode:

```bash
npm run dev
```

## Customization

### Styling

Edit `src/scss/listings-list.scss` to customize:

- Colors (variables at top of file)
- Layout and spacing
- Responsive breakpoints
- Animations and transitions

### Layout

Edit the `geotour_render_listing_item()` function in `listings-list.php` to:

- Change the order of elements
- Add/remove metadata fields
- Modify the HTML structure

### Functionality

Edit `src/js/listings-list.js` to:

- Customize scroll behavior
- Add AJAX filtering
- Implement lazy loading
- Add animations

## WordPress Integration

### Creating a Listings Page

1. Create a new page in WordPress (e.g., "All Listings")
2. Add the shortcode in the content editor:
   ```
   [listings_list posts_per_page="20"]
   ```
3. Publish the page
4. Note the page URL (e.g., `/all-listings/`)

### Linking to a Specific Listing from Single Listing Pages

Use the built-in helper functions to create "View in List" links:

#### Method 1: Using the Helper Function (Recommended)

```php
<?php
// In your single-listing.php template or anywhere
$listing_id = get_the_ID();
$list_page_url = home_url('/all-listings/'); // Your listings page URL

// Basic link
geotour_the_listing_in_list_link( $listing_id, $list_page_url );

// With custom text and class
geotour_the_listing_in_list_link( 
    $listing_id, 
    $list_page_url, 
    array(), // No filters
    'Back to Listings', // Custom text
    'btn btn-secondary' // CSS classes
);

// With filters applied
geotour_the_listing_in_list_link( 
    $listing_id, 
    $list_page_url, 
    array(
        'category' => 'museums',
        'region' => 'heraklion',
        'orderby' => 'title',
        'order' => 'ASC'
    ),
    'View in Museums List'
);
?>
```

#### Method 2: Building the URL Manually

```php
<?php
$listing_id = get_the_ID();
$list_page_url = home_url('/all-listings/');

// Get the URL
$url = geotour_get_listing_in_list_url( $listing_id, $list_page_url );

// Or with filters
$url = geotour_get_listing_in_list_url( 
    $listing_id, 
    $list_page_url, 
    array(
        'category' => 'beaches',
        'orderby' => 'title',
        'order' => 'ASC'
    )
);
?>

<a href="<?php echo esc_url($url); ?>" class="view-in-list-btn">
    View in Full Listings
</a>
```

#### Method 3: Manual URL Construction (Not Recommended)

```php
<?php
$listing_id = 123;
$listings_page_url = home_url('/all-listings/');

// Build the URL with just the highlight parameter
// The system will auto-calculate the page
$link = add_query_arg(
    array(
        'highlight_post' => $listing_id
    ),
    $listings_page_url
);

// With filters
$link = add_query_arg(
    array(
        'highlight_post' => $listing_id,
        'listing_category' => 'museums',
        'listing_region' => 'heraklion',
        'listing_orderby' => 'title',
        'listing_order' => 'ASC'
    ),
    $listings_page_url
);
?>

<a href="<?php echo esc_url($link); ?>">View in List</a>
```

### Creating Filter Links

You can create links that filter the listings without highlighting a specific post:

```php
<?php
$list_page_url = home_url('/all-listings/');

// Filter by category
$museums_url = add_query_arg('listing_category', 'museums', $list_page_url);

// Multiple filters
$filtered_url = add_query_arg(
    array(
        'listing_category' => 'beaches',
        'listing_region' => 'chania',
        'listing_order' => 'ASC'
    ),
    $list_page_url
);
?>

<a href="<?php echo esc_url($museums_url); ?>">View Museums</a>
<a href="<?php echo esc_url($filtered_url); ?>">Chania Beaches</a>
```

### How It Works

When a user clicks a link with `highlight_post`:

1. The shortcode receives the post ID
2. It builds a query with all current filters from URL parameters
3. It executes the query to get all matching post IDs
4. It finds the position of the target post in the results
5. It calculates which page that post is on
6. It displays that page with the post highlighted
7. JavaScript scrolls to the highlighted post

This means:
- ✅ Works with any combination of filters
- ✅ Works with any sorting method
- ✅ Automatically adjusts if posts are added/removed
- ✅ Respects posts per page settings
- ✅ Deep linkable and shareable URLs

## Future Enhancements

Possible additions for future development:

1. **AJAX Filtering**: Filter listings without page reload
2. **Search Bar**: Add keyword search functionality
3. **Sort Options**: User-selectable sort dropdown
4. **View Toggle**: Switch between list and grid views
5. **Load More**: Infinite scroll or load more button
6. **Favorites**: Save favorite listings
7. **Map View**: Toggle between list and map display

## Troubleshooting

### Shortcode not appearing

- Ensure the file is included in `functions.php`
- Check for PHP errors in debug log
- Verify the shortcode is spelled correctly: `[listings_list]`

### Styles not loading

- Run `npm run build` to compile SCSS
- Clear WordPress and browser cache
- Check that CSS file exists in `/build/assets/`

### JavaScript not working

- Check browser console for errors
- Verify JS file exists in `/build/assets/`
- Ensure jQuery is loaded if needed

### Pagination not working

- Check permalink settings (Settings → Permalinks)
- Ensure `paged` query var is allowed
- Verify URL structure

## Support

For issues or questions, refer to:

- WordPress Codex: https://codex.wordpress.org/
- WP_Query documentation: https://developer.wordpress.org/reference/classes/wp_query/
- Theme documentation in `/map-documentation/`
