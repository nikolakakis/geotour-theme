<?php
/**
 * Template part for displaying a post in search results
 *
 * @package Geotour_Mobile_First
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('search-result search-result-post'); ?>>
    
    <?php if (has_post_thumbnail()) : ?>
        <div class="search-result-thumbnail">
            <a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                <?php the_post_thumbnail('medium', array('alt' => get_the_title())); ?>
            </a>
        </div>
    <?php endif; ?>
    
    <div class="search-result-content">
        <header class="search-result-header">
            <div class="post-type-label">
                <?php
                $post_type = get_post_type();
                $post_type_object = get_post_type_object($post_type);
                $post_type_name = $post_type_object ? $post_type_object->labels->singular_name : ucfirst($post_type);
                ?>
                <span class="post-type-badge post-type-<?php echo esc_attr($post_type); ?>"><?php echo esc_html($post_type_name); ?></span>
            </div>
            
            <?php the_title('<h2 class="search-result-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>'); ?>
            
            <?php if ('post' === get_post_type()) : ?>
                <div class="post-meta">
                    <time class="entry-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                        <?php echo get_the_date(); ?>
                    </time>
                    
                    <?php
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
            <?php endif; ?>
        </header>

        <div class="search-result-excerpt">
            <?php 
            if (has_excerpt()) {
                the_excerpt();
            } else {
                echo '<p>' . wp_trim_words(get_the_content(), 30, '...') . '</p>';
            }
            ?>
        </div>

        <footer class="search-result-footer">
            <a href="<?php the_permalink(); ?>" class="read-more-link">
                <?php _e('Read More', 'geotour'); ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14m-7-7 7 7-7 7"/>
                </svg>
            </a>
        </footer>
    </div>
    
</article>