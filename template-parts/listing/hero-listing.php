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

<section class="hero-section listing-hero-section">
    
    <?php if ($hero_image) : ?>
        <img src="<?php echo esc_url($hero_image); ?>" 
             alt="<?php echo esc_attr($listing_title); ?>"
             fetchpriority="high"
             loading="eager"
             class="hero-background-image">
    <?php endif; ?>
    
    <div class="hero-overlay"></div>
    
    <div class="hero-content">
        <div class="hero-container">
            
            <!-- Listing breadcrumb/category -->
            <?php if (!empty($category_names)) : ?>
                <div class="listing-hero-category">
                    <?php foreach ($categories as $index => $category) : ?>
                        <?php if ($index > 0) echo ' • '; ?>
                        <a href="<?php echo esc_url(geotour_get_taxonomy_listing_url('listing-category', $category->slug)); ?>" class="category-item">
                            <?php echo esc_html($category->name); ?>
                        </a>
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
                    <a href="<?php echo esc_url(geotour_get_taxonomy_listing_url('listing-region', $regions[0]->slug)); ?>" class="region-link">
                        <?php echo esc_html($region_name); ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Scroll indicator -->
            <div class="hero-scroll-indicator">
                <div class="scroll-arrow" style="cursor: pointer;"></div>
            </div>
            
        </div>
    </div>
    
</section>
<?php
// 1. Check if the visitor is on a mobile device using PHP
if ( wp_is_mobile() ) :
?>
    
    <style>
        .listing-hero-ad-section {
            padding: 20px 0;
            background: #f8f9fa; /* Light grey background */
            text-align: center;
        }
    </style>

    

<?php
endif; // End the mobile check
?>

<?php get_template_part('template-parts/listing/inpage-menu'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var scrollArrow = document.querySelector('.hero-scroll-indicator .scroll-arrow');
    var targetMenu = document.getElementById('listing-inpage-menu');
    if (scrollArrow && targetMenu) {
        scrollArrow.addEventListener('click', function(e) {
            e.preventDefault();
            targetMenu.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
});
</script>