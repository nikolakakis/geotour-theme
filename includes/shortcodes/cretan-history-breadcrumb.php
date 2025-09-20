<?php
/**
 * Cretan History Breadcrumb Shortcode
 * 
 * @package Geotour_Mobile_First
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cretan History Timeline Breadcrumb Shortcode
 * 
 * Usage: [cretan_history_breadcrumb active="minoan"]
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function geotour_cretan_history_breadcrumb_shortcode($atts) {
    $atts = shortcode_atts(array(
        'active' => 'cretan-history', // Default active period
        'show_text' => 'false' // Option to show period text labels
    ), $atts, 'cretan_history_breadcrumb');

    // Define all historical periods with their data
    $periods = array(
        'cretan-history' => array(
            'url' => '/about-geotour/cretan-history/',
            'title' => __('Cretan History', 'geotour'),
            'icon' => '',
            'class' => 'main-history'
        ),
        'prehistoric' => array(
            'url' => '/about-geotour/cretan-history/prehistoric-period/',
            'title' => __('Prehistoric Period', 'geotour'),
            'icon' => 'PREHISTORY.webp',
            'class' => 'prehistoric'
        ),
        'minoan' => array(
            'url' => '/about-geotour/cretan-history/minoan-period/',
            'title' => __('Minoan Period', 'geotour'),
            'icon' => 'MINOAN.webp',
            'class' => 'minoan'
        ),
        'dark-ages' => array(
            'url' => '/about-geotour/cretan-history/dark-ages-hystero-minoan-age/',
            'title' => __('Dark Ages', 'geotour'),
            'icon' => 'DARKAGES.webp',
            'class' => 'dark-ages'
        ),
        'classical' => array(
            'url' => '/about-geotour/cretan-history/classical-period/',
            'title' => __('Classical Period', 'geotour'),
            'icon' => 'CLASSICAL.webp',
            'class' => 'classical'
        ),
        'hellenistic' => array(
            'url' => '/about-geotour/cretan-history/hellenistic-period/',
            'title' => __('Hellenistic Period', 'geotour'),
            'icon' => 'HELLENISTIC.webp',
            'class' => 'hellenistic'
        ),
        'roman' => array(
            'url' => '/about-geotour/cretan-history/roman-period/',
            'title' => __('Roman Period', 'geotour'),
            'icon' => 'ROMAN.webp',
            'class' => 'roman'
        ),
        'byzantine-i' => array(
            'url' => '/about-geotour/cretan-history/byzantine-period/',
            'title' => __('Byzantine Period I', 'geotour'),
            'icon' => 'BYZANTINE.webp',
            'class' => 'byzantine-i'
        ),
        'arab' => array(
            'url' => '/about-geotour/cretan-history/emirate-of-crete/',
            'title' => __('Emirate of Crete', 'geotour'),
            'icon' => 'ARAB.webp',
            'class' => 'arab'
        ),
        'byzantine-ii' => array(
            'url' => '/about-geotour/cretan-history/byzantine-period-2/',
            'title' => __('Byzantine Period II', 'geotour'),
            'icon' => 'BYZANTINE.webp',
            'class' => 'byzantine-ii'
        ),
        'venetian' => array(
            'url' => '/about-geotour/cretan-history/venetian-period/',
            'title' => __('Venetian Period', 'geotour'),
            'icon' => 'VENETIAN.webp',
            'class' => 'venetian'
        ),
        'ottoman' => array(
            'url' => '/about-geotour/cretan-history/ottoman-period/',
            'title' => __('Ottoman Period', 'geotour'),
            'icon' => 'OTTOMAN.webp',
            'class' => 'ottoman'
        ),
        'modern' => array(
            'url' => '/about-geotour/cretan-history/modern-era-autonomy-unification/',
            'title' => __('Modern Era', 'geotour'),
            'icon' => 'MODERN.webp',
            'class' => 'modern'
        )
    );

    $active_period = sanitize_key($atts['active']);
    $show_text = filter_var($atts['show_text'], FILTER_VALIDATE_BOOLEAN);
    
    // Start building the HTML
    $html = '<div id="cretan-history-breadcrumb" class="breadcrumb">';
    
    foreach ($periods as $period_key => $period_data) {
        $is_active = ($period_key === $active_period);
        $classes = array('breadcrumb-item');
        
        if ($is_active) {
            $classes[] = 'active';
        }
        
        $class_string = implode(' ', $classes);
        
        $html .= sprintf(
            '<a href="%s" class="%s" title="%s">',
            esc_url($period_data['url']),
            esc_attr($class_string),
            esc_attr($period_data['title'])
        );
        
        // First item (main history) shows text, others show icons
        if ($period_key === 'cretan-history') {
            $html .= esc_html($period_data['title']);
        } else {
            // Icon periods
            $html .= sprintf(
                '<div class="period-icon %s">',
                esc_attr($period_data['class'])
            );
            
            if (!empty($period_data['icon'])) {
                $icon_url = 'https://www.geotour.gr/wp-content/uploads/2024/12/' . $period_data['icon'];
                $html .= sprintf(
                    '<img src="%s" alt="%s" loading="lazy" />',
                    esc_url($icon_url),
                    esc_attr($period_data['title'])
                );
            }
            
            $html .= '</div>';
            
            // Optionally show text labels
            if ($show_text) {
                $html .= sprintf(
                    '<span class="period-text">%s</span>',
                    esc_html($period_data['title'])
                );
            }
        }
        
        $html .= '</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

// Register the shortcode
add_shortcode('cretan_history_breadcrumb', 'geotour_cretan_history_breadcrumb_shortcode');

/**
 * Auto-detect active period based on current page
 * Can be used to automatically set the active period
 */
function geotour_get_current_history_period() {
    global $post;
    
    if (!$post) {
        return 'cretan-history';
    }
    
    $slug = $post->post_name;
    
    // Map common slugs to periods
    $slug_map = array(
        'prehistoric-period' => 'prehistoric',
        'minoan-period' => 'minoan',
        'dark-ages-hystero-minoan-age' => 'dark-ages',
        'classical-period' => 'classical',
        'hellenistic-period' => 'hellenistic',
        'roman-period' => 'roman',
        'byzantine-period' => 'byzantine-i',
        'emirate-of-crete' => 'arab',
        'byzantine-period-2' => 'byzantine-ii',
        'venetian-period' => 'venetian',
        'ottoman-period' => 'ottoman',
        'modern-era-autonomy-unification' => 'modern'
    );
    
    return isset($slug_map[$slug]) ? $slug_map[$slug] : 'cretan-history';
}

/**
 * Enhanced shortcode that auto-detects current period
 * Usage: [cretan_history_breadcrumb_auto]
 */
function geotour_cretan_history_breadcrumb_auto_shortcode($atts) {
    $atts = shortcode_atts(array(
        'active' => geotour_get_current_history_period(),
        'show_text' => 'false'
    ), $atts, 'cretan_history_breadcrumb_auto');
    
    return geotour_cretan_history_breadcrumb_shortcode($atts);
}

add_shortcode('cretan_history_breadcrumb_auto', 'geotour_cretan_history_breadcrumb_auto_shortcode');
