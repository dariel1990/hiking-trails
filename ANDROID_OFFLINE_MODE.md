# Offline Mode — Android App Implementation Spec

> Drop this into your Android Studio project (e.g. `app/docs/OFFLINE_MODE.md`) so Claude Code agents working on the Android side have the full picture.

## 1. Context

- **Existing setup**: a Laravel web app at `https://trails.xploresmithers.com` is wrapped in an Android **WebView** app. There is no native UI today — the user sees the web map inside a WebView container.
- **Goal**: ship a Strava-like offline mode in the Android app:
  1. Browse previously-viewed trails when there's no network.
  2. Record a hike (GPS track) even when the screen is off.
  3. Sync the recording back to the server when the device is online again.
- **Strategy**: keep the web frontend in the WebView and **bolt on** a native Android offline layer rather than rewriting the UI. The native layer is exposed to the WebView's JavaScript through a `JavascriptInterface` bridge.

## 2. Goals

- View saved trails (name, description, photos, route line) without a network connection.
- Render the map area around a saved trail offline, at usable zoom levels.
- Record GPS tracks reliably (foreground + background, surviving screen lock).
- Queue recorded activities on disk and sync them when connectivity returns.
- Degrade gracefully: the app must remain usable on flaky/intermittent connections.

## 3. Non-goals

- Replacing the web app's UI.
- Offline route calculation / OpenRouteService calls (routing stays online-only).
- Real-time social features (live location sharing, etc.).
- Supporting iOS — this spec is Android-only.

## 4. Architecture overview

```
┌───────────────────────────────────────────────┐
│  Android App (Kotlin)                         │
│                                               │
│  ┌──────────────────────────────────────┐     │
│  │  WebView  (existing Laravel site)    │     │
│  │   ── window.Offline.* JS bridge ─┐   │     │
│  └──────────────────────────────────│───┘     │
│                                     │         │
│  ┌──────────────────────────────────▼───┐     │
│  │  OfflineBridge (JavascriptInterface) │     │
│  └──────────────────────────────────────┘     │
│       │            │             │            │
│       ▼            ▼             ▼            │
│  ┌─────────┐  ┌─────────┐  ┌────────────┐     │
│  │ API     │  │ Tile    │  │ Recording  │     │
│  │ cache   │  │ cache   │  │ service +  │     │
│  │ (Room)  │  │ (Mapbox │  │ Room DB    │     │
│  │         │  │  / SQL) │  │            │     │
│  └─────────┘  └─────────┘  └────────────┘     │
│       │                          │            │
│       └──── WorkManager ─────────▼            │
│             ActivitySyncWorker                │
└───────────────────────────────────────────────┘
            │ HTTPS (when online)
            ▼
   Laravel API at trails.xploresmithers.com
```

## 5. Components

### 5.1 Native bridge — `OfflineBridge.kt`

Expose to the WebView with:
```kotlin
webView.addJavascriptInterface(OfflineBridge(this), "Offline")
```

Methods callable from JS:

| JS call                                   | Returns      | Description                                       |
|-------------------------------------------|--------------|---------------------------------------------------|
| `Offline.isAvailable()`                   | `Boolean`    | Returns true (use to feature-detect inside web)   |
| `Offline.isOnline()`                      | `Boolean`    | Wraps `ConnectivityManager`                       |
| `Offline.saveTrailForOffline(id)`         | `Promise`    | Downloads trail JSON + media + tile pack          |
| `Offline.getSavedTrailIds()`              | `String`     | JSON array of saved trail IDs                     |
| `Offline.removeSavedTrail(id)`            | `void`       | Frees storage                                     |
| `Offline.storageUsage()`                  | `String`     | JSON `{ usedMb, quotaMb }`                        |
| `Offline.startRecording(trailId?)`        | `void`       | Starts foreground service                         |
| `Offline.pauseRecording()`                | `void`       |                                                   |
| `Offline.resumeRecording()`               | `void`       |                                                   |
| `Offline.stopRecording()`                 | `String`     | JSON of recorded `RecordedActivity`               |
| `Offline.recordingState()`                | `String`     | JSON `{ active, durationMs, distanceM, points }`  |
| `Offline.queuePendingUpload(activityJson)`| `void`       | Stages an activity for `ActivitySyncWorker`       |

