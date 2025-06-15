# Geotour Theme Development Notes

## ACF `position` Field

The `position` Advanced Custom Field (ACF) is a required field for the `listing` custom post type. It stores geographical information in a JSON format.

**Important:** When retrieving coordinates for a specific listing, always use the values from the `markers` array, specifically the first marker.

-   **Latitude:** `markers[0]['lat']`
-   **Longitude:** `markers[0]['lng']`

The top-level `lat` and `lng` values within the `position` field data represent the map's center point in the WordPress admin editor, not necessarily the listing's exact location.

### Example JSON structure snippet:

```json
{
    "position": [
        "a:7:{s:3:"lat";s:15:"35.293340888894";s:3:"lng";s:15:"24.342527389526";s:4:"zoom";i:15;s:7:"markers";a:1:{i:0;a:6:{s:5:"label";s:0:"";s:13:"default_label";s:0:"";s:3:"lat";d:35.293340888894001;s:3:"lng";d:24.342527389526001;s:7:"geocode";a:0:{}s:4:"uuid";s:20:"marker_6846fa1c21099";}}s:7:"address";s:0:"";s:6:"layers";a:1:{i:0;s:20:"OpenStreetMap.Mapnik";}s:7:"version";s:5:"1.6.1";}"
    ]
}
```

(Note: The example shows a serialized PHP array stored as a string within the JSON. You'll need to `unserialize()` this string in PHP after `json_decode()` if you are accessing it directly from post meta, though ACF's `get_field()` typically handles this for you.)

---

**API Endpoints Implemented:**
- `/wp-json/panotours/v1/listings` - Listing search and filtering
- `/wp-json/panotours/v1/calendar` - Festive dates calendar
- `/wp-json/panotours/v1/nearest` - Nearest listings with distance calculation
- `/wp-json/geotour/v1/selector` - GeoJSON listings for map display

**SCSS Architecture:**
- Component-based styling system
- Variables and typography management
- Layout-specific styles for navigation, header, content
- Page-specific styles for single listings and homepage
- Contact form and gallery component styles

