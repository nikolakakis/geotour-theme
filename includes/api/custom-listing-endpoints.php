<?php
add_action('rest_api_init', 'customize_rest_additional'); // Renamed to avoid conflict if other customize_rest exists

function customize_rest_additional() {
    register_rest_route("panotours/v1","listings",array(
        'methods' => WP_REST_SERVER::READABLE,
        'callback' => 'listingsSearch_v2' // Renamed callback
    ));
   
    register_rest_route("panotours/v1","calendar",array(
        'methods' => WP_REST_SERVER::READABLE,
        'callback' => 'festivedates_Calendar_v2' // Renamed callback
    ));
    register_rest_route("geotour/v1","selector",array(
        'methods' => WP_REST_SERVER::READABLE,
        'callback' => 'geotour_listings_for_selector_v2' // Renamed callback
    ));
}

function listingsSearch_v2($data) {        
    $thelang = isset($data['language']) ? sanitize_text_field($data['language']) : "";    
    $args=array(
        'post_type' => 'listing',
        'post_status'    => 'publish',	
        'posts_per_page' => 999,
        'lang' => $thelang,
        's' => sanitize_text_field($data['search'])
    );
    $reorder=0;
    if (isset($data['theid'])) {
        $theid = sanitize_text_field($data['theid']);
        $reorder=1;
        $idarr = ['p' => $data['theid']];
        $args = array_merge($args, $idarr);
    }
    if (isset($data['vt'])) {
        $vt_value = sanitize_text_field($data['vt']);
        // Assuming 'vtour_link' is the new ACF meta key
        $args['meta_query'] = array(
            array(
                'key' => 'vtour_link', // Geotour Metabox to ACF field
                'value' => 'https://tour.geotour.gr/#' . $vt_value . ',', 
                'compare' => 'LIKE', 
            ),
            array(
                'key' => 'vtour_link', // Geotour Metabox to ACF field
                'value' => 'https://tour.geotour.gr/#' . $vt_value . '%', 
                'compare' => 'NOT LIKE' 
            )
        );
    }
    if (isset($data['categories'])) {
        $categories = explode(',', sanitize_text_field($data['categories'])); 
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'listing-category',
                'field' => 'slug',
                'terms' => $categories,
            )
        );
    } 
    
    $iid=array();   
    $listings = new WP_Query($args);   
    $listingsapiarray = array();

    while ($listings->have_posts()) {
        $listings->the_post();
        $theid = get_the_id();
        
        $theregions=get_the_terms($theid,'listing-region');
        $sorted_terms = array();
        $thetags=get_the_terms($theid,'listing-tag');

        if (!$thetags) {
            $thetags=array();
        }
        if ($theregions) {
            if ($reorder==1) {
                list_terms_by_parent_v2(0,$theregions,$sorted_terms); // Renamed helper
            } else {
                $sorted_terms = $theregions;
            }
        }
        if (!$sorted_terms) {
            $sorted_terms=array();
        }
        
        // Get position using ACF
        $position_acf = get_field('position', $theid); 
        $position_string = null;

        // Use lat/lng from the first marker
        if ($position_acf && isset($position_acf['markers']) && is_array($position_acf['markers']) && !empty($position_acf['markers'])) {
            $first_marker = $position_acf['markers'][0];
            if (isset($first_marker['lat']) && isset($first_marker['lng'])) {
                $position_string = $first_marker['lat'] . ',' . $first_marker['lng'];
            }
        }
        
        // For 'listingfields', we keep get_post_meta to include all, as per transitional phase.
        // Specific ACF fields can be added explicitly if needed.
        $all_custom_fields = get_post_meta($theid);

        $thumb_id = get_post_thumbnail_id( $theid );
        $thumb_url = wp_get_attachment_image_src( $thumb_id, 'thumbnail' ); 
        if ($thumb_url == false) {
             $thumb_url = array("https://www.geotour.gr/wp-content/uploads/2024/04/no-image-bello.jpg"); // Ensure it's an array if src returns array
        }

        array_push($listingsapiarray, array(
            // 'acf_position_debug' => $position_acf, // Removed for production
            'listingID' => $theid,            
            'title' => get_the_title(),            
            'url' => get_the_permalink(),   
            'position' => $position_string, // Using ACF processed position from markers[0]        
            'listingsThumbUrl' => $thumb_url ? $thumb_url[0] : "https://www.geotour.gr/wp-content/uploads/2024/04/no-image-bello.jpg", // Access URL from array
            'listingTag' => $thetags,
            'listingCategory' => get_the_terms($theid,'listing-category'),            
            'listingRegion' => $sorted_terms,            
            'listingfields' => $all_custom_fields, // Contains all post meta, including old and ACF-managed ones if keys are same
        ));
    }

    wp_reset_postdata();
    return $listingsapiarray;
}