> All `String` return values are JSON. Async results should resolve through a `window.OfflineEvents.dispatch(...)` hook the WebView code listens for, OR use a callback id pattern. Pick one and stick with it — agents must not mix.

### 5.2 API response cache

- **OkHttp cache** for `/api/*` responses with `Cache(File, 50_000_000)` and a custom interceptor that:
  - Adds `max-stale` headers when offline (`force-cache`)
  - Falls back to disk on `IOException`
- Wrap the WebView's network with `WebViewClient.shouldInterceptRequest()` and route `/api/*` through the same OkHttp client. This is the only way to make the WebView's `fetch()` calls go through the cache.
- Endpoints that **must** be cached for offline:
  - `GET /api/trails`
  - `GET /api/trail-networks`
  - `GET /api/facilities`
  - `GET /api/businesses`
  - `GET /api/highlights`
  - Image URLs under `/storage/...` (best-effort, sized down by the server)

### 5.3 Map tile cache

Two options — pick one and document the choice in this file when implemented:

1. **Mapbox SDK Offline Manager** (recommended if you keep Mapbox).
   - Use `OfflineRegion` with the bounding box of each saved trail expanded ~500 m.
   - Zoom range `10..16` (configurable). 14 is a good default balance.
   - Listen to `OfflineRegionStatus` and surface progress via the JS bridge.
   - **Licensing**: confirm your Mapbox plan allows offline tile caching and Monthly Active Users counting.
2. **Custom tile cache** (if you switch to MapTiler / self-hosted).
   - Tiles fetched through OkHttp + cached on disk in `tiles/{z}/{x}/{y}.pbf`
   - Serve them through `WebViewAssetLoader` so the JS Mapbox GL still works.

Storage budget: cap at **200 MB** total tile cache; warn user above 150 MB; oldest-saved trail tiles are evicted first.

### 5.4 GPS recording — `LocationRecordingService`

