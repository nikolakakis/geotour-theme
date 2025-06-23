<?php
/**
 * Template part for displaying main content area with sidebar for blog/archives
 *
 * @package Geotour_Mobile_First
 */
?>

<div class="content-wrapper">
    <div class="content-with-sidebar">
        
        <!-- Main Content Area -->
        <div class="main-content">
            <?php
            if (have_posts()) {

                if (is_home() && !is_front_page()) {
                    echo '<header class="page-header"><h1 class="page-title screen-reader-text">' . esc_html(get_the_title(get_option('page_for_posts'))) . '</h1></header>';
                }

                if (is_archive()) {
                    echo '<header class="page-header">';
                    the_archive_title('<h1 class="page-title">', '</h1>');
                    the_archive_description('<div class="archive-description">', '</div>');
                    echo '</header>';
                }

                /* Start the Loop */
                while (have_posts()) {
                    the_post();
                    get_template_part('template-parts/content', get_post_type());
                }

                // Previous/next page navigation
                the_posts_navigation(array(
                    'prev_text' => __('&larr; Older posts', 'geotour'),
                    'next_text' => __('Newer posts &rarr;', 'geotour'),
                ));

            } else {
                // If no content, include the "No posts found" template.
                get_template_part('template-parts/content', 'none');
            }
            ?>
        </div>

        <!-- Sidebar Area -->
        <aside class="sidebar-content">
            <div class="listing-sidebar">
                <?php if (is_active_sidebar('sidebar-blog')): ?>
                    <?php dynamic_sidebar('sidebar-blog'); ?>
                <?php else: ?>
                    <div class="sidebar-section">
                        <h3 class="sidebar-title"><?php _e('About this Blog', 'geotour'); ?></h3>
                        <div class="sidebar-content">
                            <p><?php _e('Add widgets to the "Blog Sidebar" in the Customizer or Appearance > Widgets.', 'geotour'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

    </div>
</div>
<?php echo "<!-- DEBUG: End of content-main.php -->"; ?>