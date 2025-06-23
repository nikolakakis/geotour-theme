<?php
/**
 * Template part for displaying posts in archives
 *
 * @package Geotour_Mobile_First
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('content-article archive-item'); ?>>
    
    <?php if (has_post_thumbnail()) : ?>
        <div class="post-thumbnail">
            <a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                <?php the_post_thumbnail('medium_large', array('alt' => get_the_title())); ?>
            </a>
        </div>
    <?php endif; ?>
    
    <div class="post-content">
        <header class="entry-header">
            <?php
            the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');

            if ('post' === get_post_type()) :
                ?>
                <div class="entry-meta">
                    <?php geotour_posted_by(); ?>
                </div><!-- .entry-meta -->
            <?php endif; ?>
            
            <?php 
            // Add Yoast meta description if available
            $meta_desc = '';
            if (function_exists('YoastSEO')) {
                $meta_desc = YoastSEO()->meta->for_post(get_the_ID())->description;
            }
            
            // Fallback to excerpt only if no Yoast description exists
            if (empty($meta_desc) && has_excerpt()) {
                $meta_desc = get_the_excerpt();
            }
            
            if (!empty($meta_desc)) : ?>
                <div class="post-description">
                    <?php echo wp_kses_post($meta_desc); ?>
                </div>
            <?php endif; ?>
        </header><!-- .entry-header -->

        <div class="entry-summary">
            <?php /* No content needed here since meta description is shown above */ ?>
        </div><!-- .entry-summary -->

        <footer class="entry-footer">
            <?php geotour_entry_footer(); ?>
        </footer><!-- .entry-footer -->
    </div>
    
</article><!-- #post-<?php the_ID(); ?> -->
           
