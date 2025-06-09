<?php
/**
 * Template part for displaying posts in archives
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Geotour_Mobile_First
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('content-article archive-item'); ?>>
    
    <?php if (has_post_thumbnail()) : ?>
        <div class="post-thumbnail">
            <a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                <?php the_post_thumbnail('medium', array('alt' => get_the_title())); ?>
            </a>
        </div>
    <?php endif; ?>
    
    <header class="entry-header">
        <?php
        if (is_singular()) :
            the_title('<h1 class="entry-title">', '</h1>');
        else :
            the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
        endif;

        if ('post' === get_post_type()) :
            ?>
            <div class="entry-meta">
                <?php
                geotour_posted_on();
                geotour_posted_by();
                ?>
            </div><!-- .entry-meta -->
        <?php endif; ?>
    </header><!-- .entry-header -->

    <div class="entry-summary">
        <?php the_excerpt(); ?>
    </div><!-- .entry-summary -->

    <footer class="entry-footer">
        <?php geotour_entry_footer(); ?>
    </footer><!-- .entry-footer -->
    
</article><!-- #post-<?php the_ID(); ?> -->
