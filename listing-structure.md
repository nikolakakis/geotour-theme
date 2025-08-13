# Listing Single Page Structure

## Template Files Used

**ACTIVE TEMPLATE**: `/single-listing.php` (root level)

## Detailed Desktop Layout Structure

### 1. Hero Section (`hero-listing.php`)
**Container**: `.hero-section.listing-hero-section`
- **Background**: Featured image with overlay (`.hero-background-image`, `.hero-overlay`)
- **Content Container**: `.hero-content > .hero-container`
  - **Category Breadcrumb**: `.listing-hero-category` - Links to category archive pages
  - **Main Title**: `.hero-title.listing-hero-title` (H1 for SEO)
  - **Region Info**: `.listing-hero-region` - Location link with icon
  - **Scroll Indicator**: `.hero-scroll-indicator > .scroll-arrow`

### 2. Map Section (`map-single.php`) - 100vw
**Container**: `.listing-map-full-section`
- **Wrapper**: `.listing-map-container-wrapper`
  - **Left Content Area**: `.listing-map-content-area` (hidden on mobile)
    - **Summary**: `.listing-excerpt-section`
      - Title: "Summary About This Location"
      - Excerpt content or fallback text
      - **Scroll to Content**: `.scroll-to-content` button linking to `#listing-content`
  - **Right Map Area**: `.listing-map-area`
    - **Interactive Map**: `#listing-map.geotour-map-container.listing-single-map`
    - **Map Controls**: `.listing-map-controls` 
      - Google Maps link
      - Route Planner link
      - 3D Map link
      - Virtual Tour link (conditional)
      - 3D Video Path link (conditional)
      - 3D Tour link (conditional)

### 3. Details Sections (`details-sections.php`) - 100vw
**Container**: `.listing-details-full-section`
- **Details Container**: `.listing-details-container`
  - **Combined Column**: `.details-column.details-combined`
    - **Taxonomy Section**: `.listing-taxonomy-section`
      - **Region Hierarchy**: `.taxonomy-region` - Breadcrumb-style region links
      - **Categories**: `.taxonomy-categories` - Category links
    - **Category Characteristics**: `.category-characteristics-section`
      - **Details Group**: Archaeological, Beach, Fortification, Religious info
    - **Site Access Section**: `.site-access-section` (conditional)
      - Access methods (Paved road, Earth road, 4WD, Hiking)
      - Access notes
    - **Contact Details**: `.contact-details-section` (conditional)
      - Contact name, address, phone numbers
      - WhatsApp link
      - Social media icons
  - **Weather Column**: `.details-column.details-weather` (desktop only)
    - **Weather Forecast**: `#openmeteo` container

### 4. Custom Map Section (`map-custom.php`) - 100vw (conditional)
**Container**: `.listing-custom-map-section`
- **Custom Map Container**: `.custom-map-container`
  - Displays processed shortcode from ACF `listing_map` field
  - Only shows if field contains valid `geotour_map` shortcode

### 5. Main Content Container (`content-listing-single.php`)
**Container**: `.content-wrapper > .content-with-sidebar` or `.content-no-sidebar`
- **Main Content**: `.main-content`
  - **Article**: `#post-{ID}.content-article.listing-single.listing-wide-content`
    - **Entry Content**: `#listing-content.entry-content` - Main WordPress content
    - **Virtual Tour Section**: `.virtual-tour-full-section` (conditional, 100vw)
      - Desktop: `#geotour-overlay.desktop-vtour` with iframe
      - Mobile: `#geotour-mobile-tour.mobile-vtour` with link
    - **Nearest Listings**: `.nearest-listings-full-section` (100vw)
      - Shortcode: `[listings-grid type="nearest" limit="12"]`
    - **Entry Footer**: Tags and edit link
    - **Cretan Timeline**: `#cretan-timeline-display`

- **Sidebar**: `.sidebar-content > .listing-sidebar` (conditional)
  - **Festive Dates**: `#festivedates`
  - **Related News Posts**: Related posts or latest 5 posts fallback
  - **Related People**: `[related-people]` shortcode
  - **Related Photos**: `[related-photos]` shortcode  
  - **Search Culture Title**: `#searchculturetitle`
  - **Search Culture**: `#searchculture`
  - **Ferry Hopper Widget**: `[geotour_ferryhopper_widget]` shortcode

### 6. Comments Section (conditional)
WordPress comments template (disabled in current setup)

## Layout Behavior
- **Full-width sections**: Hero, Maps, Details, Virtual Tour, Nearest Listings use 100vw
- **Contained sections**: Main content uses theme container with optional sidebar
- **Responsive**: Sidebar hidden on mobile, weather column hidden on mobile
- **Conditional rendering**: Many sections check for ACF field values before displaying
- **SEO optimized**: Proper heading hierarchy, structured data ready