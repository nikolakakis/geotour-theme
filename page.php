<?php
/**
 * Template for displaying pages with sidebar support
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
                        
                        
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                        
                        <footer class="entry-footer">
                            <?php
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
                        
                        <!-- Page Info Section -->
                        <div class="sidebar-section">
                            <h3 class="sidebar-title"><?php _e('Page Info', 'geotour'); ?></h3>
                            <div class="sidebar-content">
                                <?php
                                // Display last modified date
                                echo '<p><strong>' . __('Last Updated:', 'geotour') . '</strong><br>';
                                echo get_the_modified_date() . '</p>';
                                
                                // Display parent page if exists
                                $parent_id = wp_get_post_parent_id(get_the_ID());
                                if ($parent_id) {
                                    echo '<p><strong>' . __('Parent Page:', 'geotour') . '</strong><br>';
                                    echo '<a href="' . esc_url(get_permalink($parent_id)) . '">' . esc_html(get_the_title($parent_id)) . '</a></p>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <!-- Child Pages -->
                        <?php
                        $child_pages = get_children(array(
                            'post_parent' => get_the_ID(),
                            'post_type' => 'page',
                            'post_status' => 'publish',
                            'numberposts' => -1,
                            'orderby' => 'menu_order',
                            'order' => 'ASC'
                        ));
                        
                        if (!empty($child_pages)) : ?>
                        <div class="sidebar-section">
                            <h3 class="sidebar-title"><?php _e('Child Pages', 'geotour'); ?></h3>
                            <div class="sidebar-content">
                                <ul>
                                    <?php foreach ($child_pages as $child) : ?>
                                        <li><a href="<?php echo esc_url(get_permalink($child->ID)); ?>"><?php echo esc_html($child->post_title); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Sibling Pages if this page has a parent -->
                        <?php if ($parent_id) : 
                            $sibling_pages = get_children(array(
                                'post_parent' => $parent_id,
                                'post_type' => 'page',
                                'post_status' => 'publish',
                                'numberposts' => -1,
                                'orderby' => 'menu_order',
                                'order' => 'ASC',
                                'post__not_in' => array(get_the_ID())
                            ));
                            
                            if (!empty($sibling_pages)) : ?>
                            <div class="sidebar-section">
                                <h3 class="sidebar-title"><?php _e('Related Pages', 'geotour'); ?></h3>
                                <div class="sidebar-content">
                                    <ul>
                                        <?php foreach ($sibling_pages as $sibling) : ?>
                                            <li><a href="<?php echo esc_url(get_permalink($sibling->ID)); ?>"><?php echo esc_html($sibling->post_title); ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
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
