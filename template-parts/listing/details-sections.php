<?php
/**
 * Template part for displaying listing details in 100vw section
 * Over the content 2 - Site access and other category-based information
 * 
 * @package Geotour_Mobile_First
 */

// Get the listing ID
$listing_id = get_the_ID();

// --- Category Specific Fields ---

// Archaeological Site Details
$archaeological_fields = [
    'price' => __('Price', 'geotour'),
    'prices_notes' => __('Price Notes', 'geotour'),
    'siteinfo_accessrestricted' => __('Access Restricted', 'geotour'),
    'siteinfo_prehistoric' => __('Prehistoric', 'geotour'),
    'siteinfo_minoan' => __('Minoan', 'geotour'),
    'siteinfo_darkages' => __('Dark Ages', 'geotour'),
    'siteinfo_classical' => __('Classical', 'geotour'),
    'siteinfo_hellenistic' => __('Hellenistic', 'geotour'),
    'siteinfo_roman' => __('Roman', 'geotour'),
    'siteinfo_arab' => __('Arab', 'geotour'),
    'siteinfo_byzantine' => __('Byzantine', 'geotour'),
    'siteinfo_venetian' => __('Venetian', 'geotour'),
    'siteinfo_ottoman' => __('Ottoman', 'geotour'),
    'siteinfo_modernera' => __('Modern Era', 'geotour'),
];
$archaeological_values = [];
foreach ($archaeological_fields as $key => $label) {
    $value = get_field($key, $listing_id);
    if ($value) {
        $archaeological_values[$key] = ['label' => $label, 'value' => $value];
    }
}

// Beach Information
$beach_fields = [
    'beachinfo_sand' => __('Sand', 'geotour'),
    'beachinfo_pebbles' => __('Pebbles', 'geotour'),
    'beachinfo_rocky_shore' => __('Rocky Shore', 'geotour'),
    'beachinfo_accessible' => __('Accessible', 'geotour'),
    'beachinfo_secluded' => __('Secluded', 'geotour'),
    'beachinfo_notbusy' => __('Not Busy', 'geotour'),
    'beachinfo_busy' => __('Busy', 'geotour'),
    'beachinfo_shallow_water' => __('Shallow Water', 'geotour'),
    'beachinfo_exposed' => __('Exposed to Winds', 'geotour'),
    'beachinfo_calm' => __('Calm', 'geotour'),
    'beachinfo_facilities_none' => __('No Facilities', 'geotour'),
    'beachinfo_facilities_basic' => __('Basic Facilities', 'geotour'),
    'beachinfo_facilities_organized' => __('Organized', 'geotour'),
];
$beach_values = [];
foreach ($beach_fields as $key => $label) {
    $value = get_field($key, $listing_id);
    if ($value) {
        $beach_values[$key] = ['label' => $label, 'value' => $value];
    }
}

// Fortification Details
$fortification_fields = [
    'fortificationsiteinfo_roman' => __('Roman', 'geotour'),
    'fortificationsiteinfo_arab' => __('Arab', 'geotour'),
    'fortificationsiteinfo_venetian' => __('Venetian', 'geotour'),
    'fortificationsiteinfo_byzantine' => __('Byzantine', 'geotour'),
    'fortificationsiteinfo_ottoman' => __('Ottoman', 'geotour'),
];
$fortification_values = [];
foreach ($fortification_fields as $key => $label) {
    $value = get_field($key, $listing_id);
    if ($value) {
        $fortification_values[$key] = ['label' => $label, 'value' => $value];
    }
}

// Religious Site Details
$religious_fields = [
    'religioninfo_settlement' => __('Settlement', 'geotour'),
    'religioninfo_venetian' => __('Venetian', 'geotour'),
    'religioninfo_byzantine' => __('Byzantine', 'geotour'),
    'religioninfo_ottoman' => __('Ottoman', 'geotour'),
    'religioninfo_early_christian' => __('Early Christian', 'geotour'),
    'religioninfo_contemporary' => __('Contemporary', 'geotour'),
    'religioninfo_opentopublic' => __('Open to Public', 'geotour'),
    'religioninfo_monastery' => __('Monastery', 'geotour'),
    'religioninfo_populated' => __('Populated', 'geotour'),
];
$religious_values = [];
foreach ($religious_fields as $key => $label) {
    $value = get_field($key, $listing_id);
    if ($value) {
        $religious_values[$key] = ['label' => $label, 'value' => $value];
    }
}

