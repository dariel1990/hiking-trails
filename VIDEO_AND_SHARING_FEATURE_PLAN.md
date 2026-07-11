# Video Link + Social Sharing for Tours and Trail Networks

## Context

Clients want to attach **one video link (mostly YouTube, also Vimeo) per Tour and per Trail Network** — set by the admin, playable in the admin and on the public pages. Additionally, users should be able to **share Tours and Trail Networks on social media**, replicating the existing trail share flow — same process, layout, and design as the trail share modal (Facebook, X, WhatsApp, Email, Copy link; no server-side share tracking exists on trails or is required).

The codebase already has mature precedents to reuse:
- **Video links**: `app/Models/TrailMedia.php` (`video_url` + provider detection, `getEmbedUrl()`, YouTube/Vimeo ID regexes — but its helpers are `private`), admin play-in-modal in `resources/views/admin/trails/edit.blade.php` (`playExistingVideo()` ~5594), public click-to-play with YT IFrame API in `resources/views/trails/show.blade.php` (`_ensureYouTubeApi`/`_mountYouTube` ~1695–1723, thumbnails via `img.youtube.com`/`vumbnail.com`).
- **Sharing**: trail share modal in `resources/views/trails/show.blade.php` — button ~1224, modal markup + `xs-share-*` CSS ~1320–1431, JS handlers ~3009–3102; OG/Twitter meta `@push('meta')` lines 4–29 rendered via `@stack('meta')` in `layouts/public.blade.php:23`. Tours and networks currently have **no** share UI and **no** OG tags. Both public pages extend `layouts.public`.

Decisions: **one video link** per tour/network (a nullable column on each table, like `website_url` on networks); on the trail-network public page the video goes in the **sidebar** below description/stats.

## Phase 1 — Migrations, models, shared trait

**Migrations** (via `php artisan make:migration --no-interaction`):
- `add_video_url_to_tours_table`: `$table->string('video_url', 500)->nullable()->after('cover_image');`
- `add_video_url_to_trail_networks_table`: `$table->string('video_url', 500)->nullable()->after('website_url');`

No provider column — the provider is derived from the URL (single field, no sync logic needed).

**New trait** `app/Models/Concerns/HasVideoEmbed.php` (new dir — standard Laravel location), operating on the model's `video_url` attribute. Port from `TrailMedia` (its helpers are `private`, hence the trait):
- `extractYouTubeId()` / `extractVimeoId()` (copy regexes from TrailMedia ~176/186 — handles watch/embed/shorts/youtu.be)
- `getVideoProviderAttribute(): ?string` — same logic as `AdminTrailController::detectVideoProvider()` (~1386): youtube | vimeo | other | null
- `getVideoEmbedUrlAttribute(): ?string` → `https://www.youtube.com/embed/{id}` / `https://player.vimeo.com/video/{id}`, null when no `video_url`
- `getVideoThumbnailUrlAttribute(): ?string` → `https://img.youtube.com/vi/{id}/hqdefault.jpg` / `https://vumbnail.com/{id}.jpg` / null

**Models**: add `'video_url'` to `$fillable` and `use HasVideoEmbed;` on both `app/Models/Tour.php` and `app/Models/TrailNetwork.php`. No new models, relationships, or factories needed.

## Phase 2 — Validation

Shared rule (accept only YouTube/Vimeo URLs):
```php
'video_url' => ['nullable', 'url', 'max:500',
    'regex:#^https?://(www\.)?(youtube\.com|youtu\.be|m\.youtube\.com|vimeo\.com)/#i'],
```

- **Tours**: add to BOTH `app/Http/Requests/Admin/StoreTourRequest.php` and `UpdateTourRequest.php` (rules are duplicated between them by convention).
- **Networks**: add to `networkRules()` in `AdminTrailNetworkController.php` (near `website_url`, ~line 160). **Gotcha:** the regex contains `|`, so this rule MUST be array-form (the rest of the method uses pipe strings — Laravel accepts mixed); update the method docblock from `array<string, string>` to `array<string, mixed>`.

Add a friendly custom message in both places (Form Request `messages()`; second arg to `$request->validate()` for networks): "The video link must be a YouTube or Vimeo URL."

