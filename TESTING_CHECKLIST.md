# Trail Admin Panel - Testing Checklist

## GPX Upload Testing

### Create Trail (with GPX)
- [ ] Upload valid GPX file (< 1MB)
- [ ] Upload large GPX file (> 5MB) - should show warning
- [ ] Upload invalid file (not GPX) - should reject
- [ ] Click "Apply GPX Values" - form fields should populate
- [ ] Verify distance, elevation, time are correct
- [ ] Submit form - trail should be created with GPX data
- [ ] Check `data_source` is set to 'gpx'
- [ ] Verify GPX file is saved to storage

### Edit Trail (GPX Re-upload)
- [ ] Edit existing trail without GPX
- [ ] Upload new GPX file
- [ ] Comparison modal should appear
- [ ] Click "Keep Current" - values should remain unchanged
- [ ] Upload GPX again, click "Use New GPX" - values should update
- [ ] Submit form - check `gpx_action` is processed correctly
- [ ] Verify `data_source` updates appropriately

### Map Integration
- [ ] Upload GPX in green box - route should appear on map
- [ ] Waypoint markers should be placed
- [ ] Map should zoom to fit route
- [ ] Elevation profile should load (if available)
- [ ] Can still manually add waypoints after GPX import
- [ ] Can clear route and start over

### Edge Cases
- [ ] Upload GPX with no tracks - should show error
- [ ] Upload corrupted GPX - should show friendly error
- [ ] Upload same GPX twice - should work both times
- [ ] Upload GPX then manually edit values - data_source should be 'mixed'
- [ ] Delete trail with GPX - GPX file should be deleted from storage

### Performance
- [ ] Small GPX (< 100 points) - processes in < 2 seconds
- [ ] Medium GPX (500 points) - processes in < 5 seconds
- [ ] Large GPX (2000+ points) - shows progress, completes in < 15 seconds
- [ ] Multiple simultaneous uploads - rate limiting works

## Browser Testing
- [ ] Chrome/Edge - all features work
- [ ] Firefox - all features work
- [ ] Safari - all features work
- [ ] Mobile Chrome - responsive, usable
- [ ] Mobile Safari - responsive, usable

## Security Testing
- [ ] Non-admin user cannot access GPX endpoints
- [ ] CSRF token required for all GPX uploads
- [ ] File type validation works (rejects .exe, .php, etc)
- [ ] File size limits enforced
- [ ] Rate limiting prevents abuse

## Error Handling
- [ ] Missing required fields - validation modal shows
- [ ] Server error during GPX processing - friendly error shown
- [ ] Network timeout - graceful error message
- [ ] Storage permission issues - clear error message