- Foreground `Service` (NOT a JobIntentService) with a persistent notification.
- Channel: `RECORDING_CHANNEL` (importance `LOW` so it doesn't sound).
- Notification: "Recording hike — 2.3 km · 00:45:12 · tap to stop".
- Location source: `FusedLocationProviderClient` with `LocationRequest.Builder(PRIORITY_HIGH_ACCURACY, 3000).setMinUpdateDistanceMeters(5f).build()`.
- Persist points to Room (`RecordedActivityPoint`) every 10 s in batches.
- On `onTaskRemoved` → keep service alive (`START_STICKY`).
- Stop conditions: user taps stop, or `> 6 hr` with no movement.

### 5.5 Activity sync — `ActivitySyncWorker`

- `CoroutineWorker` scheduled by `WorkManager` with `NetworkType.CONNECTED`.
- Picks up rows from `recorded_activity` where `synced_at IS NULL`.
- Posts to **`POST /api/activities`** (Laravel side; see §7).
- Updates `synced_at` on success; exponential backoff on failure.
- Triggered:
  - When a recording is stopped.
  - When connectivity changes from offline → online.
  - Every 30 minutes as a fallback.

## 6. Permissions (AndroidManifest.xml)

```xml
<uses-permission android:name="android.permission.INTERNET"/>
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE"/>

<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION"/>
<uses-permission android:name="android.permission.ACCESS_BACKGROUND_LOCATION"/>

<uses-permission android:name="android.permission.FOREGROUND_SERVICE"/>
<!-- Android 14+ -->
<uses-permission android:name="android.permission.FOREGROUND_SERVICE_LOCATION"/>

<uses-permission android:name="android.permission.POST_NOTIFICATIONS"/>
<uses-permission android:name="android.permission.WAKE_LOCK"/>
```

**Play Store policy**: request `ACCESS_BACKGROUND_LOCATION` only the moment the user taps "Start recording", and explain *why* in a rationale dialog. Never bundle it with other permission prompts at app launch.

## 7. Backend (Laravel) additions — out of scope of this doc, but required

These need to exist on the Laravel side before Phase 5 ships. Track them in the Laravel repo:

- `POST /api/activities` — accepts:
  ```json
  {
    "trail_id": 17,
    "started_at": "2026-05-01T15:32:11Z",
    "ended_at":   "2026-05-01T16:48:02Z",
    "distance_m": 4231.5,
    "points": [[54.78, -127.16, 1714576331, 612.0], ...]
  }
  ```
- `GET /api/trails/{id}/offline-bundle` — convenience endpoint that returns the full trail JSON, GPX, and resized photos in one round trip (reduces requests for Phase 2).
- `users` need an auth token mechanism so activities can be associated with the recording user (Sanctum is already installed — issue tokens at login).

## 8. Phased rollout

| Phase | Scope                                                         | Est.   |
|-------|---------------------------------------------------------------|--------|
| 1     | OkHttp cache for `/api/*`, WebView interceptor, offline UX    | ~1 wk  |
| 2     | "Save trail for offline" — JSON + photos + GPX on disk        | ~1 wk  |
| 3     | Mapbox offline tile pack per saved trail + storage UI         | ~2 wks |
| 4     | Foreground GPS recording, persistent notification, Room DB    | ~1 wk  |
| 5     | `ActivitySyncWorker` + retries + Laravel `POST /api/activities` | ~1 wk  |

**Total: ~6 weeks for a solid v1.** Phases can ship independently behind a remote feature flag.

## 9. Acceptance criteria

- [ ] Cold-start the app in airplane mode after the user has visited the home page once → trail list still renders.
- [ ] A saved trail's detail page (with photos and route line) opens with no network.
- [ ] At zoom 14, the map around a saved trail draws fully offline.
- [ ] Pressing "Record hike", locking the phone, walking 200 m, and stopping produces a track of correct length (within ±10 m).
- [ ] Activities recorded offline appear on the server within 60 s of regaining connectivity.
- [ ] One hour of recording costs < 6 % battery on a Pixel 6 reference device.
- [ ] App stays under the Play Store's `ACCESS_BACKGROUND_LOCATION` review thresholds: permission requested only at recording-start, with rationale dialog.

## 10. Open questions

1. **Tile provider** — stay on Mapbox (paid plan with offline rights) or move to MapTiler / Thunderforest / self-hosted?
2. **Storage cap** — auto-evict saved trails after N days unused, or only manual delete?
3. **Recorded route display** — once an activity syncs, should the web map show it as a personal layer? If yes, a `GET /api/users/me/activities` endpoint is also needed.
4. **Auth** — do we add a login screen in the native shell, or rely on cookie-based session inside the WebView? (Affects how the sync worker authenticates without a WebView running.)

## 11. Things to NOT do

- **Don't rewrite the UI in native Compose / XML.** The WebView stays. Native code only adds capabilities the web layer can't reach.
- **Don't ship a full Capacitor / Cordova migration** without explicit approval — it's much more work than this plan.
- **Don't bypass the existing Laravel API contracts**. If a field is missing on the backend, raise it as a Laravel-side ticket; do not invent client-only fields.
- **Don't request all permissions at launch.** Background location must be deferred to the moment of use.
- **Don't poll `Offline.recordingState()` from the web layer.** Use the event-dispatch pattern so the WebView is updated push-style (battery-friendly).
- **Don't store raw photos in Room.** Use the file system; store only paths in the DB.

## 12. Conventions for Claude Code agents working in this repo

- **One feature, one PR.** Each phase above is a separate PR.
- **No silent permission requests.** Every new permission must come with a `Manifest.permission.*` rationale string in the PR description.
- **All native ↔ JS contracts go in one file**: `OfflineBridge.kt`. No JavascriptInterface methods anywhere else.
- **Threading**: bridge methods that touch disk MUST return immediately and dispatch results via `window.OfflineEvents`. Blocking the JS thread will freeze the WebView.
- **Tests**:
  - Unit tests for the API cache interceptor.
  - Instrumented test for `LocationRecordingService` with a fake `FusedLocationProviderClient`.
  - Manual matrix: Pixel 6 (Android 14), older Samsung mid-range (Android 11).
- **Logging**: tag everything with `OfflineMode/{Component}` so logs are filterable.

---

When in doubt about scope, refer back to §3 (Non-goals) and §11 (Things to NOT do).
