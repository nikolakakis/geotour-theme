<?php
/**
 * Template for displaying single posts with sidebar support
 * 
 * @package Geotour_Mobile_First
 */

get_header();
?>

<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        
        <div class="content-wrapper">
            <?php
            // Check if sidebar should be hidden
            // Only use ACF field, ignore legacy meta field
            $acf_hide_sidebar = get_field('hide_sidebar');
            // Convert to proper boolean: true means hide, false/null means show
            $hide_sidebar = ($acf_hide_sidebar === true || $acf_hide_sidebar === 1 || $acf_hide_sidebar === '1');
            $layout_class = $hide_sidebar ? 'content-no-sidebar' : 'content-with-sidebar';
            ?>
            
            <div class="<?php echo esc_attr($layout_class); ?>">
                
                <!-- Main Content Area -->
                <div class="main-content">
                    <article id="post-<?php the_ID(); ?>" <?php post_class('content-article'); ?>>
                        
                        <header class="entry-header">
                            <h1 class="entry-title"><?php the_title(); ?></h1>
                            
                            <div class="entry-meta">
                                <?php
                                echo '<time class="entry-date" datetime="' . esc_attr(get_the_date('c')) . '">';
                                echo get_the_date();
                                echo '</time>';
                                
                                $categories = get_the_category();
                                if (!empty($categories)) {
                                    echo ' • ';
                                    $category_links = array();
                                    foreach ($categories as $category) {
                                        $category_links[] = '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>';
                                    }
                                    echo implode(', ', $category_links);
                                }
                                ?>
                            </div>
                        </header>
                        
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                        
                        <footer class="entry-footer">
                            <?php
                            // Display post tags
                            $tags = get_the_tags();
                            if ($tags) {
                                echo '<div class="post-tags">';
                                echo '<strong>' . __('Tags:', 'geotour') . '</strong> ';
                                foreach ($tags as $tag) {
                                    echo '<a href="' . esc_url(get_tag_link($tag->term_id)) . '" class="post-tag">' . esc_html($tag->name) . '</a>';
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
                        <!-- Post Info Section -->
                        <div class="sidebar-section">
                            <h3 class="sidebar-title"><?php _e('Post Info', 'geotour'); ?></h3>
                            <div class="sidebar-content">
                                <?php
                                // Display categories
                                $categories = get_the_category();
                                if (!empty($categories)) {
                                    echo '<p><strong>' . __('Categories:', 'geotour') . '</strong><br>';
                                    $category_links = [];
                                    foreach ($categories as $category) {
                                        $category_links[] = '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>';
                                    }
                                    echo implode(', ', $category_links) . '</p>';
                                }                                
                                
                                
                                // Display publish date
                                echo '<p><strong>' . __('Published:', 'geotour') . '</strong><br>';
                                echo get_the_date() . '</p>';
                                ?>
                            </div>
                        </div>
                        
                        <!-- Related Posts -->
                        <div class="sidebar-section">
                            <h3 class="sidebar-title"><?php _e('Other Posts', 'geotour'); ?></h3>
                            <div class="sidebar-content">
                                <?php
                                $related_args = array(
                                    'post_type' => 'post',
                                    'posts_per_page' => 5,
                                    'post__not_in' => array(get_the_ID()),
                                    'category__in' => wp_get_post_categories(get_the_ID())
                                );
                                
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
                                    echo '<p>' . __('No related posts found.', 'geotour') . '</p>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <!-- Related Listings Shortcode -->
                        <div class="sidebar-section">
                            <?php echo do_shortcode('[related-listings-to-post]'); ?>
                        </div>
                        
                    </div>
                </aside>
                <?php endif; ?>
                
            </div>
        </div>
        
        <?php
        // If comments are open or there are comments, load the comment template
        if (comments_open() || get_comments_number()) :
            echo '<div class="content-wrapper"><div class="content-no-sidebar"><div class="main-content">';
            comments_template();
            echo '</div></div></div>';
        endif;
        ?>
        
    <?php endwhile; ?>
<?php else : ?>
    <div class="content-wrapper">
        <div class="content-no-sidebar">
            <div class="main-content">
                <?php get_template_part('template-parts/content', 'none'); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
get_footer();
?>
