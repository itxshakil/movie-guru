# Changelog

All notable changes to Movie Guru are documented here.

## [Unreleased]

### Auth — Apple-like Split Layout

- **GuestLayout.vue** — Replaced the simple centered card with a full-screen split layout: cinematic movie-poster mosaic
  left panel (6 TMDB posters, dark gradient overlay, brand quote) + clean white right panel with large heading,
  subtitle, and minimal form slot. Mobile shows logo only.
- **Login.vue** — Passes `title="Welcome back"` + `subtitle` to GuestLayout. Redesigned form: `space-y-5`, `rounded-xl`
  inputs with `focus:ring-2`, inline "Forgot password?" link, full-width "Sign in" button, and "Don't have an account?"
  footer link.
- **Register.vue** — Passes `title="Create account"`. Same clean form style with full-width "Create account" button
  and "Already have an account?" footer link.
- **ForgotPassword.vue** — Passes `title="Reset password"`. Simplified to single email field + full-width "Send reset
  link" button.
- **ResetPassword.vue** — Passes `title="Choose new password"`. Clean three-field form with full-width "Reset password"
  button.
- **ConfirmPassword.vue** — Passes `title="Confirm your password"` with subtitle. Single password field + full-width "
  Confirm" button.
- **VerifyEmail.vue** — Passes `title="Verify your email"`. Info box + full-width "Resend verification email" button +
  plain "Log out" link.

### Watchlist — DB-backed (Authenticated)

- **Migration** `create_watchlists_table` — `user_id`, `imdb_id`, `title`, `poster`, `year` columns; unique constraint
  on `(user_id, imdb_id)`; index on `user_id`.
- **Watchlist model** — `fillable`, `belongsTo(User)`.
- **WatchlistStoreRequest** — Validates `imdb_id`, `title`, `poster`, `year`.
- **WatchlistController** — `index` (Inertia `WatchlistPage`), `store` (firstOrCreate), `destroy` (delete by imdb_id).
- **WatchlistPolicy** — `viewAny`, `create` (auth), `delete` (owner only).
- **WatchlistPage.vue** — New Inertia page showing DB watchlist in a clean grid with remove button; served at
  `/my-watchlist`.
- **routes/web.php** — Added `GET/POST/DELETE /my-watchlist` under `auth` middleware.

### Movie Match

- **Migration** `create_movie_match_sessions_table` — `token`, `creator_picks` (JSON), `partner_picks` (JSON),
  `matched` (JSON), `expires_at`.
- **MovieMatchSession model** — Casts JSON columns, `isExpired()` helper.
- **MovieMatchController** — `create` (render page), `store` (create session + redirect), `show` (load session),
  `submit` (partner picks + compute intersection).
- **MovieMatch.vue** — Shareable link flow: creator picks IMDb IDs → gets share link → partner submits picks → both see
  matched overlap. Clean minimal UI with states for create/waiting/partner-submit/results.
- **routes/web.php** — `GET /movie-match`, `POST /movie-match`, `GET /movie-match/{token}`,
  `POST /movie-match/{token}/submit`.

### Mood Discovery

- **MoodDiscoveryController** — Maps 5 mood slugs (`cozy`, `heartbroken`, `hyped`, `bored`, `adventurous`) to genre
  queries against `movie_details`; returns top 24 rated matches.
- **MoodDiscovery.vue** — Clean mood-picker grid with emoji cards; results grid below; shareable URL per mood.
- **routes/web.php** — `GET /mood`, `GET /mood/{mood}`.

### Hidden Gem of the Day

- **HiddenGemController** — Returns a daily-stable hidden gem (rating ≥ 7.0, votes ≤ 50k) seeded by date via
  `RAND(Ymd)`, cached until end of day.
- **HiddenGem.vue** — Async-fetched component with animated skeleton loader; shows poster, title, year, rating, genre,
  director; links to detail page.
- **Welcome.vue** — HiddenGem component added before OurFeatures section.
- **routes/web.php** — `GET /hidden-gem` (throttled).

### Newsletter

- **emails/newsletter/welcome.blade.php** — Rewritten as raw HTML with Apple-inspired inline CSS: logo header, white
  card, feature list rows, pill CTA button, minimal footer with unsubscribe link. Switched `WelcomeSubscriberMail` from
  `markdown:` to `view:`.
- **emails/newsletter/weekly-monthly.blade.php** — Rewritten as raw HTML: logo header, hero card with digest title,
  optional hidden gem highlight card, movie list cards (poster + title + plot + pill CTA), browse-all CTA, minimal
  footer. Switched `NewsletterMail` from `markdown:` to `view:`.