## Phase 3 — Controllers

- **`AdminTourController`**: no changes needed beyond validation — `video_url` flows through the existing `Tour::create($validated)` / `$tour->update($validated)` mass assignment once fillable + rules exist.
- **`AdminTrailNetworkController`**: same — `video_url` rides along with the validated data already passed to create/update.
- **Public controllers**: no changes — `video_url` is a column on the models already passed to the views.

## Phase 4 — Admin blades

**Tours** — `resources/views/admin/tours/_form.blade.php`:
- Add a "Video Link (YouTube or Vimeo)" `<input type="url" name="video_url" value="{{ old('video_url', $tour->video_url ?? '') }}">` field, styled like the existing inputs, in the Tour Details card (near `duration_estimate`), with helper text and `@error('video_url')` display.
- Below it, a preview block shown when a URL is set: thumbnail (`$tour->video_thumbnail_url`, dark tile fallback) with play overlay calling `playAdminVideo(url)` — copy `playExistingVideo()` from `admin/trails/edit.blade.php` ~5594 into `_scripts.blade.php` (builds embed URL, opens fixed-overlay modal iframe).

**Networks** — `resources/views/admin/trail-networks/create.blade.php` and `edit.blade.php`:
- Same "Video Link" input directly below the existing `website_url` field in both files (edit prefills `$trailNetwork->video_url`), with `@error` display.
- On edit, the same thumbnail + `playAdminVideo()` preview (inline `<script>` for the modal helper, as these pages keep their JS inline).

**Admin show page** — `resources/views/admin/trail-networks/show.blade.php`: add a video row/card (thumbnail + play) alongside the existing cover-image block so admins can verify playback. (Tours have no admin show view — skip.)

## Phase 5 — Public blades

**Shared video embed partial** `resources/views/partials/video-embed.blade.php` — props: `$model` (Tour or TrailNetwork, anything using `HasVideoEmbed`), `$embedId` (unique string). Renders nothing when `$model->video_url` is empty. Otherwise:
- A 16:9 thumbnail tile (`video_thumbnail_url`, dark fallback tile for `other` provider) with white play-button overlay (copy overlay markup from `trails/show.blade.php` ~862–868). Click-to-play, not an eager iframe — keeps the map-heavy pages light.
- `@once` script: on click — (a) hand off to `window.Offline.playVideo(url)` inside the native app (plain YT iframes fail in the app WebView, error 152 — same guard as trails/show ~1626); (b) YouTube via the IFrame Player API (copy `_ensureYouTubeApi`/`_mountYouTube` from trails/show ~1695–1723, rename with an `xsVe` prefix to avoid global collisions); (c) Vimeo autoplay iframe. Player mounts in place of the thumbnail (or a small lightbox); destroy iframe on close/navigation so audio stops.
- **Not Pro-gated** (trail videos are `xsIsPro`-gated; tour/network videos are public per requirement — flag to client).

**`resources/views/tours/show.blade.php`**:
1. `@push('meta')` after `@section('title')` mirroring trails/show lines 4–29: og:title = `$tour->title`, description from tagline/description (`Str::limit`), image = `url($tour->cover_image_url)` (guarded), + Twitter `summary_large_image` block.
2. Video card in the right column after the "About This Tour" card (~175), before Tour Stops: `@if($tour->video_url)` heading "Video" + the embed partial.
3. "Share Tour" button (below/in the About card) with `data-share-open="tour-share"`; include the share-modal partial at end of content (`kicker: 'Share This Tour'`, title/tagline, share text "Check out the {title} tour in Smithers, BC!").

**`resources/views/trail-networks/show.blade.php`** (~1793 lines — purely additive changes):
1. `@push('meta')`: title `$network->network_name`, description, image `url('storage/'.$network->image)` (guarded).
2. **Sidebar video**: the description/stats live in the non-scrolling sticky `.sidebar-header` (~306). A single 16:9 tile is compact enough, but to be safe with small laptop heights place it as the **first block inside the scrollable container** (the `overflow-y-auto flex-1` div ~684, above `#trails-container`) — visually directly below the stats when not scrolled.
3. Share button in the sidebar header (next to the close button ~618 or a row under "Visit Website") with `data-share-open="network-share"`; include the share-modal partial (`kicker: 'Share This Network'`).

