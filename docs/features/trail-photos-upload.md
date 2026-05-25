# Trail Photo Upload Feature

User-submitted trail photos with admin moderation and a public carousel on each trail.

**Estimated effort:** 28–43 hours across 6 phases.

---

## Goals

- Let visitors contribute photos to any trail (or fishing lake) so the gallery grows organically.
- Keep quality and safety in admin control via a pending/approved/rejected workflow.
- Notify admins by email when a new submission needs review.
- Display only approved photos publicly, in a carousel reusing the project's existing slider.

## Non-goals (for v1)

- User accounts / login is **not required** to submit. Submitter provides a name + email (email required for moderation, never displayed).
- No edit/delete by the submitter after upload.
- No comments, likes, or social features on photos.
- No multi-photo batch upload from a single submitter in one request (one file per submission for v1).
- No email **verification** loop — gated by admin approval + reCAPTCHA + rate limit instead.

## Security model (v1)

Decided defenses against abuse, layered:

1. **Required email** on the submission form, stored for moderation only. Never rendered publicly or in the API.
2. **Honeypot field** (`website`) — silent reject if filled.
3. **Google reCAPTCHA v3** — invisible, scores each request 0.0 (bot) to 1.0 (human). Add `RECAPTCHA_SITE_KEY` / `RECAPTCHA_SECRET_KEY` to `.env` and `config/services.php`. Server rejects submissions scoring below **0.5** (configurable via `RECAPTCHA_MIN_SCORE`). Note: reCAPTCHA tracks visitors via Google cookies — acceptable tradeoff per project decision (2026-05-24).
4. **Rate limit** on `POST /api/trail-photos`: 5 submissions per hour per IP via Laravel's `throttle` middleware. Also throttle per email (5/day) at the controller level.
5. **Manual admin approval** — nothing is publicly visible until an admin clicks Approve.
6. **MIME revalidation on the server**, not just by extension. Reject SVG outright (XSS vector).
7. **Strip EXIF / GPS metadata** during the resize step so submitters do not leak (or attackers do not embed) personal location data.
8. **Filename randomization** (UUID) — submitter never controls the path or extension.
9. Photos for trails with `location_type = fishing_lake` are subject to the same workflow as hiking trails.

---

## Phase 1 — Database & Structure (4–6 hrs)

### `trail_photos` table

| Column        | Type                                        | Notes                                                    |
| ------------- | ------------------------------------------- | -------------------------------------------------------- |
| `id`          | bigint, PK                                  |                                                          |
| `trail_id`    | foreign key → `trails.id`, `cascadeOnDelete`| Required.                                                |
| `image_path`  | string                                      | Path under `storage/app/public/trail-photos/`.           |
| `caption`     | string (nullable, max 255)                  | Optional.                                                |
| `name`        | string (nullable, max 100)                  | Submitter's display name. Optional, shown publicly.      |
| `email`       | string, required                            | Stored for moderation only. **Never displayed or returned via API.** |
| `submitter_ip`| string (nullable, 45)                       | IP at submission time, for abuse triage.                 |
| `status`      | string, default `pending`                   | `pending` / `approved` / `rejected`.                     |
| `reviewed_by` | foreign key → `users.id` (nullable)         | Admin who actioned it.                                   |
| `reviewed_at` | timestamp (nullable)                        |                                                          |
| `timestamps`  | `created_at`, `updated_at`                  |                                                          |

Index `status` and `trail_id` (composite) for fast public listing.

### Model & relationships

- `App\Models\TrailPhoto`
  - `belongsTo(Trail::class)`
  - `belongsTo(User::class, 'reviewed_by')`
  - Scopes: `scopeApproved`, `scopePending`.
  - `casts()` method (Laravel 12 convention) for `reviewed_at`.
- `Trail` model gains `hasMany(TrailPhoto::class)` and a convenience `approvedPhotos()` relationship.
- Factory + seeder for `TrailPhoto` (status states covered).

---

## Phase 2 — User Submission (6–10 hrs)

### Upload form

- Lives **on the trail details page only for v1** (`resources/views/trails/show.blade.php`, route `trails.show`).
- A "Submit a photo" button opens a modal. Built as a reusable Blade partial (`resources/views/components/trail-photo-upload-modal.blade.php`) so a future map-popup integration can drop it in.
- Fields:
  - File (required) — passes through cropper before submit.
  - Caption (optional, max 255).
  - Submitter name (optional, max 100).
  - **Email (required)** — labelled "Not shown publicly. Used only if we need to follow up about your photo."
  - **Invisible** reCAPTCHA v3 — token generated on submit via `grecaptcha.execute(SITE_KEY, {action: 'submit_trail_photo'})`. No visible widget.
  - Hidden honeypot field (`website`).
