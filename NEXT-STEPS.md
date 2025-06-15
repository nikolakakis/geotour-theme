Status of details-sections.php:

* Type: listing-category (It must be removed from the 2. .listing-map-full-section that is above)
* Region: listing-region (It must be removed from the 2. .listing-map-full-section that is above) This needs to be a level type of breadcrumb like links, since one listing-region is a higher level region eg Heraklion than the other eg Gortyna which is a municipality 
* Site access information (how to reach the location) (if applicable) consinst of many ACF fields
* Contact details (if applicable) again consinst of many ACF fields
* Category-specific content based on listing type


Weather widget integration


The grid should be:
- 100vw full viewport width
- Responsive layout (likely 2-3 columns on desktop, stacked on mobile)
- Contain multiple content blocks in a card-based design
- Integrate with ACF fields for dynamic content

Let me check what's currently in details-sections.php and then propose the proper grid implementation.

Would you like me to:
1. First examine the current details-sections.php content
2. Implement a proper responsive grid layout
3. Add ACF field integration for dynamic content
4. Style it to match your theme's design system

Which aspect should we focus on first?