// Get site access information
$siteinfo_pavedroad = get_field('siteinfo_pavedroad', $listing_id);
$siteinfo_earthroad = get_field('siteinfo_earthroad', $listing_id);
$siteinfo_fourwheel = get_field('siteinfo_fourwheel', $listing_id);
$siteinfo_hike = get_field('siteinfo_hike', $listing_id);
$siteinfo_notes = get_field('siteinfo_notes', $listing_id);

// Get contact information
$contact_title = get_field('contact_title', $listing_id);
$contact_address = get_field('contact_address', $listing_id);
$contact_phone = get_field('contact_phone', $listing_id);
$contact_mobile = get_field('contact_mobile', $listing_id);
$contact_whatsapp = get_field('contact_whatsapp', $listing_id);
$contact_website = get_field('contact_website', $listing_id);
$contact_email = get_field('contact_email', $listing_id);

// Get social media fields
$social_facebook = get_field('social_facebook', $listing_id);
$social_twitter = get_field('social_twitter', $listing_id);
$social_instagram = get_field('social_instagram', $listing_id);
$social_tiktok = get_field('social_tiktok', $listing_id);
$social_pinterest = get_field('social_pinterest', $listing_id);
$social_tripadvisor = get_field('social_tripadvisor', $listing_id);
$social_youtube = get_field('social_youtube', $listing_id);
$social_linkedin = get_field('social_linkedin', $listing_id);

// Check if we have any access information to display
$has_access_info = $siteinfo_pavedroad || $siteinfo_earthroad || $siteinfo_fourwheel || $siteinfo_hike || !empty($siteinfo_notes);

// Check if we have contact information to display
$has_contact_info = !empty($contact_title) || !empty($contact_address) || !empty($contact_phone) || !empty($contact_mobile) || !empty($contact_whatsapp) || !empty($contact_website) || !empty($contact_email) || !empty($social_facebook) || !empty($social_twitter) || !empty($social_instagram) || !empty($social_tiktok) || !empty($social_pinterest) || !empty($social_tripadvisor) || !empty($social_youtube) || !empty($social_linkedin);

// Determine if we have any additional info to display
$has_additional_info = $has_access_info || $has_contact_info || !empty($archaeological_values) || !empty($beach_values) || !empty($fortification_values) || !empty($religious_values);

// Set CSS class based on content availability
$section_class = $has_additional_info ? 'has-additional-info' : 'weather-only';
?>