function list_terms_by_parent_v2($parent_id = 0, &$terms, &$ordered_terms){ // Renamed helper
    $root_parent = $parent_id;
    if (count($terms)> 0) {
        foreach($terms as $index => $term){
            if($term->parent == (int) $parent_id){
                array_push($ordered_terms,$term);
                $root_parent = $term->term_id;
                unset($terms[$index]);
            }
        }
    }
    if(!empty($terms)) list_terms_by_parent_v2($root_parent, $terms, $ordered_terms); // Renamed helper
}
  
function festivedates_Calendar_v2($data) { // Renamed callback
    if ( isset($data['language'])) {
        $thelang=sanitize_text_field($data['language']);
        if ($thelang=="en") {
            setlocale(LC_ALL, 'en_US');
        }
        if ($thelang=="el") {
            setlocale(LC_ALL, 'el_GR');
        }
    } else {
        $thelang = "";
    }   

    $today=date("Y-m-d");
    
    // Assuming 'festive_date1' is the new ACF meta key
    $meta_query_args = array(
           array(
               'key' => 'festive_date1', // Geotour Metabox to ACF field
               'value' => '', 
               'compare' => '!='
           )
     );

    if ($data['theid']) {
        $args=array(
            'post_type' => 'listing',
            'post_status'    => 'publish',	
            'posts_per_page' => 999,
            'lang' => $thelang,
            'meta_query' => $meta_query_args,
            's' => sanitize_text_field($data['search']),
            'p' => $data['theid']
        );        
    } else {
        $args=array(
            'post_type' => 'listing',
            'post_status'    => 'publish',	
            'posts_per_page' => 999,
            'lang' => $thelang,
            'meta_query' => $meta_query_args,
            's' => sanitize_text_field($data['search'])
        );
    }

    $listings = new WP_Query($args);

    $calendaritems = array();
    $ht['left']='prev,next today';
    $ht['center']='title';
    $ht['right']='multiMonthYear,dayGridMonth,listMonth'; 
    $calendaritems['headerToolbar']=$ht;
    $calendaritems['initialView']='listMonth';
    $calendaritems['initialDate']=$today;
    $calendaritems['editable']=true;
    $calendaritems['selectable']=true;
    $calendaritems['selectMirror']=true;
    $calendaritems['nowIndicator']=true;
    $calendaritems['contentHeight']='auto';
    
    while ($listings->have_posts()) {
        $listings->the_post();
        $theid = get_the_id();
        $url=get_the_permalink($theid);
        $events = array();       
        
        // Use get_field for ACF fields
        $reason1 = get_field('festive_reason1', $theid); // Geotour Metabox to ACF field
        $startdate1 = get_field('festive_date1', $theid); // Geotour Metabox to ACF field
        $enddate1 = get_field('festive_date1_end', $theid); // Geotour Metabox to ACF field

        if ($startdate1) { // Process only if primary date exists
            $events['title']=get_the_title() . ($reason1 ? " (" . strip_tags($reason1) .")" : "");        
            $events['url']=$url;   
            $events['start']= $startdate1; // ACF date field typically returns YYYY-MM-DD
            $events['end'] = $enddate1 ? $enddate1 : null;    
            $calendaritems['events'][]=$events;
        }

        // Festive date 2, run only if exist
        $reason2 = get_field('festive_reason2', $theid); // Geotour Metabox to ACF field
        $startdate2 = get_field('festive_date2', $theid); // Geotour Metabox to ACF field
        $enddate2 = get_field('festive_date2_end', $theid); // Geotour Metabox to ACF field

        if ($startdate2) { // Process only if primary date exists for event 2
            $events = array(); // Reset for second event      
            $events['title']=get_the_title() . ($reason2 ? " (" . strip_tags($reason2) .")" : "");              
            $events['url']=$url;   
            $events['start']= $startdate2;  
            $events['end'] = $enddate2 ? $enddate2 : null;    
            $calendaritems['events'][]=$events;
        }
    }
    wp_reset_postdata();
    return $calendaritems;
}

