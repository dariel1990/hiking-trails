# Plan — "Sign in with Google" on the hiking-trails web frontend

## Context
The hiking-trails Laravel app already has Google sign-in for the **mobile app** via
`POST /api/auth/google` (verifies a Google **ID token** with `google/apiclient`).
This plan adds Google sign-in for **browser users** on the web frontend.

The web frontend is **Blade + Alpine.js + Tailwind v3**, with **session-based** auth
(`web` guard). There is currently **no user-facing login** — only `/admin/login`.
For a server-rendered Blade site, the right approach is the **OAuth redirect flow via
Laravel Socialite**, NOT the mobile ID-token method.

### Already in place (reuse — do NOT redo)
- `users` table has `google_id` (nullable, unique) and **nullable** `password`
  (migration `2026_06_04_005841_add_google_id_to_users_table.php`).
- `User` model `$fillable` already includes `google_id`.
- `google/apiclient` + `google/auth` installed.
- `config/services.php` has a `google` block with `web_client_id` (from env `GOOGLE_WEB_CLIENT_ID`).
- The same **Web OAuth client** (`435833372142-...apps.googleusercontent.com`) can be reused —
  it just needs redirect URIs + the client secret added.

## Approach: Laravel Socialite (redirect flow)
User clicks "Sign in with Google" → redirected to Google → Google redirects back to
`/auth/google/callback` → we `updateOrCreate` the user by email, set `google_id`, then
`Auth::login()` to establish a normal web session.

## Google Cloud Console changes (do first)
On the existing **Web** OAuth client (Google Auth Platform → Clients → the Web application):
1. Add **Authorized redirect URIs**:
   - `http://localhost:8000/auth/google/callback` (local testing)
   - `https://trails.xploresmithers.com/auth/google/callback` (production)
2. Copy the client's **Client secret** (Web clients have one) — needed for Socialite.

## Backend changes

### 1. Install Socialite
```
composer require laravel/socialite
```
(Confirm it resolves under PHP 8.2 — composer.json pins `config.platform.php = 8.2.30`.)

### 2. `config/services.php` — extend the existing `google` block
```php
'google' => [
    'web_client_id' => env('GOOGLE_WEB_CLIENT_ID'),   // existing
    'client_id'     => env('GOOGLE_WEB_CLIENT_ID'),    // Socialite reads client_id
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect'      => env('GOOGLE_REDIRECT_URI'),
],
```
`.env` (local) + production `.env`:
```
GOOGLE_CLIENT_SECRET=<from console>
GOOGLE_REDIRECT_URI=https://trails.xploresmithers.com/auth/google/callback
# local uses http://localhost:8000/auth/google/callback
```

### 3. Routes — `routes/web.php` (web middleware = sessions)
```php
Route::get('/auth/google/redirect', [WebGoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [WebGoogleAuthController::class, 'callback'])->name('google.callback');
Route::post('/logout', [WebGoogleAuthController::class, 'logout'])->name('logout'); // if no web logout exists yet
```
Note: `/login` currently redirects to `admin.login` — leave that as-is; this is a separate flow.

### 4. Controller — `app/Http/Controllers/Auth/WebGoogleAuthController.php` (new)
- `redirect()` → `return Socialite::driver('google')->redirect();`
- `callback()`:
  - `$googleUser = Socialite::driver('google')->user();` (wrap in try/catch → on failure redirect back with an error)
  - `$user = User::updateOrCreate(['email' => $googleUser->getEmail()], ['name' => $googleUser->getName() ?: $googleUser->getEmail(), 'google_id' => $googleUser->getId()]);`
  - `Auth::login($user, remember: true);`
  - `return redirect()->intended('/');`
- `logout()` → `Auth::logout()` + invalidate session + regenerate token → redirect `/`.
- **Mirror the existing mobile logic** in `app/Http/Controllers/Api/AuthController.php@googleSignIn` for the user-creation part (same `updateOrCreate` by email).

## Frontend changes (Blade + Tailwind)

### 5. Nav — `resources/views/layouts/public.blade.php`
In the `@guest` block add a **"Sign in"** link/button → `route('google.redirect')`.
In `@auth` add the user's name + a **Log out** form (POST to `route('logout')`).

### 6. (Optional) Login landing page
If you want a dedicated page rather than a nav button, add
`resources/views/auth/google-login.blade.php` extending `layouts.public`, styled to match
the premium `resources/views/admin/login.blade.php` (gradients/topographic SVG), with one
**"Sign in with Google"** button (white card, Google "G" logo, Tailwind `.btn-*` classes).
Use the brand palette (`primary` #2C5F5D, `accent` #E87B35).

## Decisions to confirm before building
1. **Where do web users land after login?** There's no user dashboard on web — default to
   home `/` with the nav showing their name. (Recommended.)
2. **Button placement:** nav-only button, or a dedicated `/login` user page too? (`/login`
   is currently the admin redirect — if you want a public login page, use a different path
   like `/account/login` to avoid clashing with admin.)
3. **Admin vs regular users:** Google sign-in creates **non-admin** users (`is_admin` stays
   false). Confirm Google users should NOT get admin access (they won't by default).

## Edge cases to handle
- Email already exists as an admin / password user → `updateOrCreate` by email **links**
  `google_id`, keeps existing `is_admin`. No duplicate.
- User denies consent / Google error on callback → catch, redirect back with a friendly flash error.
- CSRF: redirect flow is GET (Socialite handles `state`); the logout POST needs `@csrf`.
- Session fixation: regenerate session on login (Socialite + `Auth::login` handles; ensure
  `$request->session()->regenerate()` if doing manual login).

## Testing (local first)
1. `php artisan migrate` (google_id migration — likely already run locally).
2. `php artisan serve` → visit site → click "Sign in" → Google consent → back to `/` logged in.
3. Verify a new email creates a user row with `google_id`; an existing email links without duplicate.
4. Log out → session cleared, nav shows "Sign in" again.
5. Confirm `/admin/login` still works independently (no regression).

## Production deploy (after local testing passes)
On Hostinger SSH (`public_html`):
1. Deploy updated `composer.json` + `composer.lock` → `composer install --no-dev --optimize-autoloader`.
2. Add `GOOGLE_CLIENT_SECRET` + `GOOGLE_REDIRECT_URI` (prod URL) to production `.env`.
3. `php artisan migrate --force` (if google_id migration not yet on prod).
4. `php artisan config:clear && php artisan config:cache && php artisan route:cache`.
5. Confirm the prod redirect URI is registered in the Google Console Web client.
6. Smoke-test sign-in on the live site.

## Files this will touch (keep scope tight)
- `composer.json` / `composer.lock` (Socialite)
- `config/services.php`
- `routes/web.php`
- `app/Http/Controllers/Auth/WebGoogleAuthController.php` (new)
- `resources/views/layouts/public.blade.php` (+ optional new login blade)
- `.env` (local) and production `.env`
