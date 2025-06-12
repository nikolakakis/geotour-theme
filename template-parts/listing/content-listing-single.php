<?php
/**
 * Template part for displaying single listing content
 *
 * @package Geotour_Mobile_First
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('content-article listing-single listing-wide-content'); ?>>
    
    <div class="entry-content">        
        <?php the_content(); ?>
    </div>
        
    <!-- Virtual Tour Section (100vw) -->
    <?php
    $vtour_link = get_field('vtour_link');
    if ($vtour_link) : ?>
        <section class="virtual-tour-full-section">
            <!-- Desktop Virtual Tour -->
            <div id="geotour-overlay" class="desktop-vtour">
                <div id="geotour-tour">
                    <img class="vrgirl" src="https://www.geotour.gr/wp-content/uploads/2024/06/VRgirl.webp" alt="VR Girl" />
                    <h3 class="geotour-title"><span>G</span>eotour Virtual Tour</h3>
                    <p>Drag the mouse to change the field of view of the virtual tour, or use a VR headset to roam to Crete in an alternative way!</p>
                    <div class="geotour-vtour">
                        <span>You can open the tour in fullscreen by double clicking (or double tap with fingers) on it! Double click again to exit the fullscreen mode.</span>
                        <iframe src="<?php echo esc_url($vtour_link); ?>" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Virtual Tour -->
            <div id="geotour-mobile-tour" class="mobile-vtour">
                <a href="<?php echo esc_url($vtour_link); ?>">
                    <img class="vrgirl" src="https://www.geotour.gr/wp-content/uploads/2024/06/VRgirl.webp" alt="VR Girl" />
                </a>
                <a class="text-link" href="<?php echo esc_url($vtour_link); ?>">
                    <span class="mobiletitle">G</span>eotour Virtual Tour
                    <span class="text">Opens Geotour Virtual Tour in the current position</span>
                </a>
            </div>
        </section>
    <?php endif; ?>
    
    <!-- Nearest listings grid shortcode (100vw) -->
    <section class="nearest-listings-full-section">
        <?php echo do_shortcode('[nearest-listings-grid]'); ?>
    </section>    <footer class="entry-footer">
        <?php
        // Display listing tags
        $tags = get_the_terms(get_the_ID(), 'listing-tag');
        if ($tags && !is_wp_error($tags)) {
            echo '<div class="listing-tags">';
            echo '<strong>' . __('Tags:', 'geotour') . '</strong> ';
            foreach ($tags as $tag) {
                echo '<a href="' . esc_url(geotour_get_taxonomy_listing_url('listing-tag', $tag->slug)) . '" class="listing-tag">' . esc_html($tag->name) . '</a>';
            }
            echo '</div>';
        }
        
        // Edit link for authorized users  
        edit_post_link(
            sprintf(
                wp_kses(
                    __('Edit <span class="screen-reader-text">%s</span>', 'geotour'),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post(get_the_title())
            ),
            '<span class="edit-link">',
            '</span>'
        );
        ?>
    </footer>

</article>