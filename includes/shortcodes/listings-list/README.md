# Listings List Shortcode

This directory contains the listings list shortcode implementation.

## Quick Start

Add this shortcode to any page or post:

```
[listings_list]
```

## Files

- `listings-list.php` - Main shortcode logic, rendering, and asset enqueueing

## Related Files

- `/src/js/listings-list.js` - JavaScript functionality
- `/src/scss/listings-list.scss` - Styles

## Documentation

See `/LISTINGS_LIST_DOCUMENTATION.md` for complete documentation.

## Key Features

- Paginated listing display
- URL-based pagination and highlighting
- Responsive layout
- Content type filtering (simple listings have no links)
- Scroll-to functionality
- Multiple taxonomy filtering

## Usage Examples

```php
// Basic
[listings_list]

// With filters
[listings_list content_type="simple" posts_per_page="20"]

// Multiple filters
[listings_list category="museums" region="heraklion" order="ASC"]
```