// listingsTexts function seems to be unused or incomplete based on original commented out code.
// If it needs to be adapted, its purpose and desired output fields should be clarified.
// For now, adapting the existing structure.
function listingsTexts_v2($data) { // Renamed callback
    if ( isset($data['language'])) {
        $thelang=sanitize_text_field($data['language']);
    } else {
        $thelang = "";
    }
    
    if ($data['theid']) {
        $args=array(
            'post_type' => 'listing',
            'post_status'    => 'publish',	
            'posts_per_page' => 9999, // Consider if this is necessary or should be limited
            'lang' => $thelang,
            's' => sanitize_text_field($data['search']),
            'p' => $data['theid']
        );
    } else {
        $args=array(
            'post_type' => 'listing',
            'post_status'    => 'publish',	
            'posts_per_page' => 999,
            'lang' => $thelang,
            's' => sanitize_text_field($data['search'])
        );
    }
    
    $listings = new WP_Query($args);
    $listingsapiarray = array();

    while ($listings->have_posts()) {
        $listings->the_post();
        // $theid = get_the_id(); // $theid not used in the current array_push
        
        // Corrected duplicate 'title' key and used 'content' for get_the_content()
        array_push($listingsapiarray, array(          
            'title' => get_the_title(),            
            'content' => get_the_content() // Changed key from 'title' to 'content'          
        ));
        /*
        // Original commented out block, would need similar ACF adoption if used:
        array_push($listingsapiarray, array(
            'listingID' => $theid,            
            'title' => get_the_title(),            
            'url' => get_the_permalink(),   
            'position' => $position, // This would need to be fetched using get_field('position', $theid)         
            'listingsThumbUrl' => get_the_post_thumbnail_url(),
            'listingTag' => $thetags, // These would need to be fetched
            'listingCategory' => get_the_terms($theid,'listing-category'),            
            'listingRegion' => $sorted_terms, // These would need to be fetched
            'listingfields' => $customfields // This would need to be get_post_meta or specific get_field calls
        ));
        */
    }
    wp_reset_postdata();
    return $listingsapiarray;
}

