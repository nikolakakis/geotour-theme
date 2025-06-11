<?php
/**
 * Listing Hero Section Template Part
 * 
 * @package Geotour_Mobile_First
 */

// Get current post data
$listing_title = get_the_title();
$listing_excerpt = get_the_excerpt();
$listing_permalink = get_permalink();

// Get featured image or fallback
$hero_image = '';
if (has_post_thumbnail()) {
    $hero_image = get_the_post_thumbnail_url(get_the_ID(), 'listing-hero');
} else {
    // Fallback to theme default hero image
    $hero_image = get_theme_mod('custom_header_image', '');
}

// Get listing categories for subtitle
$categories = get_the_terms(get_the_ID(), 'listing-category');
$category_names = array();
if ($categories && !is_wp_error($categories)) {
    foreach ($categories as $category) {
        $category_names[] = $category->name;
    }
}
$subtitle = !empty($category_names) ? implode(', ', $category_names) : $listing_excerpt;

// Get listing regions for additional context
$regions = get_the_terms(get_the_ID(), 'listing-region');
$region_name = '';
if ($regions && !is_wp_error($regions)) {
    $region_name = $regions[0]->name; // Use first region
}
?>

<section class="hero-section listing-hero-section" 
         <?php if ($hero_image) : ?>style="background-image: url('<?php echo esc_url($hero_image); ?>');"<?php endif; ?>>
    
    <div class="hero-overlay"></div>
    
    <div class="hero-content">
        <div class="hero-container">
            
            <!-- Listing breadcrumb/category -->
            <?php if (!empty($category_names)) : ?>
                <div class="listing-hero-category">
                    <?php foreach ($category_names as $index => $category) : ?>
                        <?php if ($index > 0) echo ' • '; ?>
                        <span class="category-item"><?php echo esc_html($category); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Main listing title (H1 for SEO) -->
            <h1 class="hero-title listing-hero-title">
                <?php echo esc_html($listing_title); ?>
            </h1>
            
            <!-- Region information -->
            <?php if ($region_name) : ?>
                <div class="listing-hero-region">
                    <i class="location-icon"></i>
                    <span><?php echo esc_html($region_name); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Listing excerpt/subtitle (full text, no trimming) -->
            <?php if ($listing_excerpt && strlen($listing_excerpt) > 10) : ?>
                <div class="hero-subtitle listing-hero-subtitle">
                    <p><?php echo esc_html($listing_excerpt); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Scroll indicator -->
            <div class="hero-scroll-indicator">
                <div class="scroll-arrow"></div>
            </div>
            
        </div>
    </div>
    
</section>