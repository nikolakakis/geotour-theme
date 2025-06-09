# Hero Section Implementation

## Overview
The hero section is a full-width, responsive component that displays prominently below the header. It features a background image with overlay, title, subtitle, and call-to-action button.

## Features
- **Responsive Design**: Adapts to different screen sizes
- **Dynamic Content**: Shows appropriate content based on page type
- **Background Images**: Uses featured images or fallback
- **Animations**: Fade-in animations with staggered timing
- **Typography**: Uses Syne font family for headings

## Page Type Behavior

### Homepage/Front Page
- Title: Site name (`get_bloginfo('name')`)
- Subtitle: Site description (`get_bloginfo('description')`)
- CTA Button: "Explore Tours" linking to main content
- Background: Custom header image or featured image

### Single Posts/Pages
- Title: Post/page title (`get_the_title()`)
- Subtitle: Post excerpt (`get_the_excerpt()`)
- Background: Featured image if available

### Archive Pages
- Title: Archive title (Category, Tag, Author name)
- Subtitle: Archive description
- Background: Default fallback image

## Styling Features
- **Parallax Effect**: Fixed background attachment on desktop
- **Overlay Gradient**: Dark overlay for text readability
- **Scroll Indicator**: Animated arrow pointing down
- **Hover Effects**: Interactive CTA button with transform and shadow

## Typography
All typography follows the Syne font family system:
- Hero title: Clamp sizing from 2.5rem to 6rem
- Hero subtitle: Clamp sizing from 1.125rem to 1.75rem
- Text shadows for better readability over images

## Files
- `template-parts/hero.php` - Hero template part
- `src/scss/components/_hero.scss` - Hero styles
- `src/scss/base/_typography.scss` - Typography definitions

## Usage
The hero section is automatically included on:
- Homepage/front page
- Single posts and pages
- Archive pages
- Category/tag pages

To exclude from specific pages, modify the conditional in `header.php`.
