<?php
/**
 * Template part for displaying hero section
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Geotour_Mobile_First
 */

// Add body class for hero presence
add_filter('body_class', function($classes) {
    $classes[] = 'has-hero';
    return $classes;
});

// Get the featured image or fallback
$hero_image = '';
$hero_title = '';
$hero_subtitle = '';

if (is_home() || is_front_page()) {
    // For homepage, use site title and description
    $hero_title = get_bloginfo('name');
    $hero_subtitle = get_bloginfo('description');
    
    // Try to get custom header image or featured image
    if (has_custom_header() && get_header_image()) {
        $hero_image = get_header_image();
    } elseif (has_post_thumbnail()) {
        $hero_image = get_the_post_thumbnail_url(null, 'full');
    }
} elseif (is_singular()) {
    // For single posts/pages, use post title and featured image
    $hero_title = get_the_title();
    $hero_subtitle = get_the_excerpt();
    
    if (has_post_thumbnail()) {
        $hero_image = get_the_post_thumbnail_url(null, 'full');
    }
} elseif (is_archive()) {
    // For archive pages
    if (is_category()) {
        $hero_title = single_cat_title('', false);
        $hero_subtitle = category_description();
    } elseif (is_tag()) {
        $hero_title = single_tag_title('', false);
        $hero_subtitle = tag_description();
    } elseif (is_author()) {
        $hero_title = get_the_author();
        $hero_subtitle = get_the_author_meta('description');
    } elseif (is_post_type_archive()) {
        // For custom post type archives, get the post type object and use its label
        $post_type = get_post_type_object(get_post_type());
        if ($post_type) {
            $hero_title = $post_type->label; // Use the clean label without any prefixes or HTML
        } else {
            // Fallback to the archive title with prefixes and HTML stripped
            $hero_title = wp_strip_all_tags(get_the_archive_title());
        }
        $hero_subtitle = get_the_archive_description();
    } else {
        // For other archive types, strip all HTML tags
        $hero_title = wp_strip_all_tags(get_the_archive_title());
        $hero_subtitle = get_the_archive_description();
    }
}

// Fallback image if none found
if (empty($hero_image)) {
    $hero_image = get_template_directory_uri() . '/assets/graphics/default-hero.jpg';
}

// Clean up title (remove "Category:", "Tag:", etc.)
$hero_title = str_replace(['Category: ', 'Tag: ', 'Author: ', 'Archives: '], '', $hero_title);

// Remove any remaining HTML tags for safety
$hero_title = wp_strip_all_tags($hero_title);
?>

<section class="hero-section">
    
    <?php if ($hero_image) : ?>
        <img src="<?php echo esc_url($hero_image); ?>" 
             alt="<?php echo esc_attr($hero_title); ?>"
             fetchpriority="high"
             loading="eager"
             class="hero-background-image">
    <?php endif; ?>
    
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-container">
            <?php if (!empty($hero_title)) : ?>
                <h1 class="hero-title"><?php echo esc_html($hero_title); ?></h1>
            <?php endif; ?>
            
            <?php if (!empty($hero_subtitle)) : ?>
                <div class="hero-subtitle">
                    <?php echo wp_kses_post($hero_subtitle); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="hero-scroll-indicator">
        <div class="scroll-arrow"></div>
    </div>
</section>
