<?php
/**
 * Template part for displaying single listing content
 *
 * @package Geotour_Mobile_First
 */

// Check if sidebar should be hidden for this listing
// Only use ACF field, ignore legacy meta field
$acf_hide_sidebar = get_field('hide_sidebar');
// Convert to proper boolean: true means hide, false/null means show
$hide_sidebar = ($acf_hide_sidebar === true || $acf_hide_sidebar === 1 || $acf_hide_sidebar === '1');
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
                
                <!-- Viator Activities Section (100vw) -->
                <?php
                // Get coordinates from ACF position field
                $position_data = get_field('position');
                $viator_lat = null;
                $viator_lng = null;
                
                if ($position_data && isset($position_data['markers']) && !empty($position_data['markers'])) {
                    $marker = $position_data['markers'][0];
                    $viator_lat = isset($marker['lat']) ? floatval($marker['lat']) : null;
                    $viator_lng = isset($marker['lng']) ? floatval($marker['lng']) : null;
                } else {
                    $viator_lat = 35.182780;
                    $viator_lng = 24.770842;
                }
                
                // Get keywords from ACF field
                $viator_keywords = get_field('viator_keywords');
                if (empty($viator_keywords)) $viator_keywords = "Crete";
                // Only display if we have coordinates and keywords
                ?>
                <section class="viator-activities-full-section">
                    <?php echo do_shortcode('[viator_activities count="24" lat="' . $viator_lat . '" lng="' . $viator_lng . '" keyword="' . esc_attr($viator_keywords) . '" min_rating="3"]'); ?>
                </section>                
                    
                <!-- Virtual Tour Section (100vw) -->
                <?php
                $vtour_link = get_field('vtour_link');
                if ($vtour_link) : ?>
                    <section class="virtual-tour-full-section">
                        <?php if (!wp_is_mobile()) : ?>
                        <!-- Desktop Virtual Tour -->
                        <div id="geotour-overlay" class="desktop-vtour">
                            <div id="geotour-tour">
                                <img class="vrgirl" src="https://www.geotour.gr/wp-content/uploads/2024/06/VRgirl.webp" alt="Virtual Reality Girl - Geotour VR Experience" />
                                <h3 class="geotour-title"><span>G</span>eotour Virtual Tour</h3>
                                <p>Drag the mouse to change the field of view of the virtual tour, or use a VR headset to roam to Crete in an alternative way!</p>
                                <div class="geotour-vtour">
                                    <span>You can open the tour in fullscreen by double clicking (or double tap with fingers) on it! Double click again to exit the fullscreen mode.</span>
                                    <iframe src="<?php echo esc_url($vtour_link); ?>" 
                                            title="<?php echo esc_attr(sprintf(__('Virtual Tour of %s', 'geotour'), get_the_title())); ?>" 
                                            allowfullscreen></iframe>
                                </div>
                            </div>
                        </div>
                        <?php else : ?>
                        <!-- Mobile Virtual Tour -->
                        <div id="geotour-mobile-tour" class="mobile-vtour">
                            <a href="<?php echo esc_url($vtour_link); ?>" 
                               aria-label="<?php echo esc_attr(sprintf(__('Open Virtual Tour of %s', 'geotour'), get_the_title())); ?>">
                                <img class="vrgirl" src="https://www.geotour.gr/wp-content/uploads/2024/06/VRgirl.webp" alt="Virtual Reality Girl - Geotour VR Experience" />
                            </a>
                            <a class="text-link" href="<?php echo esc_url($vtour_link); ?>"
                               aria-label="<?php echo esc_attr(sprintf(__('Launch Virtual Tour of %s', 'geotour'), get_the_title())); ?>">
                                <span class="mobiletitle">G</span>eotour Virtual Tour
                                <span class="text">Opens Geotour Virtual Tour in the current position</span>
                            </a>
                        </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
                
                <!-- Nearest listings grid shortcode (100vw) -->
                <section class="nearest-listings-full-section">
                    <?php echo do_shortcode('[listings-grid type="nearest" limit="12"]'); ?>
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
                
                <!-- Cretan Timeline Display -->
                <div id="cretan-timeline-display"></div>

            </article>
        </div>
          <!-- Sidebar Area -->
        <?php if (!$hide_sidebar) : ?>
        <aside class="sidebar-content">
            <div class="listing-sidebar">
                  <!-- Festive Dates -->
                <div id="festivedates"></div>
                
                <!-- Related News Posts with fallback -->
                <?php
                // Check if related-newsposts shortcode has results
                $current_listing_id = get_the_ID();
                $related_posts_args = array(
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'posts_per_page' => 1, // Just check if any exist
                    'meta_query' => array(
                        array(
                            'key' => 'related_listings',
                            'value' => '"' . $current_listing_id . '"',
                            'compare' => 'LIKE',
                        )
                    )
                );
                
                $has_related_posts = new WP_Query($related_posts_args);
                
                if ($has_related_posts->have_posts()) {
                    // Has related posts, output shortcode without title
                    echo do_shortcode('[related-newsposts]');
                } else {
                    // No related posts, show latest 5 posts with title
                    echo '<h3 class="sidebar-title">' . __('Latest Posts', 'geotour') . '</h3>';
                    echo '<div class="sidebar-content related-posts-list">';
                    
                    $latest_posts = new WP_Query(array(
                        'post_type' => 'post',
                        'post_status' => 'publish',
                        'posts_per_page' => 5,
                        'orderby' => 'date',
                        'order' => 'DESC'
                    ));
                    
                    if ($latest_posts->have_posts()) {
                        $post_count = $latest_posts->post_count;
                        $current_index = 0;
                        
                        while ($latest_posts->have_posts()) {
                            $latest_posts->the_post();
                            $postid = get_the_ID();
                            $current_index++;
                            
                            $post_title = get_the_title($postid);
                            $post_permalink = get_the_permalink($postid);
                            $post_creation_date = get_the_date('', $postid);
                            
                            // Get the thumbnail URL
                            $thumbnail_id = get_post_thumbnail_id($postid);
                            $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'thumbnail');
                            $placeholder_url = "https://placehold.co/80x80/cccccc/ffffff?text=No+Image";
                            
                            // Get categories for the post
                            $post_terms = get_the_terms($postid, 'category');
                            $term_output = '';
                            if ($post_terms && !is_wp_error($post_terms)) {
                                $term_count = 0;
                                foreach ($post_terms as $term) {
                                    if ($term_count >= 2) break;
                                    $term_link = get_term_link($term);
                                    if (!is_wp_error($term_link)) {
                                        $term_output .= '<a href="' . esc_url($term_link) . '" class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full hover:bg-gray-200 transition-colors duration-200" style="font-size: 11px; background: #f3f4f6; color: #374151; padding: 2px 8px; border-radius: 12px; text-decoration: none; margin-right: 6px;">' . esc_html($term->name) . '</a>';
                                        $term_count++;
                                    }
                                }
                            }
                            
                            echo '<article>';
                            
                            // Title
                            echo '<h4 style="font-size: 0.875rem; font-weight: 500; line-height: 1.2; margin-bottom: 0.5rem;">';
                            echo '<a href="' . esc_url($post_permalink) . '" style="color: inherit; text-decoration: none;" onmouseover="this.style.color=\'#2563eb\'" onmouseout="this.style.color=\'inherit\'">';
                            echo esc_html($post_title);
                            echo '</a>';
                            echo '</h4>';
                            
                            // Image and meta info row
                            echo '<div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 1rem;">';
                            
                                // Image
                                echo '<a href="' . esc_url($post_permalink) . '" style="display: block; flex-shrink: 0;" aria-label="' . esc_attr(sprintf(__('Read more about %s', 'geotour'), $post_title)) . '">';
                                echo '<img src="' . esc_url($thumbnail_url ? $thumbnail_url : $placeholder_url) . '" ';
                                echo 'alt="' . esc_attr($thumbnail_url ? sprintf(__('Featured image for %s', 'geotour'), $post_title) : __('No image available', 'geotour')) . '" ';
                                echo 'style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;">';
                                echo '</a>';
                                
                                // Date & Categories
                                echo '<div style="flex: 1; min-width: 0;">';
                                    echo '<time datetime="' . esc_attr(get_the_date('c', $postid)) . '" style="display: block; font-size: 0.75rem; color: #6b7280; margin-bottom: 4px;">';
                                    echo esc_html($post_creation_date);
                                    echo '</time>';
                                    
                                    if (!empty($term_output)) {
                                        echo '<div style="display: flex; flex-wrap: wrap; gap: 6px;">';
                                        echo $term_output;
                                        echo '</div>';
                                    }
                                echo '</div>';
                                
                            echo '</div>';
                            
                            // Separator
                            if ($current_index < $post_count) {
                                echo '<hr style="margin: 20px 0; border: none; border-top: 1px solid #e5e7eb;">';
                            }
                            
                            echo '</article>';
                        }
                    }
                    
                    echo '</div>';
                    wp_reset_postdata();
                }
                
                wp_reset_postdata(); // Reset the has_related_posts query
                ?>
                
                <!-- Related People -->
                <?php echo do_shortcode('[related-people]'); ?>
                
                <!-- Related Photos -->
                <?php echo do_shortcode('[related-photos]'); ?>
                
                <!-- Search Culture Title -->
                <div id="searchculturetitle"></div>
                
                <!-- Search Culture -->
                <div id="searchculture"></div>
                
                <!-- Geotour Ferry Hopper Widget -->
                <?php echo do_shortcode('[geotour_ferryhopper_widget]'); ?>
                
            </div>
        </aside>
        <?php endif; ?>
        
    </div>
</div>