## Phase 6 — Shared share-modal partial `resources/views/partials/share-modal.blade.php`

Pixel-for-pixel copy of the trail modal (markup + `xs-share-*` CSS, trails/show ~1320–1431: orange kicker `#E87B35`, teal `#2C5F5D`, bottom-sheet mobile / centered desktop, same animations), parameterized:
- Vars with defaults: `$shareId` (suffixes all element ids), `$kicker`, `$title`, `$subtitle`, `$shareUrl` (default `url()->current()`), `$shareText`, `$emailSubject`, `$emailBody`.
- CSS + shared JS wrapped in `@once`.
- **No globals**: openers targeted via `[data-share-open="{{ $shareId }}"]`; action buttons use `data-share-action="facebook|twitter|whatsapp|email"` with one delegated listener (same share URLs as trail: facebook.com/sharer, twitter.com/intent/tweet, wa.me, mailto). Copy-to-clipboard w/ fallback, ESC/backdrop close, `is-visible`/`is-open` timing copied verbatim from trails/show ~3025–3083.
- **Modal z-index bumped to ~10500** — the network mobile sidebar is z-index 9999; the trail modal's 9500 would sit underneath it.
- Existing trail share implementation stays untouched (refactoring the 5600-line file is out of scope; data-attribute delegation avoids global-name collisions).

## Phase 7 — Tests & verification

Run `vendor/bin/pint --dirty` before finalizing. Admin routes need `User::factory()->create(['is_admin' => true])` (guarded by `auth`+`admin` middleware, routes/web.php ~138). Tour/TrailNetwork have no factories (no `HasFactory`) — create records directly per `tests/Feature/TrailPhotoModelTest.php` convention (private `makeTour()`/`makeNetwork()` helpers).

**`tests/Feature/TourVideoTest.php`** (PHPUnit class, `RefreshDatabase`):
1. Admin creates a tour with a YouTube `video_url` → persisted.
2. Admin updates a tour to a Vimeo URL / clears it (`video_url => ''` → null-ish) → persisted.
3. Invalid URL (`https://example.com/video`) → `assertSessionHasErrors('video_url')`.
4. Accessor tests: watch/youtu.be/shorts URLs → correct `video_embed_url` + `img.youtube.com` thumbnail; vimeo → `player.vimeo.com` + vumbnail; null `video_url` → null accessors.
5. Public tour page shows the video block + `og:title` (`assertSee(..., false)`); no video block when `video_url` is null.

**`tests/Feature/TrailNetworkVideoTest.php`**: mirror 1–3 against `admin.trail-networks.store/update` (required fields: network_name, type, season, latitude, longitude), plus public `trail-networks.show` renders the sidebar video block, OG tags, and share modal (`data-share-open`).

Run: `php artisan test tests/Feature/TourVideoTest.php tests/Feature/TrailNetworkVideoTest.php`.

**Manual verification**:
1. `php artisan migrate`.
2. Admin tours create + edit: paste a YouTube link, see thumbnail preview, play in modal, save, reload — persists; clear it — removed. Same for trail networks (create, edit, admin show).
3. Public tour page: thumbnail renders; YouTube and Vimeo both play; closing stops audio. Share modal: open/close (ESC, backdrop), copy link, FB/X/WhatsApp/Email URLs correct.
4. Public network page: video sits below stats in the sidebar (desktop + ≤768px mobile bottom-sheet); share modal renders above the mobile sidebar.
5. `curl <public url> | grep og:` to confirm OG tags on both pages.
6. Regression: trail detail page share + video playback untouched.

## Risks / gotchas
- Trail videos are Pro-gated; tour/network videos deliberately are not — confirm with client.
- Native app WebView requires the `Offline.playVideo` handoff / YT IFrame API (error 152 with bare iframes).
- `networkRules()` regex must be an array-form rule (pipe character in the pattern).
- Rename copied JS helpers (`xsVe` prefix) and use data-attribute delegation so nothing collides with trail-page globals.
- Network mobile sidebar z-index 9999 → share modal z-index raised to ~10500.
