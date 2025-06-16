<?php
/**
 * Template part for displaying single listing content
 *
 * @package Geotour_Mobile_First
 */

// Check if sidebar should be hidden for this listing
$hide_sidebar = get_field('hide_sidebar') || get_post_meta(get_the_ID(), '_hide_sidebar', true);
$layout_class = $hide_sidebar ? 'content-no-sidebar' : 'content-with-sidebar';
?>

<div class="content-wrapper">
    <div class="<?php echo esc_attr($layout_class); ?>">
        
        <!-- Main Content Area -->
        <div class="main-content">
            <article id="post-<?php the_ID(); ?>" <?php post_class('content-article listing-single listing-wide-content'); ?>>
                
                <div id="listing-content" class="entry-content">
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
                </section>
                
                <footer class="entry-footer">
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
        </div>
        
        <!-- Sidebar Area -->
        <?php if (!$hide_sidebar) : ?>
        <aside class="sidebar-content">
            <div class="listing-sidebar">
                
                <!-- Quick Info Section -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title"><?php _e('Quick Info', 'geotour'); ?></h3>
                    <div class="sidebar-content">
                        <?php
                        // Display categories
                        $categories = get_the_terms(get_the_ID(), 'listing-category');
                        if ($categories && !is_wp_error($categories)) {
                            echo '<p><strong>' . __('Category:', 'geotour') . '</strong><br>';
                            $category_links = [];
                            foreach ($categories as $category) {
                                $category_links[] = '<a href="/listing/?listing-category=' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</a>';
                            }
                            echo implode(', ', $category_links) . '</p>';
                        }
                        
                        // Display regions
                        $regions = get_the_terms(get_the_ID(), 'listing-region');
                        if ($regions && !is_wp_error($regions)) {
                            echo '<p><strong>' . __('Region:', 'geotour') . '</strong><br>';
                            $region_links = [];
                            foreach ($regions as $region) {
                                $region_links[] = '<a href="/listing/?listing-region=' . esc_attr($region->slug) . '">' . esc_html($region->name) . '</a>';
                            }
                            echo implode(', ', $region_links) . '</p>';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Navigation Section -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title"><?php _e('Navigation', 'geotour'); ?></h3>
                    <div class="sidebar-content">
                        <?php
                        $position_data = get_field('position');
                        if ($position_data && isset($position_data['markers'][0])) {
                            $marker = $position_data['markers'][0];
                            $lat = $marker['lat'];
                            $lng = $marker['lng'];
                            
                            echo '<ul>';
                            echo '<li><a href="https://www.google.com/maps?q=' . $lat . ',' . $lng . '" target="_blank">' . __('Open in Google Maps', 'geotour') . '</a></li>';
                            echo '<li><a href="https://www.geotour.gr/timeline/route-planner/?routelistings=' . get_the_ID() . '" target="_blank">' . __('Route Planner', 'geotour') . '</a></li>';
                            echo '</ul>';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Contact Information if available -->
                <?php 
                $contact_title = get_field('contact_title');
                $contact_phone = get_field('contact_phone');
                $contact_mobile = get_field('contact_mobile');
                $contact_website = get_field('contact_website');
                
                if ($contact_title || $contact_phone || $contact_mobile || $contact_website) : ?>
                <div class="sidebar-section">
                    <h3 class="sidebar-title"><?php _e('Contact', 'geotour'); ?></h3>
                    <div class="sidebar-content">
                        <?php if ($contact_title) : ?>
                            <p><strong><?php echo esc_html($contact_title); ?></strong></p>
                        <?php endif; ?>
                        
                        <?php if ($contact_phone) : ?>
                            <p><?php _e('Phone:', 'geotour'); ?> <?php echo esc_html($contact_phone); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($contact_mobile) : ?>
                            <p><?php _e('Mobile:', 'geotour'); ?> <?php echo esc_html($contact_mobile); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($contact_website) : ?>
                            <p><a href="<?php echo esc_url($contact_website); ?>" target="_blank"><?php _e('Visit Website', 'geotour'); ?></a></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Related Listings -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title"><?php _e('Related Listings', 'geotour'); ?></h3>
                    <div class="sidebar-content">
                        <?php
                        $related_args = array(
                            'post_type' => 'listing',
                            'posts_per_page' => 5,
                            'post__not_in' => array(get_the_ID()),
                            'tax_query' => array()
                        );
                        
                        // Add category filter if available
                        if ($categories && !is_wp_error($categories)) {
                            $related_args['tax_query'][] = array(
                                'taxonomy' => 'listing-category',
                                'field' => 'term_id',
                                'terms' => wp_list_pluck($categories, 'term_id')
                            );
                        }
                        
                        $related_query = new WP_Query($related_args);
                        
                        if ($related_query->have_posts()) {
                            echo '<ul>';
                            while ($related_query->have_posts()) {
                                $related_query->the_post();
                                echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
                            }
                            echo '</ul>';
                            wp_reset_postdata();
                        } else {
                            echo '<p>' . __('No related listings found.', 'geotour') . '</p>';
                        }
                        ?>
                    </div>
                </div>
                
            </div>
        </aside>
        <?php endif; ?>
        
    </div>
</div>