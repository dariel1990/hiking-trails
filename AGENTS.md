# AGENTS.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Common commands

### Install / first-time setup
- Install PHP deps:
  - `composer install`
- Install JS deps / build tooling:
  - `npm install`
- Create DB schema + seed baseline data:
  - `php artisan migrate --seed`
    - Seeds include a default admin user via `database/seeders/AdminUserSeeder.php`:
      - email: `admin@example.com`
      - password: `password123`
- Serve uploaded photos/GPX from the `public` disk:
  - `php artisan storage:link`

### Run locally
- Run the full dev stack (Laravel server + queue listener + logs + Vite):
  - `composer dev`
    - Runs `php artisan serve`, `php artisan queue:listen --tries=1`, `php artisan pail --timeout=0`, and `npm run dev` concurrently (see `composer.json`).
- Run pieces individually:
  - App server: `php artisan serve`
  - Frontend dev server: `npm run dev`

### Build assets
- Production build (Vite):
  - `npm run build`

### Format / lint
- PHP formatting (Laravel Pint):
  - Check: `./vendor/bin/pint --test`
  - Fix: `./vendor/bin/pint`

### Tests
- Run all tests:
  - `composer test` (clears config then runs `php artisan test`)
  - or directly: `php artisan test`
- Run a single test file:
  - `php artisan test tests/Feature/SomeTest.php`
  - `php artisan test tests/Unit/SomeTest.php`
- Run a single test by name/pattern:
  - `php artisan test --filter SomeTest`

### Operational / maintenance artisan commands
- Clean temporary GPX preview uploads (public disk `gpx/temp`, older than 1 hour):
  - `php artisan gpx:clean-temp`
- Scrape events from SmithersEvents.com:
  - `php artisan events:scrape`
  - `php artisan events:scrape --debug`
  - `php artisan events:scrape --force` (truncates existing events first)
- Migrate existing trails to set location_type (one-time migration):
  - `php artisan trails:migrate-location-type --dry-run` (preview changes)
  - `php artisan trails:migrate-location-type` (apply changes with confirmation)
  - `php artisan trails:migrate-location-type --force` (apply without confirmation)

## High-level architecture

### Framework + entrypoints
- Laravel 12 app.
- Route registration + middleware aliases are configured in `bootstrap/app.php`.
- HTTP routes:
  - Public + admin web routes: `routes/web.php`
  - JSON API routes used by the map/admin route builder: `routes/api.php`

### Core domains

#### Trails & Fishing Lakes (public site, interactive map, and admin CRUD)
- Main model: `app/Models/Trail.php`
  - Supports two location types: `trail` and `fishing_lake` (via `location_type` enum)
  - Two geometry types: `linestring` (multi-point routes) and `point` (single location)
  - Key geo fields are JSON arrays:
    - `start_coordinates`: `[lat, lng]`
    - `end_coordinates`: `[lat, lng] | null`
    - `route_coordinates`: `[[lat, lng], ...]` for trails, `[[lat, lng]]` for fishing lakes (single-point array)
  - Trail-specific fields (nullable): `difficulty_level`, `distance_km`, `elevation_gain_m`, `estimated_time_hours`, `trail_type`
  - Fishing lake-specific fields: `fishing_location` (text), `fishing_distance_from_town`, `fish_species` (JSON array), `best_fishing_time`, `best_fishing_season`
  - Helper methods: `isFishingLake()`, `isTrail()`, `getFishSpeciesList()`
  - Media/feature relationships are surfaced through `media()`, `features()`, and `generalMedia()`.
- Public pages are served by `app/Http/Controllers/TrailController.php`:
  - `/` (featured trails)
  - `/trails` listing + filtering
  - `/map` interactive Leaflet map (shows both trails and fishing lakes with different icons)
  - API endpoints:
    - `GET /api/trails` powers the public map (returns normalized coordinates, media, highlights, activities, location_type).
- Admin CRUD + the trail builder live in `app/Http/Controllers/Admin/AdminTrailController.php` and Blade views under `resources/views/admin/trails/`.
  - Conditional validation: trails require GPX/route data, fishing lakes require point coordinates
  - Admin forms use Alpine.js to show/hide sections based on location_type
  - Fishing lake creation uses `resources/js/PointPicker.js` for single-point map picker with search (Nominatim API)

#### GPX upload + auto-calculations
- GPX parsing/calculation is centralized in `app/Services/GpxService.php` (phpGPX):
  - Calculates distance (Haversine), elevation gain, and estimated time.
  - Simplifies long tracks (reduces to ~500 points) for `route_coordinates` storage/rendering.
- Storage conventions (public disk):
  - Permanent GPX uploads: `storage/app/public/gpx/`
  - Temp preview/compare uploads: `storage/app/public/gpx/temp/`
- Trail GPX metadata fields are added in `database/migrations/2025_10_13_083808_add_gpx_fields_to_trails_table.php`:
  - `gpx_raw_data`, `gpx_calculated_*`, `data_source` (`manual`/`gpx`/`mixed`), `gpx_uploaded_at`.
- Admin GPX endpoints (see `routes/web.php`):
  - `POST /admin/trails/gpx/preview` → `AdminTrailController@previewGpx`
  - `POST /admin/trails/{trail}/gpx/compare` → `AdminTrailController@compareGpx`

#### Smart routing + elevation profile (admin route builder)
- The admin trail builder (inlined JS inside `resources/views/admin/trails/create.blade.php` and `resources/views/admin/trails/edit.blade.php`) calls JSON endpoints to compute route segments and elevations:
  - `POST /api/calculate-route`:
    - Uses `app/Services/RouteService.php` to call OpenRouteService (foot-walking profile).
    - Requires `OPENROUTESERVICE_API_KEY` (see `config/services.php`).
  - `POST /api/elevation-profile`:
    - Uses `RouteService::getElevationProfile()` which calls OpenTopoData (no API key).

#### Public interactive map
- The map page (`resources/views/map.blade.php`) contains a large inlined JS implementation (`EnhancedTrailMap`) that:
  - Initializes Leaflet and manages filters (season, distance, difficulty, advanced filters).
  - Fetches trails via `GET /api/trails?...`.
  - Fetches facilities via `GET /api/facilities`.
- Shared frontend entrypoint is minimal (`resources/js/app.js`): Alpine + Leaflet globals + Leaflet CSS.

#### Events
- Events are stored in `app/Models/Event.php` and rendered by `app/Http/Controllers/EventsController.php`.
- Scraping:
  - Artisan command: `app/Console/Commands/ScrapeSmithersEvents.php`.
  - There is also a scraper service implementation at `app/Services/SmithersEventsScraper.php`.
- Scheduling:
  - `routes/console.php` schedules `events:scrape --force` daily at 02:00 and appends output to `storage/logs/scraper.log`.

#### Scheduled maintenance
- Scheduler registration lives in `bootstrap/app.php` and `routes/console.php`.
- Currently scheduled:
  - `gpx:clean-temp` hourly (`bootstrap/app.php`).
  - `events:scrape --force` daily at 02:00 (`routes/console.php`).

### Auth/admin
- Admin gating is via `is_admin` on `users` (see `app/Models/User.php`) and `App\Http\Middleware\AdminMiddleware`.
- Admin routes are under the `/admin` prefix in `routes/web.php`.

## Repo-specific docs
- `ADMIN_GUIDE.md`: how to use the admin GPX upload / auto-calculator flows.
- `TESTING_CHECKLIST.md`: manual QA checklist for GPX upload + map integration edge cases.
