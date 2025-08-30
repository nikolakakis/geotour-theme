<?php
/**
 * In-page sticky menu for single listing
 *
 * @package Geotour_Mobile_First
 */

// Section checks
$has_summary = true; // Always present
$has_details = true; // Always present
$has_main_content = true; // Always present
$has_nearby = true; // Always present

// Check for SeachCulture fields
$searchculture_fields = [
    'searchculture_ekt_chronology',
    'searchculture_ekt_type',
    'searchculture_dcterms_spatial',
    'searchculture_ekt_place',
    'searchculture_ekt_general_term',
];
$has_searchculture = false;
foreach ($searchculture_fields as $field) {
    if (get_field($field)) {
        $has_searchculture = true;
        break;
    }
}

// Menu items config
$menu_items = [
    [
        'id' => 'summary',
        'label' => __('Summary', 'geotour'),
        'show' => $has_summary,
        'class' => 'inpage-menu-summary',
    ],
    [
        'id' => 'details',
        'label' => __('Details', 'geotour'),
        'show' => $has_details,
    ],
    [
        'id' => 'main-content',
        'label' => __('Content', 'geotour'),
        'show' => $has_main_content,
    ],
    [
        'id' => 'viator-activities-container',
        'label' => __('Activities', 'geotour'),
        'show' => true,
    ],
    [
        'id' => 'nearby',
        'label' => __('Nearby', 'geotour'),
        'show' => $has_nearby,
    ],
];
?>
<nav class="listing-inpage-menu" id="listing-inpage-menu">
    <ul>
        <?php foreach ($menu_items as $item) : if (!$item['show']) continue; ?>
            <li>
                <a href="#<?php echo esc_attr($item['id']); ?>" class="inpage-menu-link<?php echo !empty($item['class']) ? ' ' . esc_attr($item['class']) : ''; ?>" data-section="<?php echo esc_attr($item['id']); ?>">
                    <span class="inpage-menu-label"><?php echo esc_html($item['label']); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
<script>
// JS for fixed sticky menu
(function() {
    const menu = document.getElementById('listing-inpage-menu');
    if (!menu) return;
    const menuOffset = menu.offsetTop;
    function checkFixed() {
        if (window.scrollY >= menuOffset) {
            if (!menu.classList.contains('is-fixed')) {
                menu.classList.add('is-fixed');
                console.log('[InpageMenu] Fixed activated');
            }
        } else {
            if (menu.classList.contains('is-fixed')) {
                menu.classList.remove('is-fixed');
                console.log('[InpageMenu] Fixed deactivated');
            }
        }
    }
    window.addEventListener('scroll', checkFixed, { passive: true });
    checkFixed();
})();
</script>
