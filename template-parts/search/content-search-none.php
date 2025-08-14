<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @package Geotour_Mobile_First
 */
?>

<section class="no-results not-found">
    <header class="page-header">
        <h2 class="page-title"><?php _e('Nothing Found', 'geotour'); ?></h2>
    </header><!-- .page-header -->

    <div class="page-content">
        <p><?php _e('Sorry, but nothing matched your search terms. Please try again with different keywords.', 'geotour'); ?></p>
        
        <!-- Enhanced Search Form -->
        <div class="search-form-container">
            <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <label for="search-field-content" class="screen-reader-text"><?php _e('Search for:', 'geotour'); ?></label>
                <input type="search" 
                       id="search-field-content" 
                       name="s" 
                       value="<?php echo esc_attr(get_search_query()); ?>" 
                       placeholder="<?php esc_attr_e('Try a different search...', 'geotour'); ?>"
                       class="search-field">
                <button type="submit" class="search-submit">
                    <span class="screen-reader-text"><?php _e('Search', 'geotour'); ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div><!-- .page-content -->
</section><!-- .no-results -->