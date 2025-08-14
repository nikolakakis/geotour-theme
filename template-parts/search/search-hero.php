<?php
/**
 * Template part for displaying search page hero section
 *
 * @package Geotour_Mobile_First
 */

// Get search query
$search_query = get_search_query();
$total_results = $wp_query->found_posts;
?>

<section class="hero-section search-hero-section">
    <div class="hero-overlay"></div>
    
    <div class="hero-content">
        <div class="hero-container">
            
            <!-- Search breadcrumb -->
            <div class="search-hero-breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="breadcrumb-home">
                    <?php _e('Home', 'geotour'); ?>
                </a>
                <span class="breadcrumb-separator">•</span>
                <span class="breadcrumb-current"><?php _e('Search Results', 'geotour'); ?></span>
            </div>
            
            <!-- Main search title (H1 for SEO) -->
            <h1 class="hero-title search-hero-title">
                <?php if (!empty($search_query)) : ?>
                    <?php printf(__('Search Results for "%s"', 'geotour'), '<span class="search-query">' . esc_html($search_query) . '</span>'); ?>
                <?php else : ?>
                    <?php _e('Search Results', 'geotour'); ?>
                <?php endif; ?>
            </h1>
            
            <!-- Results count -->
            <?php if (!empty($search_query)) : ?>
                <div class="search-hero-count">
                    <?php if ($total_results > 0) : ?>
                        <span class="results-found">
                            <?php
                            printf(
                                _n(
                                    '%d result found',
                                    '%d results found',
                                    $total_results,
                                    'geotour'
                                ),
                                $total_results
                            );
                            ?>
                        </span>
                    <?php else : ?>
                        <span class="no-results">
                            <?php _e('No results found', 'geotour'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Scroll indicator -->
            <div class="hero-scroll-indicator">
                <div class="scroll-arrow"></div>
            </div>
            
        </div>
    </div>
    
</section>