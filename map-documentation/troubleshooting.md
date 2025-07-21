# Troubleshooting

## Big Map Issues

### Map Not Loading
- Check if `geotour-crete-maps` plugin is active
- Verify page template is "Listing Map"
- Check browser console for Leaflet errors
- Test REST API: `/wp-json/geotour/v3/spatial-info`

### Sidebar Not Responsive
```javascript
// Check responsive state
console.log('Is Mobile:', window.innerWidth <= 1024);
console.log('Sidebar classes:', document.querySelector('.map-sidebar').classList);
```

### Map Not Updating on Pan/Zoom
- Verify `onMapMoveEnd` event listener is attached
- Check debounce timeout (should be 1000ms)
- Ensure API calls aren't blocked by loading state

### Search Not Working
- Check search input exists with ID `map-search-input`
- Verify enter key event listener is attached
- Check URL parameter handling

## Regular Map Issues

### Maps Not Displaying
```css
/* Ensure explicit height */
.geotour-map-container {
    height: 400px;
    width: 100%;
}
```

### Markers Not Showing
- Verify coordinates are valid numbers
- Check format is [lat, lng] not [lng, lat]
- Ensure coordinates are within valid range

## Performance Issues

### Slow Loading
- Check API response time
- Monitor data volume returned
- Consider increasing debounce timeout

### Memory Issues
```javascript
// Proper cleanup
this.currentMarkers.forEach(marker => {
    map.removeLayer(marker);
});
this.currentMarkers = [];
```

## WordPress Issues

### Template Not Loading
- Assign "Listing Map" template to page in admin
- Check `page-listing.php` exists in theme root

### URL Routing Issues
```php
// Flush rewrite rules once after changes
flush_rewrite_rules();
```

### API Authentication
- Check nonce is generated correctly
- Verify WordPress REST API is enabled

## Emergency Reset
```javascript
// Add to browser console for emergency use
function resetBigMap() {
    window.location.reload();
}
```