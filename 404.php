<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Geotour_Mobile_First
 */

get_header();
?>

<main id="primary" class="site-main error-404-page">
    <!-- Hero background -->
    <div class="error-hero-background">
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/photos/cretan-man-with-flag.webp'); ?>" 
             alt="<?php esc_attr_e('Cretan man with flag', 'geotour'); ?>" 
             class="hero-image">
        <div class="hero-overlay"></div>
    </div>
    
    <div class="main-container">
        <section class="error-404 not-found">
            <header class="page-header">
                <div class="error-code">404</div>
                <h1 class="page-title"><?php esc_html_e('Oops! That page can&rsquo;t be found.', 'geotour'); ?></h1>
                
                <!-- Greek language hint -->
                <div class="language-hint">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="info-icon">
                        <path d="M13,9H11V7H13M13,17H11V11H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z"/>
                    </svg>
                    <span><?php esc_html_e('If you were looking for Greek content, it might be because the Greek language is currently disabled.', 'geotour'); ?></span>
                </div>
            </header><!-- .page-header -->

            <div class="page-content">
                <p class="error-message">
                    <?php esc_html_e('It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'geotour'); ?>
                </p>

                <!-- Search Form -->
                <div class="error-search">
                    <h3><?php esc_html_e('Search for something else', 'geotour'); ?></h3>
                    <?php get_search_form(); ?>
                </div>

                <!-- Popular Categories -->
                <div class="error-categories">
                    <h3><?php esc_html_e('Explore Crete by Category', 'geotour'); ?></h3>
                    <div class="category-grid">
                        <?php
                        $categories = get_terms([
                            'taxonomy' => 'listing-category',
                            'hide_empty' => true,
                            'number' => 6,
                            'parent' => 0, // Only top-level categories
                        ]);

                        if (!empty($categories) && !is_wp_error($categories)) :
                            foreach ($categories as $category) : ?>
                                <a href="<?php echo esc_url(get_term_link($category)); ?>" class="category-card">
                                    <span class="category-name"><?php echo esc_html($category->name); ?></span>
                                    <span class="category-count"><?php printf(_n('%d listing', '%d listings', $category->count, 'geotour'), $category->count); ?></span>
                                </a>
                            <?php endforeach;
                        endif; ?>
                    </div>
                </div>

                <!-- Quick Navigation -->
                <div class="error-navigation">
                    <h3><?php esc_html_e('Quick Navigation', 'geotour'); ?></h3>
                    <div class="nav-buttons">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="nav-button home-button">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z"/>
                            </svg>
                            <?php esc_html_e('Go Home', 'geotour'); ?>
                        </a>
                        
                        <a href="<?php echo esc_url(home_url('/listing')); ?>" class="nav-button map-button">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M15,19L9,16.89V5L15,7.11M20.5,3C20.44,3 20.39,3 20.28,3L15,5.1L9,3L3.36,4.9C3.15,4.97 3,5.15 3,5.38V20.5A0.5,0.5 0 0,0 3.5,21C3.55,21 3.61,21 3.72,20.95L9,18.9L15,21L20.64,19.1C20.85,19 21,18.85 21,18.62V3.5A0.5,0.5 0 0,0 20.5,3Z"/>
                            </svg>
                            <?php esc_html_e('Explore Map', 'geotour'); ?>
                        </a>
                        
                        <?php if (has_nav_menu('primary')) : ?>
                            <button id="404-menu-toggle" class="nav-button menu-button">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M3,6H21V8H3V6M3,11H21V13H3V11M3,16H21V18H3V16Z"/>
                                </svg>
                                <?php esc_html_e('Menu', 'geotour'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Listings -->
                <?php
                $recent_listings = new WP_Query([
                    'post_type' => 'listing',
                    'posts_per_page' => 3,
                    'post_status' => 'publish',
                    'meta_query' => [
                        [
                            'key' => 'position',
                            'compare' => 'EXISTS'
                        ]
                    ]
                ]);

                if ($recent_listings->have_posts()) : ?>
                    <div class="error-recent-listings">
                        <h3><?php esc_html_e('Recent Discoveries', 'geotour'); ?></h3>
                        <div class="listings-grid">
                            <?php while ($recent_listings->have_posts()) : $recent_listings->the_post(); ?>
                                <article class="listing-card">
                                    <a href="<?php the_permalink(); ?>" class="listing-link">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <div class="listing-image">
                                                <?php the_post_thumbnail('medium'); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="listing-content">
                                            <h4 class="listing-title"><?php the_title(); ?></h4>
                                            <?php
                                            $regions = get_the_terms(get_the_ID(), 'listing-region');
                                            if (!empty($regions) && !is_wp_error($regions)) : ?>
                                                <span class="listing-region"><?php echo esc_html($regions[0]->name); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </article>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php wp_reset_postdata();
                endif; ?>

            </div><!-- .page-content -->
        </section><!-- .error-404 -->
    </div>
</main><!-- #main -->

<script>
// Add menu toggle functionality for 404 page
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('404-menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            // Try to trigger the main menu
            const mainMenuToggle = document.getElementById('hamburger-icon');
            if (mainMenuToggle) {
                mainMenuToggle.click();
            }
        });
    }
});
</script>

<?php
get_footer();