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

The shortcode supports URL parameters for pagination and highlighting:

### Pagination Parameter

- `listing_page`: The current page number
- Example: `https://yoursite.com/listings/?listing_page=2`

### Highlight Parameter

- `highlight_post`: The post ID to scroll to and highlight
- Example: `https://yoursite.com/listings/?listing_page=3&highlight_post=123`

This is useful for linking to a specific listing within the list. The page will automatically scroll to the listing and highlight it.

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

### Linking to a Specific Listing

To link to a specific listing within the list:

```php
<?php
$listing_id = 123;
$listings_page_url = home_url('/all-listings/');
$page_number = 1; // Calculate which page the listing is on

// Build the URL
$link = add_query_arg(
    array(
        'listing_page' => $page_number,
        'highlight_post' => $listing_id
    ),
    $listings_page_url
);
?>

<a href="<?php echo esc_url($link); ?>">View in List</a>
```

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