function geotour_listings_for_selector_v2($data) { // Renamed callback
    if (isset($data['lang'])) {
        $thelang = sanitize_text_field($data['lang']);
    } else {
        $thelang = "en";
    }

    if (isset($data['period'])) {
        $selected_period = sanitize_text_field($data['period']);
    } else {
        $selected_period = ''; 
    }

    $tax_query_conditions = array( // Changed from $meta to $tax_query_conditions for clarity
        array(
            'taxonomy' => 'listing-category',
            'field'    => 'slug',
            'terms'    => ["museum-en", "pois"] // Ensure these slugs are correct for your setup
        )
    );

    // Assuming getperiod() is defined elsewhere and returns a meta_query array
    // $period_filter = getperiod($selected_period); // This function needs to be available
    $period_filter = function_exists('getperiod') ? getperiod($selected_period) : array();


    $query_args = array( // Renamed from $args to $query_args for clarity
        'post_type'      => 'listing',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'lang'           => $thelang,
        's'              => isset($data['search']) ? sanitize_text_field($data['search']) : '',
        'tax_query'      => $tax_query_conditions, // Initialize with base tax query
    );
    
    if (isset($data['theid']) && !empty($data['theid'])) { // Added check for empty
        $query_args['p'] = intval($data['theid']);
    }

    if (!empty($period_filter)) {
        if (isset($query_args['meta_query'])) {
            $query_args['meta_query']['relation'] = 'AND'; // Ensure relation if other meta queries exist
            $query_args['meta_query'][] = $period_filter;
        } else {
            $query_args['meta_query'] = $period_filter;
        }
    }

    if (isset($data['exclude_ids']) && !empty($data['exclude_ids'])) {
        $exclude_ids = explode(',', sanitize_text_field($data['exclude_ids']));
        $exclude_ids = array_map('intval', $exclude_ids); 
        $query_args['post__not_in'] = $exclude_ids; 
    }

    $origin_lat = null;
    $origin_lon = null;

    if (isset($data['lat']) && isset($data['lon'])) {
        $origin_lat = floatval($data['lat']);
        $origin_lon = floatval($data['lon']);
    }

    $listings_query = new WP_Query($query_args); // Renamed from $listings to $listings_query
    $features = array();
    $geoapi_response   = array(); // Renamed from $geoapi to $geoapi_response
    $geoapi_response['type'] = "FeatureCollection";

    while ($listings_query->have_posts()) {
        $listings_query->the_post();
        $theid = get_the_id();

        $position_acf = get_field('position', $theid); // ACF field
        $lat = null;
        $lon = null;

        // Use lat/lng from the first marker
        if ($position_acf && isset($position_acf['markers']) && is_array($position_acf['markers']) && !empty($position_acf['markers'])) {
            $first_marker = $position_acf['markers'][0];
            if (isset($first_marker['lat']) && isset($first_marker['lng'])) {
                $lat = floatval($first_marker['lat']);
                $lon = floatval($first_marker['lng']);
            }
        }

        if ($lat !== null && $lon !== null) { // Proceed only if lat and lon were successfully extracted
            $geom      = array();
            $geom["type"] = "Point";
            $geom["coordinates"] = array($lon, $lat);

            $properties = array();
            $properties['listingID'] = $theid;
            $properties['title'] = get_the_title();
            $properties['summary'] = get_the_excerpt();
            $properties['url'] = get_the_permalink($theid);
            
            $thecats = get_the_terms($theid, 'listing-category');
            if ( is_array( $thecats ) && ! is_wp_error( $thecats ) ) {
                $properties['category1'] = isset($thecats[0]) ? $thecats[0]->name : null;
                $properties['category2'] = isset($thecats[1]) ? $thecats[1]->name : null;
            } else {
                $properties['category1'] = null;
                $properties['category2'] = null;
            }
            $properties['categories'] = $thecats; 
            
            // Population fields using get_field
            $properties['1583'] = get_field('population_1583', $theid); // Geotour Metabox to ACF field
            $properties['1881'] = get_field('population_1881', $theid); // Geotour Metabox to ACF field
            $properties['1900'] = get_field('population_1900', $theid); // Geotour Metabox to ACF field
            $properties['1928'] = get_field('population_1928', $theid); // Geotour Metabox to ACF field
            $properties['1940'] = get_field('population_1940', $theid); // Geotour Metabox to ACF field
            $properties['1951'] = get_field('population_1951', $theid); // Geotour Metabox to ACF field
            $properties['1961'] = get_field('population_1961', $theid); // Geotour Metabox to ACF field
            $properties['1971'] = get_field('population_1971', $theid); // Geotour Metabox to ACF field
            $properties['1981'] = get_field('population_1981', $theid); // Geotour Metabox to ACF field
            $properties['1991'] = get_field('population_1991', $theid); // Geotour Metabox to ACF field
            $properties['2001'] = get_field('population_2001', $theid); // Geotour Metabox to ACF field
            $properties['2011'] = get_field('population_2011', $theid); // Geotour Metabox to ACF field
            $properties['2021'] = get_field('population_2021', $theid); // Geotour Metabox to ACF field
            
            $properties['image_path'] = get_the_post_thumbnail_url($theid);
            $properties['geometrystring'] = $lat . ',' . $lon; // Reconstruct from ACF markers[0] lat/lng

            $icon_url = null;
            if ($thecats && !is_wp_error($thecats)) {
                foreach ($thecats as $category) {
                    // Assuming 'icon' is the ACF field name for term meta and returns a URL
                    $temp_icon_url = get_field('icon', 'listing-category_' . $category->term_id); 
                    if ($temp_icon_url) {
                        $icon_url = $temp_icon_url;
                        break; 
                    }
                }
            }
            $properties['icon_url'] = $icon_url;

            if ($origin_lat !== null && $origin_lon !== null && $lat !== null && $lon !== null) {
                 // Ensure haversine_distance2 is available
                if (function_exists('haversine_distance2')) {
                    $distance = haversine_distance2($origin_lat, $origin_lon, $lat, $lon);
                    $properties['distance'] = round($distance, 2); 
                } else {
                    $properties['distance'] = null; // or some error indicator
                }
            }

            $features[] = array( 
                'type'       => 'Feature',
                'geometry'   => $geom,
                'properties' => $properties
            );
        }
    }

    if ($origin_lat !== null && $origin_lon !== null) {
        usort($features, function ($a, $b) {
            // Handle cases where distance might not be set
            $dist_a = isset($a['properties']['distance']) ? $a['properties']['distance'] : PHP_INT_MAX;
            $dist_b = isset($b['properties']['distance']) ? $b['properties']['distance'] : PHP_INT_MAX;
            return $dist_a <=> $dist_b;
        });
    }

    $geoapi_response['features'] = $features;
    wp_reset_postdata();
    return $geoapi_response;
}

// Note: The functions haversine_distance2() and getperiod() are assumed to be defined elsewhere in your theme or a plugin.
// If they are not, you will need to define them or include the files where they are defined.
?>
