# Dashboard Tables Fix

## Steps:
- [x] 1. Edit backend/models/Device.php: Fix getDevicesInside/Outside JOIN to d.id = c.device_id, status='inside'/'checked_out'; getAll JOIN s.id
- [x] 2. Edit backend/models/Analytics.php: devicesInside() + checkinCheckoutStats status='inside'/'checked_out'
- [x] 3. Edit backend/controllers/AnalyticsController.php: studentReport() JOIN d.id = c.device_id

- [ ] 4. Test: Check-in device → Refresh Dashboard → Inside table shows it
- [ ] 5. Complete
