# Geotour Mobile First Theme

A modern, mobile-first WordPress theme designed specifically for geographic and tourism content management. Built for the Geotour Crete project, this theme provides comprehensive listing management, interactive mapping, and spatial data visualization capabilities.

## Overview

The Geotour Mobile First Theme is a complete redevelopment of the original Geotour theme, focusing on modern web standards, performance optimization, and enhanced user experience. The theme is specifically designed for managing geographic listings with advanced mapping features, spatial search capabilities, and mobile-responsive design.

### Key Features

- **Mobile-First Responsive Design**: Optimized for all device sizes with touch-friendly interfaces
- **Advanced Mapping Integration**: Leaflet-based maps with custom markers and spatial search
- **Geographic Listing Management**: Custom post type for locations with comprehensive metadata
- **REST API Integration**: Custom endpoints for spatial data and listing management
- **Modern Build System**: Vite-powered asset compilation with SCSS and modern JavaScript
- **ACF Integration**: Advanced Custom Fields for flexible content management
- **Performance Optimized**: Efficient caching, lazy loading, and optimized asset delivery

### Technology Stack

- **Frontend**: SCSS (Sass), Modern JavaScript (ES6+), Leaflet.js
- **Backend**: WordPress, PHP 8.0+, Advanced Custom Fields Pro
- **Build Tools**: Vite, PostCSS, npm
- **Mapping**: Leaflet with OpenStreetMap tiles
- **Fonts**: Syne font family for modern typography

---

## Version History

### Version 0.9.0 - Initial Development Release
*Release Date: June 2025*

**Major Features Implemented:**
- Complete theme architecture and file structure
- Mobile-first responsive design system
- Custom listing post type with taxonomies (categories, regions, tags)
- ACF integration for listing metadata and coordinates
- Single listing page with hero section, map, and details sections
- Full-screen listing archive page with interactive map
- Custom REST API endpoints for spatial data
- Modern header with social media integration and navigation
- Contact Form 7 styling integration
- Asset build system with Vite

**Template Structure:**
- `single-listing.php` - Main listing template
- `page-listing.php` - Full-screen map archive
- Template parts for modular listing components
- Hero sections with SVG clip-path design
- Over-the-content layout sections

**API Endpoints Implemented:**
- `/wp-json/panotours/v1/listings` - Listing search and filtering
- `/wp-json/panotours/v1/calendar` - Festive dates calendar
- `/wp-json/panotours/v1/nearest` - Nearest listings with distance calculation
- `/wp-json/geotour/v1/selector` - GeoJSON listings for map display

**SCSS Architecture:**
- Component-based styling system
- Variables and typography management
- Layout-specific styles for navigation, header, content
- Page-specific styles for single listings and homepage
- Contact form and gallery component styles

**Known Issues:**
- Migration from old metabox system to ACF in progress
- Some legacy shortcodes still being replaced
- Performance optimization ongoing

**Next Steps for v1.0:**
- Complete ACF migration from old metabox system
- Implement category-specific listing characteristics
- Add advanced filtering and search capabilities
- Performance optimization and caching improvements
- Complete documentation and code cleanup

---

## Development Notes

### Architecture Decisions
- Chose mobile-first approach for better performance on mobile devices
- Implemented modular template system for easier maintenance
- Used ACF for flexible content management instead of custom metaboxes
- Built custom REST API endpoints for better frontend integration
- Adopted modern build tools for efficient asset management

### Performance Considerations
- Implemented efficient image loading and optimization
- Used viewport-relative units for better responsive behavior
- Minimized HTTP requests through asset bundling
- Implemented proper caching strategies for API endpoints

### Future Roadmap
- Enhanced search and filtering capabilities
- Multi-language support improvements
- Advanced map clustering for large datasets
- Progressive Web App (PWA) features
- Enhanced accessibility compliance