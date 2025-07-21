# Regular Maps

## Single Listing Maps
- **File**: `template-parts/listing/map-single.php`
- **Purpose**: Show one listing location
- **Features**: Static display, external controls

### Template Structure
```php
<div id="listing-map" 
     data-lat="<?php echo esc_attr($marker_lat); ?>"
     data-lng="<?php echo esc_attr($marker_lng); ?>"
     data-zoom="<?php echo esc_attr($zoom_level); ?>"
     data-static="true">
</div>
```

### External Controls
```php
// Google Maps
<a href="<?php echo esc_url($google_maps_base_url . $marker_lat . ',' . $marker_lng); ?>">

// Route Planner  
<a href="<?php echo esc_url($route_planner_base_url . $post_id); ?>">

// 3D Map
<a href="<?php echo esc_url(add_query_arg([
    'lon' => $marker_lng, 
    'lat' => $marker_lat
], $cesium_3d_map_base_url)); ?>">
```

### ACF Integration
```php
// 3D Video Path
$video_path_value = get_field('video_path', $post_id);
if (!empty($video_path_value)) {
    // Show video control
}

// 3D Tour
$tour_3d_value = get_field('3d_tour', $post_id);
if (!empty($tour_3d_value)) {
    // Show tour control
}
```

## Archive Listing Maps
- **File**: `template-parts/listing/map-archive.php`
- **Purpose**: Multiple listings on category pages
- **Features**: Multi-marker, clustering, bounds fitting

### Implementation
```javascript
const archiveMapData = {
    coordinates: [35.2401, 24.8093],
    zoomLevel: 9,
    listings: [
        {
            id: 1,
            coordinates: [35.2401, 24.8093],
            title: "Location 1",
            permalink: "/listing/location-1"
        }
    ]
};

const map = initializeGeotourMap('archive-map', archiveMapData);
```

## Custom Maps

### Basic Usage
```javascript
const mapData = {
    coordinates: [35.2401, 24.8093],
    zoomLevel: 15,
    popupText: '<h5>Custom Location</h5>'
};

initializeGeotourMap('custom-map', mapData);
```

### WordPress Integration
```php
<?php 
$coordinates = geotour_get_listing_coordinates($post_id);
if ($coordinates): ?>
    <div id="post-map" class="geotour-map-container"></div>
    <script>
        const mapData = {
            coordinates: [<?php echo $coordinates['lat']; ?>, <?php echo $coordinates['lng']; ?>],
            zoomLevel: 14
        };
        initializeGeotourMap('post-map', mapData);
    </script>
<?php endif; ?>
```

## Helper Functions
```php
// Get coordinates from ACF
function geotour_get_listing_coordinates($post_id) {
    $position_data = get_field('position', $post_id);
    if (!$position_data || !isset($position_data['markers'][0])) {
        return false;
    }
    $marker = $position_data['markers'][0];
    return [
        'lat' => floatval($marker['lat']),
        'lng' => floatval($marker['lng'])
    ];
}
```