- **NewsletterWidget.vue** — New compact inline subscription widget (first name + email + subscribe button) for
  embedding on detail pages. Uses same `subscribe` route and BroadcastChannel toast.
- **Show.vue** — NewsletterWidget added inline after DetailCard (before movie sections).

### Native Browser APIs

- **Dropdown.vue** — Replaced JS overlay, escape listener, and open/close state with the native Popover API (
  `popover="auto"` + `popovertarget`). Added `@starting-style` enter animation and scoped CSS transitions.
- **Modal.vue** — Replaced manual focus trap, `overflow: hidden` body lock, and escape key listener with a native
  `<dialog>` element using `showModal()` / `close()`. Gained built-in `::backdrop`, focus trap, and `@cancel` (Escape)
  handling for free.
- **app.js** — Wired Inertia `router.on('navigate')` to `document.startViewTransition` for silky, GPU-accelerated
  page-to-page transitions with no extra JavaScript.
- **ToastNotifications.vue** — Replaced Vue `<TransitionGroup>` with a plain `<div>` and CSS `@starting-style` +
  `transition` for JS-free, GPU-accelerated enter/leave animations.

### Design — Apple-like Polish

- **SearchCard.vue** — Initial redesign added frosted glass hover overlay, `scale` + `shadow` transitions, refined
  `aspect-[2/3]` poster, clean typographic hierarchy with `font-feature-settings`, and a hover-reveal share button.
  Subsequently simplified to a clean, uncluttered design: removed frosted glass overlay, hover-reveal share button,
  translate/scale/shadow micro-interactions, and `active:scale` states — keeping only a subtle `hover:border-gray-200`
  border transition.
- **DetailCard.vue** — Initial redesign added a cinematic blurred backdrop poster, frosted glass gradient overlay,
  `@keyframes panel-reveal` section animation, refined title/meta layout, and polished source cards. Subsequently
  simplified: removed blurred backdrop poster, poster scale on hover, affiliate link card scale/shadow animations,
  sticky CTA `active:scale`, and the `detail-panel` reveal animation entirely.
- **app.css** — Added frosted glass sticky navbar (`backdrop-filter: saturate(180%) blur(20px)`), global
  `font-feature-settings` + `-webkit-font-smoothing: antialiased`, smooth focus rings, and input micro-interactions.
  Removed `@keyframes panel-reveal` and `.detail-panel` rule after design simplification.

### Service Worker

- **public/sw.js** — Multiple improvements for correctness and UX:
    - Added `isInertiaRequest()` helper to bypass cache entirely for `X-Inertia` and `X-Inertia-Partial-Data` requests,
      always fetching from network for Inertia AJAX calls.
    - Tag stored full-page responses with a `X-SW-Request-Type: full` synthetic header; on cache hit, evict and re-fetch
      if the request type mismatches the cached response type.
    - Implemented stale-while-revalidate strategy for `/search` and `/i/` routes: return cached response immediately,
      then silently re-fetch and update the cache in the background.
    - Deduplicated two `BroadcastChannel` instances into one shared `channel` on `service-worker-channel`.
    - Fixed `activate` handler to preserve all current caches (`ALL_CACHES`) instead of only `APP_CACHE`.
    - Added `notificationclose` event tracking.
    - Enriched all notification types (`offlineSyncRequest`, `dailyNotification`, `sendTrendingNotification`) with
      `image`, local-path `badge`, structured `actions`, `vibrate` patterns, and `tag` / `renotify`.
    - Bumped cache version to `v-4.15.3` to force a clean re-install.
    - Added separate `INERTIA_CACHE` (`inertia-cache-v-4.15.3`) for full Inertia AJAX responses. New
      `isFullInertiaRequest()` helper and `cacheInertiaRequest()` function cache full Inertia requests (tagged
      `X-SW-Request-Type: inertia`, 5-minute TTL, 30 entries max) while partial reloads (`X-Inertia-Partial-Data`) still
      bypass cache entirely and go straight to network.
- **ToastNotifications.vue** — Added a `swChannel` listener on `service-worker-channel` to receive SW toast messages (
  `APP_AVAILABLE_OFFLINE`, `APP_UPDATED`, `OFFLINE_SYNC_FETCHED`, `NOTIFICATION_PERMISSION_DENIED`) that previously
  relied on the now-removed second broadcast channel.