- Client flow:
  1. Pick file → preview in cropper (see Phase 3 below).
  2. Apply crop → cropped Blob attached as the upload payload.
  3. Submit → POST to API → on success, show "Thanks, awaiting admin review" inline.

### Cropping (client-side, before upload)

- Use [Cropper.js](https://github.com/fengyuanchen/cropperjs) (small, well-maintained, MIT). Confirm with user before adding the npm dep.
- **Fixed aspect ratio: 16:9** for v1, applied uniformly to all submissions so the carousel is visually consistent. Easy to change in one place later.
- Output: cropped image as a `Blob` at max 1600×900 (cropper handles downscaling). Server still re-validates and re-encodes.

### API endpoint

`POST /api/trail-photos`

- Rate-limited: `throttle:5,60` (5 per hour per IP) at the route level + per-email throttle in the controller (5/day).
- Validated via `App\Http\Requests\StoreTrailPhotoRequest`.
- Validation rules:
  - `trail_id` → required, exists on `trails` (covers both `trail` and `fishing_lake` rows since they share the table).
  - `image` → required, file, image, `mimes:jpg,jpeg,png,webp` (no SVG), `max:8192` (8 MB).
  - `caption` → nullable, string, max:255.
  - `name` → nullable, string, max:100.
  - `email` → **required**, email, max:150.
  - `g-recaptcha-response` → required, validated server-side against `RECAPTCHA_SECRET_KEY`; score must be ≥ `RECAPTCHA_MIN_SCORE` and `action` must equal `submit_trail_photo`.
  - Honeypot `website` → must be empty.
- Behavior:
  - Verify reCAPTCHA token (POST to `https://www.google.com/recaptcha/api/siteverify` with `secret`, `response`, and `remoteip`). Reject if `success !== true`, `action !== 'submit_trail_photo'`, or `score < RECAPTCHA_MIN_SCORE`.
  - Revalidate image MIME server-side using `getimagesize` / Intervention.
  - Re-encode image (strips EXIF/GPS) and store under `storage/app/public/trail-photos/{trail_id}/{uuid}.webp`.
  - Generate a 640×360 thumbnail at `storage/app/public/trail-photos/{trail_id}/thumbs/{uuid}.webp`.
  - Insert row with `status = pending`, `submitter_ip = $request->ip()`.
  - Dispatch `NewTrailPhotoSubmitted` notification to all admins (Phase 4).
  - Return 201 with `{ message: "Thanks — your photo is awaiting review." }`. Do **not** return the image URL (not public yet).

---

## Phase 3 — Admin Approval Panel (4–6 hrs)

### Route

`/admin/trail-photos` gated by existing `AdminMiddleware`.

### List page

- Table or card grid showing: preview thumbnail, trail name (link), submitter name, caption, status, submitted date.
- Filters: status (default `pending`), trail.
- Per-row action: **Approve** / **Reject** buttons (or status dropdown — pick whichever matches sibling admin pages; check `AdminTrailController` views).
- Inline modal/lightbox to view the full image without leaving the list.

### Bulk actions (nice-to-have)

- Checkbox + bulk Approve / Reject from the list header.

### Audit

- On any status change: set `reviewed_by = auth()->id()` and `reviewed_at = now()`.

---

## Phase 4 — Notification System (3–5 hrs)

### On submission

- Notification class `App\Notifications\NewTrailPhotoSubmitted` (mail channel).
- Sent to **all users where `is_admin = true`** (resolved at dispatch time so newly promoted admins also get notified).
- Email body includes:
  - Trail name + link to public trail page.
  - Submitter name (or "Anonymous") and caption.
  - Inline preview thumbnail.
  - **Direct link to the admin review page** (`/admin/trail-photos?status=pending#photo-{id}`).
- Use a queued notification (`ShouldQueue`) so submissions are not blocked by SMTP latency.

### On approval (optional v1.1)

- If the submitter provided an email, send a "Your photo was approved" message with the public link.

---

## Phase 5 — Public Display / Carousel (5–8 hrs)

### API

`GET /api/trail-photos?trail_id={id}`

- Returns approved photos only, ordered `created_at desc`.
- Eloquent API Resource: `TrailPhotoResource` with `id`, `image_url` (Storage::url), `caption`, `name` (or "Anonymous"), `created_at`.

### Frontend

- Reuse the existing slider library used elsewhere in the project (check `resources/views` for Swiper or similar before adding a dep).
- Carousel placed on the **trail details page only** (`resources/views/trails/show.blade.php`) for v1. Map-popup integration is deferred.
- Works for both `location_type = trail` and `location_type = fishing_lake` since both render via the same show view.
- Captions shown beneath each slide; submitter name shown subtly (small text). Email is **never** rendered.
- Empty state: **"Be the first to upload a photo of this trail."** (or "…this fishing lake." conditionally on `location_type`) with a CTA opening the upload modal.

---

## Phase 6 — Optimization & Edge Cases (6–8 hrs)

### Image handling

- Server re-encodes all uploads to **WebP at quality 85** after cropping. This:
  - Strips EXIF/GPS metadata.
  - Normalizes format (no surprises from weird PNG profiles).
  - Reduces storage vs. JPEG.
- Two variants stored per submission:
  - **Full**: 1600×900 (16:9) at `trail-photos/{trail_id}/{uuid}.webp`.
  - **Thumb**: 640×360 (16:9) at `trail-photos/{trail_id}/thumbs/{uuid}.webp`.
- Use `intervention/image` v3 for server-side processing (confirm with user before adding).
- MIME re-check on the server via `getimagesize` after upload — do not trust the client-supplied MIME or extension.

### Limits

- Max upload 8 MB pre-encode.
- IP rate limit 5/hour via `throttle:5,60` on the API route.
- Email rate limit 5/day enforced in `StoreTrailPhotoRequest::passedValidation()` or the controller.

### Spam protection

- Honeypot field (`website`) — silent 200 response if filled, but no DB write.
- **Google reCAPTCHA v3** required on every submission (see Security model section above).

### Edge cases

- Trail deleted → cascade delete photo rows (FK), and a `TrailPhoto::deleting` model event deletes both the full and thumb files from `storage/app/public`.
- Photo **rejected** by admin → keep DB row for audit (status = `rejected`, `reviewed_by`, `reviewed_at`), but delete the files from disk to save storage.
- Missing file on disk at read time → public `index` endpoint filters out rows whose `image_path` no longer exists, and logs a warning.
- Submitter clicks "Approve" link in email after the photo was already actioned → admin page shows current status, not an error.
- reCAPTCHA script fails to load (network/blocked) → form clearly shows the error and disables submit; no silent fallback that lets bots through.

---

## File / route inventory (planned)

**Migrations**
- `database/migrations/{timestamp}_create_trail_photos_table.php`

**Models**
- `app/Models/TrailPhoto.php`
- Update `app/Models/Trail.php` — add relationships.

**Controllers**
- `app/Http/Controllers/Api/TrailPhotoController.php` — `store`, `index`.
- `app/Http/Controllers/Admin/AdminTrailPhotoController.php` — `index`, `update` (approve/reject), `destroy`, optional `bulkUpdate`.

**Form Requests**
- `app/Http/Requests/StoreTrailPhotoRequest.php`

**Resources**
- `app/Http/Resources/TrailPhotoResource.php`

**Notifications**
- `app/Notifications/NewTrailPhotoSubmitted.php`

**Routes**
- `routes/api.php` — `POST /trail-photos`, `GET /trail-photos`.
- `routes/web.php` — admin routes under existing `/admin` group.

**Views**
- `resources/views/admin/trail-photos/index.blade.php`
- `resources/views/components/trail-photo-upload-modal.blade.php` (shared partial)
- Updates to `resources/views/map.blade.php` to mount the modal + carousel.

**Factory / Seeder**
- `database/factories/TrailPhotoFactory.php`
- (Optional) seed a handful of approved/pending rows for local dev.

**Tests**
- `tests/Feature/TrailPhotoSubmissionTest.php` — validation, storage, pending status, notification dispatch.
- `tests/Feature/Admin/TrailPhotoModerationTest.php` — approve/reject, auth gating, audit fields.
- `tests/Feature/Api/PublicTrailPhotosTest.php` — only approved returned, ordering, empty state.

---

## Decisions (locked in 2026-05-24)

1. **Security:** required email (not displayed) + **Google reCAPTCHA v3** (invisible, score threshold 0.5) + per-IP/per-email rate limits + honeypot + admin approval. No email verification loop. Privacy tradeoff (Google cookies) accepted.
2. **Carousel placement:** trail details page only (`trails/show.blade.php`) for v1. Map popup deferred.
3. **Cropping:** Cropper.js on the client, locked to **16:9** aspect ratio. Server re-encodes to WebP and strips EXIF.
4. **Notifications:** queued mail to every user with `is_admin = true`.
5. **Scope:** applies to both `location_type = trail` and `location_type = fishing_lake` (same model, same UI).

## New dependencies to confirm before Phase 1

- `intervention/image` (composer) — server-side resize + re-encode + EXIF strip.
- `cropperjs` (npm) — client-side crop with fixed aspect ratio.
- Google reCAPTCHA v3 site → `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`, optional `RECAPTCHA_MIN_SCORE` (default `0.5`) in `.env`.