<section class="listing-details-full-section <?php echo esc_attr($section_class); ?>">
    <div class="listing-details-container">
        
        <?php if ($has_additional_info) : ?>
        <!-- Combined Column: Site Access & Additional Info -->
        <div class="details-column details-combined">                <div class="column-content">
                      <!-- Category and Region Information -->
                    <div class="listing-taxonomy-section">
                        <?php
                        // Get regions with hierarchical structure FIRST
                        $regions = get_the_terms(get_the_ID(), 'listing-region');
                        if (!empty($regions) && !is_wp_error($regions)) {
                            // Build hierarchical breadcrumb for regions
                            $region_hierarchy = [];
                            foreach ($regions as $region) {
                                $current_region = $region;
                                $hierarchy_path = [];
                                
                                while ($current_region && !is_wp_error($current_region)) {
                                    array_unshift($hierarchy_path, $current_region);
                                    if ($current_region->parent == 0) {
                                        break;
                                    }
                                    $current_region = get_term($current_region->parent, 'listing-region');
                                }
                                
                                if (!empty($hierarchy_path)) {
                                    $region_hierarchy[] = $hierarchy_path;
                                }
                            }
                            
                            // Display the longest hierarchy
                            if (!empty($region_hierarchy)) {
                                usort($region_hierarchy, function($a, $b) {
                                    return count($b) - count($a);
                                });
                                
                                $main_hierarchy = $region_hierarchy[0]; ?>
                                <div class="taxonomy-region">
                                    <?php 
                                    foreach ($main_hierarchy as $index => $region_item) {
                                        if ($index > 0) echo ' → ';
                                        ?>
                                        <a href="/listing/?listing-region=<?php echo esc_attr($region_item->slug); ?>">
                                            <?php echo esc_html($region_item->name); ?>
                                        </a>
                                    <?php } ?>
                                </div>
                            <?php }
                        }
                        
                        // Get categories SECOND
                        $categories = get_the_terms(get_the_ID(), 'listing-category');
                        if (!empty($categories) && !is_wp_error($categories)) { ?>
                            <div class="taxonomy-categories">
                                <?php 
                                $category_links = [];
                                foreach ($categories as $category) {
                                    $category_links[] = '<a href="/listing/?listing-category=' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</a>';
                                }
                                echo implode(', ', $category_links);
                                ?>
                            </div>
                        <?php } ?>
                    </div>
                    
                    <!-- Category-specific characteristics -->
                    <div class="category-characteristics-section">
                        <?php if (!empty($archaeological_values)) : ?>
                            <div class="characteristic-group archaeological-details">
                                <h4 class="section-heading"><?php _e('Archaeological Information', 'geotour'); ?></h4>
                                <ul class="characteristics-list">
                                    <?php foreach ($archaeological_values as $key => $field) : ?>
                                        <li>
                                            <?php if ($key === 'price' || $key === 'prices_notes') : ?>
                                                <span class="char-label"><?php echo esc_html($field['label']); ?>:</span>
                                                <span class="char-value"><?php echo esc_html($field['value']); ?></span>
                                            <?php else: ?>
                                                <span class="char-label"><?php echo esc_html($field['label']); ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($beach_values)) : ?>
                            <div class="characteristic-group beach-details">
                                <h4 class="section-heading"><?php _e('Beach Information', 'geotour'); ?></h4>
                                <ul class="characteristics-list">
                                    <?php foreach ($beach_values as $field) : ?>
                                        <li>
                                            <span class="char-label"><?php echo esc_html($field['label']); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($fortification_values)) : ?>
                            <div class="characteristic-group fortification-details">
                                <h4 class="section-heading"><?php _e('Fortification Information', 'geotour'); ?></h4>
                                <ul class="characteristics-list">
                                    <?php foreach ($fortification_values as $field) : ?>
                                        <li>
                                            <span class="char-label"><?php echo esc_html($field['label']); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($religious_values)) : ?>
                            <div class="characteristic-group religious-details">
                                <h4 class="section-heading"><?php _e('Religious Site Information', 'geotour'); ?></h4>
                                <ul class="characteristics-list">
                                    <?php foreach ($religious_values as $field) : ?>
                                        <li>
                                            <span class="char-label"><?php echo esc_html($field['label']); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($has_access_info) : ?>
                <div class="site-access-section">
                    <h4 class="section-heading"><?php _e('Site Access', 'geotour'); ?></h4>
                    
                    <div class="site-access-info">
                        <?php
                        $access_methods = array();
                        
                        if ($siteinfo_pavedroad == '1') {
                            $access_methods[] = __('Paved road', 'geotour');
                        }
                        if ($siteinfo_earthroad == '1') {
                            $access_methods[] = __('Earth road', 'geotour');
                        }
                        if ($siteinfo_fourwheel == '1') {
                            $access_methods[] = __('4WD vehicle required', 'geotour');
                        }
                        if ($siteinfo_hike == '1') {
                            $access_methods[] = __('Hiking required', 'geotour');
                        }
                        
                        if (!empty($access_methods)) :
                        ?>
                            <div class="access-methods">
                                <ul>
                                    <?php foreach ($access_methods as $method) : ?>
                                        <li><?php echo esc_html($method); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($siteinfo_notes)) : ?>
                            <div class="access-notes">
                                <?php echo wp_kses_post($siteinfo_notes); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($has_contact_info) : ?>
                <!-- Contact Details Section -->
                <div class="contact-details-section">
                    <h4 class="section-heading"><?php _e('Contact Details', 'geotour'); ?></h4>
                    
                    <!-- Contact Name -->
                    <?php if (!empty($contact_title)) : ?>
                    <div class="contact-name">
                        <strong><?php echo esc_html($contact_title); ?></strong>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Contact Address -->
                    <?php if (!empty($contact_address)) : ?>
                        <div class="contact-address">
                            <span class="contact-label"><?php _e('Address:', 'geotour'); ?></span>
                            <?php echo esc_html($contact_address); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Phone Numbers -->
                    <?php if (!empty($contact_phone) || !empty($contact_mobile)) : ?>
                        <div class="contact-phones">
                            <span class="contact-label"><?php _e('Phone:', 'geotour'); ?></span>
                            <?php if (!empty($contact_phone)) : ?>
                                <span class="phone-number"><?php echo esc_html($contact_phone); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($contact_mobile)) : ?>
                                <span class="mobile-number"><?php echo esc_html($contact_mobile); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- WhatsApp -->
                    <?php if (!empty($contact_whatsapp)) : ?>
                        <div class="contact-whatsapp">
                            <a href="https://wa.me/<?php echo esc_attr(str_replace([' ', '+', '-'], '', $contact_whatsapp)); ?>" target="_blank" rel="noopener">
                                <svg class="whatsapp-icon" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.465 3.63"/>
                                </svg>
                                <?php echo esc_html($contact_whatsapp); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Social and Contact Icons -->
                    <div class="contact-social-icons">
                        <?php if (!empty($contact_website)) : ?>
                            <a href="<?php echo esc_url($contact_website); ?>" target="_blank" rel="noopener" title="<?php _e('Website', 'geotour'); ?>">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($contact_email)) : ?>
                            <a href="mailto:<?php echo esc_attr($contact_email); ?>" title="<?php _e('Email', 'geotour'); ?>">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 12.713l-11.985-9.713h23.97l-11.985 9.713zm0 2.574l-12-9.725v15.438h24v-15.438l-12 9.725z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <!-- Social Media Icons -->
                        <?php if (!empty($social_facebook)) : ?>
                            <a href="<?php echo esc_url($social_facebook); ?>" target="_blank" rel="noopener" title="Facebook">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($social_twitter)) : ?>
                            <a href="<?php echo esc_url($social_twitter); ?>" target="_blank" rel="noopener" title="X (Twitter)">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($social_instagram)) : ?>
                            <a href="<?php echo esc_url($social_instagram); ?>" target="_blank" rel="noopener" title="Instagram">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($social_tiktok)) : ?>
                            <a href="<?php echo esc_url($social_tiktok); ?>" target="_blank" rel="noopener" title="TikTok">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($social_pinterest)) : ?>
                            <a href="<?php echo esc_url($social_pinterest); ?>" target="_blank" rel="noopener" title="Pinterest">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.097.118.112.221.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24c6.624 0 11.99-5.367 11.99-12C24.007 5.367 18.641.001.012.001z.001z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($social_tripadvisor)) : ?>
                            <a href="<?php echo esc_url($social_tripadvisor); ?>" target="_blank" rel="noopener" title="TripAdvisor">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12.006 4.295c-2.67 0-5.338.784-7.645 2.353H1.294l1.265 1.265c-.477.895-.73 1.91-.73 2.954 0 3.341 2.654 6.006 5.988 6.024 2.009.012 3.924-.789 5.189-2.115 1.265 1.326 3.18 2.127 5.189 2.115 3.334-.018 5.988-2.683 5.988-6.024 0-1.044-.253-2.059-.73-2.954L24.718 6.648h-3.067C19.344 5.079 16.676 4.295 12.006 4.295zM7.818 7.871c1.326 0 2.399 1.085 2.399 2.4 0 1.326-1.073 2.4-2.399 2.4-1.326 0-2.399-1.074-2.399-2.4 0-1.315 1.073-2.4 2.399-2.4zm8.376 0c1.326 0 2.399 1.085 2.399 2.4 0 1.326-1.073 2.4-2.399 2.4-1.326 0-2.399-1.074-2.399-2.4 0-1.315 1.073-2.4 2.399-2.4zM7.818 8.583c-.979 0-1.776.797-1.776 1.776 0 .979.797 1.776 1.776 1.776.979 0 1.776-.797 1.776-1.776 0-.979-.797-1.776-1.776-1.776zm8.376 0c-.979 0-1.776.797-1.776 1.776 0 .979.797 1.776 1.776 1.776.979 0 1.776-.797 1.776-1.776 0-.979-.797-1.776-1.776-1.776z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($social_youtube)) : ?>
                            <a href="<?php echo esc_url($social_youtube); ?>" target="_blank" rel="noopener" title="YouTube">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($social_linkedin)) : ?>
                            <a href="<?php echo esc_url($social_linkedin); ?>" target="_blank" rel="noopener" title="LinkedIn">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Weather Forecast Column -->
        <?php if (!wp_is_mobile()) : ?>
        <div class="details-column details-weather">
            <div class="column-content">
                <h4 class="section-heading"><?php _e('Weather Forecast', 'geotour'); ?></h4>
                <div id="openmeteo"></div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</section>