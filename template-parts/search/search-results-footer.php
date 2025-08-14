<?php
/**
 * Template part for displaying search results footer
 *
 * @package Geotour_Mobile_First
 */

// Get search query and results info
$search_query = get_search_query();
$total_results = $wp_query->found_posts;
$current_page = max(1, get_query_var('paged'));
$posts_per_page = get_query_var('posts_per_page');
$total_pages = $wp_query->max_num_pages;

// Calculate result range
$start_result = ($current_page - 1) * $posts_per_page + 1;
$end_result = min($current_page * $posts_per_page, $total_results);
?>

<footer class="search-results-footer">
    <?php if ($total_results > 0) : ?>
        <div class="search-pagination-info">
            <p class="results-range">
                <?php
                if ($total_pages > 1) {
                    printf(
                        /* translators: 1: start result number, 2: end result number, 3: total results, 4: current page, 5: total pages */
                        esc_html__('Showing results %1$d-%2$d of %3$d (Page %4$d of %5$d)', 'geotour'),
                        $start_result,
                        $end_result,
                        $total_results,
                        $current_page,
                        $total_pages
                    );
                } else {
                    printf(
                        /* translators: 1: start result number, 2: end result number, 3: total results */
                        esc_html__('Showing all %3$d results', 'geotour'),
                        $start_result,
                        $end_result,
                        $total_results
                    );
                }
                ?>
            </p>
        </div>
        
        <!-- Additional Search Tips -->
        <div class="search-tips">
            <h3><?php _e('Search Tips:', 'geotour'); ?></h3>
            <ul>
                <li><?php _e('Try different keywords or phrases', 'geotour'); ?></li>
                <li><?php _e('Check your spelling', 'geotour'); ?></li>
                <li><?php _e('Use more general terms', 'geotour'); ?></li>
                <li><?php printf(__('Browse our <a href="%s">listings</a> or <a href="%s">blog posts</a>', 'geotour'), esc_url(home_url('/listing/')), esc_url(home_url('/blog/'))); ?></li>
            </ul>
        </div>
        
    <?php else : ?>
        
        <!-- No Results - Alternative Suggestions -->
        <div class="search-alternatives">
            <h3><?php _e('Try These Instead:', 'geotour'); ?></h3>
            
            <!-- Popular Search Terms -->
            <div class="popular-searches">
                <h4><?php _e('Popular Searches:', 'geotour'); ?></h4>
                <div class="search-suggestions">
                    <?php
                    $popular_terms = array('crete', 'archaeological', 'beach', 'monastery', 'venetian', 'minoan');
                    foreach ($popular_terms as $term) :
                    ?>
                        <a href="<?php echo esc_url(home_url('/?s=' . urlencode($term))); ?>" class="search-suggestion">
                            <?php echo esc_html(ucfirst($term)); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Recent Content -->
            <div class="recent-content">
                <h4><?php _e('Recent Content:', 'geotour'); ?></h4>
                <?php
                $recent_posts = get_posts(array(
                    'numberposts' => 3,
                    'post_type' => array('post', 'listing'),
                    'post_status' => 'publish'
                ));
                
                if ($recent_posts) :
                ?>
                    <ul class="recent-content-list">
                        <?php foreach ($recent_posts as $recent_post) : ?>
                            <li>
                                <a href="<?php echo esc_url(get_permalink($recent_post->ID)); ?>">
                                    <?php echo esc_html(get_the_title($recent_post->ID)); ?>
                                </a>
                                <span class="post-type"><?php echo esc_html(get_post_type_object($recent_post->post_type)->labels->singular_name); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        
    <?php endif; ?>
</footer>