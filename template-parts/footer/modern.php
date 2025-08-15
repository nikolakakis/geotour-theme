<?php
/**
 * Template part for displaying the modern site footer
 * 
 * @package Geotour_Mobile_First
 */

// Get current language
$current_lang = function_exists('pll_current_language') ? pll_current_language() : 'en';
$is_english = ($current_lang === 'en');
?>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-top">
            <div class="footer-column">
                <h3><?php echo $is_english ? 'Search & have fun' : 'Αναζήτηση & Διασκέδαση'; ?></h3>
                <p>
                    <?php 
                    if ($is_english) {
                        echo 'Search anytime for whatever you need, for your business, fun or personal needs. <b>Panotours</b> helps you find it easy and fast.';
                    } else {
                        echo 'Αναζητήστε οποιαδήποτε στιγμή ό,τι χρειάζεστε, για την επιχείρησή σας, τη διασκέδαση ή τις προσωπικές σας ανάγκες. Το <b>Panotours</b> σας βοηθά να το βρείτε εύκολα και γρήγορα.';
                    }
                    ?>
                </p>
                <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                    <input type="text" name="s" placeholder="<?php echo $is_english ? 'Keyword to search...?' : 'Λέξη-κλειδί για αναζήτηση...?'; ?>">
                    <button type="submit" class="search-submit" aria-label="<?php echo $is_english ? 'Search' : 'Αναζήτηση'; ?>" title="<?php echo $is_english ? 'Search the website' : 'Αναζήτηση στον ιστότοπο'; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                    </button>
                    <?php if (function_exists('pll_current_language')) : ?>
                        <input type="hidden" name="lang" value="<?php echo esc_attr($current_lang); ?>">
                    <?php endif; ?>
                </form>
            </div>

            <div class="footer-column">
                <h3><?php echo $is_english ? 'Geotour' : 'Geotour'; ?></h3>
                <ul class="footer-links">
                    <?php
                    $geotour_links = [
                        ['title_en' => 'About Geotour', 'title_el' => 'Σχετικά με το Geotour', 'url' => home_url('/about-geotour')],
                        ['title_en' => 'Geotour timeline', 'title_el' => 'Χρονολόγιο Geotour', 'url' => home_url('/about-geotour/geotour-timeline/')],                        
                        ['title_en' => 'A Technical Overview', 'title_el' => 'Τεχνική Επισκόπηση', 'url' => home_url('/geotour-a-technical-overview-of-a-wordpress-based-platform-for-sustainable-tourism-and-cultural-discovery-in-crete')],
                    ];

                    foreach ($geotour_links as $link) {
                        $title = $is_english ? $link['title_en'] : $link['title_el'];
                        echo '<li><a href="' . esc_url($link['url']) . '">' . esc_html($title) . '</a></li>';
                    }
                    ?>
                </ul>
            </div>

            <div class="footer-column">
                <h3><?php echo $is_english ? 'Explore' : 'Εξερεύνηση'; ?></h3>
                <ul class="footer-links">
                    <?php
                    // Get listing categories for the current language
                    $category_args = [
                        'taxonomy' => 'listing-category',
                        'hide_empty' => true,
                        'number' => 4, // Limit to 4 categories
                        'orderby' => 'count',
                        'order' => 'DESC'
                    ];
                    
                    if (function_exists('pll_current_language')) {
                        $category_args['lang'] = $current_lang;
                    }
                    
                    $categories = get_terms($category_args);
                    
                    if (!empty($categories) && !is_wp_error($categories)) {
                        foreach ($categories as $category) {
                            // Create a link to filtered listing map
                            $map_url = add_query_arg('listing-category', $category->slug, home_url('/listing/'));
                            echo '<li><a href="' . esc_url($map_url) . '">' . esc_html($category->name) . '</a></li>';
                        }
                    } else {
                        // Fallback static categories
                        $explore_links = [
                            ['title_en' => 'Cretan History', 'title_el' => 'Ιστορία της Κρήτης', 'url' => home_url('/listing/?listing-category=history')],
                            ['title_en' => 'Archaeological Sites', 'title_el' => 'Αρχαιολογικοί Χώροι', 'url' => home_url('/listing/?listing-category=archaeological-sites')],
                            ['title_en' => 'Religion Points of Interest', 'title_el' => 'Θρησκευτικά Αξιοθέατα', 'url' => home_url('/listing/?listing-category=religion')],
                            ['title_en' => 'Fortifications', 'title_el' => 'Οχυρώσεις', 'url' => home_url('/listing/?listing-category=fortifications')],
                        ];

                        foreach ($explore_links as $link) {
                            $title = $is_english ? $link['title_en'] : $link['title_el'];
                            echo '<li><a href="' . esc_url($link['url']) . '">' . esc_html($title) . '</a></li>';
                        }
                    }
                    ?>
                </ul>
            </div>

            <div class="footer-column">
                <h3><?php echo $is_english ? 'Panotours' : 'Panotours'; ?></h3>
                <ul class="footer-links">
                    <?php
                    $info_links = [
                        ['title_en' => 'Contact', 'title_el' => 'Επικοινωνία', 'url' => home_url('/contact')],
                        ['title_en' => 'Terms & Conditions', 'title_el' => 'Όροι & Προϋποθέσεις', 'url' => home_url('/terms')],
                        ['title_en' => 'Cookies', 'title_el' => 'Cookies', 'url' => home_url('/cookies')],
                        ['title_en' => 'Privacy Policy', 'title_el' => 'Πολιτική Απορρήτου', 'url' => home_url('/privacy')],
                    ];

                    foreach ($info_links as $link) {
                        $title = $is_english ? $link['title_en'] : $link['title_el'];
                        echo '<li><a href="' . esc_url($link['url']) . '">' . esc_html($title) . '</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>

        <hr class="footer-divider">

        <div class="footer-bottom">
            <div class="copyright-info">
                <img src="<?php echo esc_url(get_theme_mod('footer_logo', 'https://www.geotour.gr/wp-content/uploads/2024/04/cropped-panotours_oil.png')); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="copyright-logo">
                <p class="copyright-text">
                    <?php if ($is_english) : ?>
                        <a href="<?php echo esc_url(home_url('/')); ?>">Geotour website</a> by <a href="https://panotours.gr" target="_blank">Nikolakakis Manolis</a> is licensed under 
                        <a class="cc-license" href="https://creativecommons.org/licenses/by-nc-sa/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer">
                            CC BY-NC-SA 4.0<img src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1" alt="CC"><img src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1" alt="BY"><img src="https://mirrors.creativecommons.org/presskit/icons/nc.svg?ref=chooser-v1" alt="NC"><img src="https://mirrors.creativecommons.org/presskit/icons/sa.svg?ref=chooser-v1" alt="SA">
                        </a>
                    <?php else : ?>
                        <a href="<?php echo esc_url(home_url('/')); ?>">Η ιστοσελίδα Geotour</a> από τον <a href="https://panotours.gr" target="_blank">Νικολακάκη Μανώλη</a> διατίθεται με άδεια 
                        <a class="cc-license" href="https://creativecommons.org/licenses/by-nc-sa/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer">
                            CC BY-NC-SA 4.0<img src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1" alt="CC"><img src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1" alt="BY"><img src="https://mirrors.creativecommons.org/presskit/icons/nc.svg?ref=chooser-v1" alt="NC"><img src="https://mirrors.creativecommons.org/presskit/icons/sa.svg?ref=chooser-v1" alt="SA">
                        </a>
                    <?php endif; ?>
                </p>
                <p class="gemi-number">Αριθμός ΓΕΜΗ 185790727000</p>
            </div>
            <div class="social-links">
                <?php
                // Social media links - can be customized in theme options
                $social_links = [
                    ['platform' => 'facebook', 'url' => 'https://facebook.com/geotourcrete', 'icon' => 'fab fa-facebook-f'],
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/geotour.gr', 'icon' => 'fab fa-instagram'],
                    ['platform' => 'youtube', 'url' => 'https://youtube.com/channel/panotours', 'icon' => 'fab fa-youtube']
                ];

                foreach ($social_links as $link) {
                    $label = $is_english ? ucfirst($link['platform']) : ucfirst($link['platform']);
                    echo '<a href="' . esc_url($link['url']) . '" aria-label="' . esc_attr($label) . '" target="_blank" rel="noopener">';
                    echo '<i class="' . esc_attr($link['icon']) . '"></i>';
                    echo '</a>';
                }
                ?>
            </div>
        </div>
    </div>
</footer>