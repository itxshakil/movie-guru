# Watched list design

Date: 2026-10-15
Status: approved in conversation, awaiting written spec review
Branch: `feat/pwa-issue-22` (builds on the unified offline watchlist)

## Context and goal

Movie Guru has a watchlist ("to watch") that works for guests (IndexedDB on the device) and members (stored in the account), merges on sign-in, and works offline.
Users also want to record what they have already watched, and later to match with friends and similar users based on these lists.

This spec covers **piece 1 of 4** only:

1. Watched list (this spec).
2. Movie Match from your lists: start a match from your watchlist; titles either person has watched are excluded.
3. Recommendations from similar users: anonymous, members only.
4. Find people with similar taste: opt-in public profiles and a taste-match score, members only.

Pieces 2 to 4 get their own specs.
This spec only has to make sure the data it stores (want, watched, liked or disliked) is what they need.

### Decisions made with the user

- Marking a title watched records the fact plus an **optional like or dislike** (no star ratings).
- The watched list lives as a **"Watched" tab on the existing Watchlist page**, not a separate page.
- Marking a watchlist title as watched **moves it out of "To watch"**.
- Storage uses **one library with a status** instead of a second, parallel list.

### Success criteria

- A guest and a member can mark any title watched from a card or the detail view, optionally like or dislike it, and see it under "Watched".
- Marking watched removes the title from "To watch" in one step, with no state where it is in both or neither.
- Existing watchlists (member rows and guest IndexedDB or localStorage data) survive the change unchanged, as "To watch".
- Signing in merges a guest library into the account without losing watched state or reactions.
- Both tabs open offline.

## Data model

### Table

A migration renames `watchlists` to `library_items` and adds:

| Column | Type | Notes |
| --- | --- | --- |
| `status` | string, backed by enum `LibraryStatus` (`to_watch`, `watched`) | Not null, default `to_watch`. |
| `reaction` | nullable string, backed by enum `LibraryReaction` (`liked`, `disliked`) | Only allowed when `status = watched`. |
| `watched_at` | nullable timestamp | Set when `status` becomes `watched`, cleared otherwise. |

Existing columns stay: `user_id`, `imdb_id`, `title`, `poster`, `year`, timestamps.

Constraints and indexes:

- Unique `(user_id, imdb_id)`: one row per user and title, so "move to watched" is an in-place update.
  This index already exists on `watchlists` and carries over with the rename.
- Index `(user_id, status)` for the tabs and for the matching queries in pieces 2 to 4.

Existing rows become `status = to_watch`, `reaction = null`, `watched_at = null`.

The migration is reversible: `down()` drops the new columns and indexes and renames the table back. Watched data is lost on rollback; that is acceptable and is noted in the migration.

### Model and authorization

- `App\Models\Watchlist` is renamed to `App\Models\LibraryItem`, with `casts()` for both enums and `watched_at`, a `forUser(User $user)` scope, and the `user()` relationship.
- `WatchlistFactory` becomes `LibraryItemFactory` with `watched()`, `liked()` and `disliked()` states.
- `WatchlistPolicy` becomes `LibraryItemPolicy` (members manage only their own items).

### State rules

These rules live in one place on the server (an Action, `UpdateLibraryItem`) and are mirrored by one pure function on the client:

- `to_watch` to `watched`: set `watched_at = now()`, keep `reaction` if provided.
- `watched` to `to_watch`: clear `reaction` and `watched_at`.
- `reaction` set while not `watched`: validation error (422).
- "Not watched" from the Watched tab deletes the item; re-adding it to the watchlist is a separate action.

## Server API (members)

All endpoints require authentication, use Form Requests, and return API Resources (`LibraryItemResource`: `imdb_id`, `title`, `poster`, `year`, `status`, `reaction`, `watched_at`).

| Method and path | Purpose |
| --- | --- |
| `GET /library` | The member's whole library, newest first. The client splits it into tabs. |
| `PUT /library/{imdbId}` | Upsert one item: `title`, `poster`, `year`, `status`, `reaction`. Applies the state rules. |
| `DELETE /library/{imdbId}` | Remove one item. |
| `POST /library/import` | Merge a guest library on sign-in (max 500 items). |

The `{imdbId}` route parameter is constrained to IMDb id format.

Merge rules for `/library/import`, per title:

- Not in the account: insert as sent.
- In the account as `to_watch`, guest has `watched`: becomes `watched` with the guest's `watched_at` and `reaction`.
- In the account as `watched`: stays `watched`; the account's reaction wins, and the guest's reaction fills it only when the account has none.
- Runs in one database transaction.

Removed or changed routes:

- `GET /my-watchlist` keeps redirecting HTML visits to `/watchlist`, so old links keep working.
- The `watchlist.index` JSON, `watchlist.store`, `watchlist.destroy` and `watchlist.import.store` routes are replaced by the `/library` routes.
  Only the app's own composable calls them, so there are no outside consumers to keep compatible.
- `WatchlistController`, `WatchlistImportController`, `MergeGuestWatchlist`, `WatchlistStoreRequest`, `WatchlistImportRequest` and `WatchlistItemResource` are replaced by `LibraryController`, `LibraryImportController`, `MergeGuestLibrary`, `LibraryItemRequest`, `LibraryImportRequest` and `LibraryItemResource`.

## Client data layer

### `useLibrary.js`

Replaces the internals of `useWatchlist.js`.

- One module-level reactive list of `{imdb_id, title, poster, year, status, reaction, watched_at, saved_at}`.
- Computed `toWatch` (by `saved_at`, newest first) and `watched` (by `watched_at`, newest first).
- Actions: `addToWatchlist(movie)`, `markWatched(movie, reaction?)`, `setReaction(imdbId, reaction|null)`, `unmarkWatched(imdbId)` (deletes), `remove(imdbId)`.
- `isInWatchlist(imdbId)`, `isWatched(imdbId)`, `reactionFor(imdbId)`.
- Status transitions go through one pure `applyLibraryChange(item, change)` function that mirrors the server's state rules.

`useWatchlist()` stays as a thin wrapper over `useLibrary()` (`watchlist` = `toWatch`, `toggleMovie`, `removeMovie`, `isMovieInWatchlist`), so `AppHeader` and `WatchlistButton` keep working.

### Guests: IndexedDB

- The `movie-guru` database goes from version 1 to version 2.
- `onupgradeneeded` from version 1 creates a `library` store (keyPath `imdb_id`), copies every `watchlist` row into it with `status: 'to_watch'`, and deletes the old `watchlist` store, all in the upgrade transaction.
- The existing one-time localStorage migration still runs first, writing into `library` with `status: 'to_watch'`.

### Members

- Writes are optimistic: update the list, call the API, and on failure restore the previous list and show a toast.
- On sign-in, a non-empty guest library is sent to `/library/import`; on success the guest store is cleared and the response becomes the list.
- The "watchlist owner changed" check that resets the offline page cache keeps working unchanged.

### Offline

- The service worker's network-first route for the member list moves from `/my-watchlist` to `/library`.
- `CACHE_WATCHLIST` is renamed `CACHE_LIBRARY` and caches detail pages and posters for both to-watch and watched titles.
  Purging on app update and on owner change stays as it is.
- Offline member writes are out of scope: they fail with the toast "Couldn't sync, try again when you're online".
  Guests always succeed offline because their library is local.

### Header badge

Counts "To watch" items only.

## UI

### Watchlist page (`/watchlist`)

- Two tabs: "To watch (n)" and "Watched (n)".
  They are real links (`/watchlist?tab=watched`) so the back button, bookmarks and a future shortcut work.
  The route stays a plain `Route::inertia` page; the tab is read from the query string on the client.
- To watch: the current grid and remove button, plus a "Mark watched" action on each card.
- Watched: the same grid, newest first, each card showing a 👍 / 👎 toggle and a "Not watched" action in its menu.
- Empty states per tab, for example "Nothing watched yet. Mark titles as watched from any movie card."
- The page `<title>` and description mention both tabs.

### Cards and detail view

A new `WatchedButton.vue` sits next to `WatchlistButton` in `SearchCard` and `DetailCard`.

- Not watched: a "✓ Watched" button.
  One tap marks it watched; a small inline "Did you like it? 👍 👎" prompt appears for about 5 seconds and can be ignored.
- Watched: the button shows the state (and reaction emoji, if any).
  Tapping opens a popover (the existing anchor-positioned popover pattern) with Like, Dislike and "Not watched".
- In compact card grids (`--card-density: compact`) both buttons collapse to icons, like `WatchlistButton` today.
- A watched title's `WatchlistButton` shows as unsaved, since the title moved out of the watchlist.

### Accessibility

- Toggles use `aria-pressed`; labels name the title, for example "Mark Inception as watched", "Like Inception".
- The reaction prompt and popover are keyboard reachable and dismissible with Escape.
- Status changes are announced through the existing toast live region.

## Error handling

- Every member action is optimistic and reverts with a toast on failure.
- 422 responses (for example a reaction on an unwatched item) revert and show the validation message.
- The import endpoint failing on sign-in leaves the guest store intact so it is retried on the next load.

## Testing

Server (Pest feature tests):

- Each `/library` endpoint: success, validation, authentication, and only touching the member's own items.
- State rules: watched stamps `watched_at`; back to `to_watch` clears reaction and `watched_at`; a reaction without watched is rejected.
- Import merge rules, including watched beating to-watch and the account reaction winning.
- The migration: existing `watchlists` rows end up as `to_watch` library items.
- `/my-watchlist` still redirects to `/watchlist`.

Client:

- `applyLibraryChange()` and the IndexedDB version 1 to 2 upgrade mapping are written as pure functions and checked with small Node scripts, since the project has no JS test runner.
- The Watchlist page tabs, `WatchedButton` states and the reaction prompt are checked visually in headless Chrome against the production build, in light and dark mode and at phone width.

Lint and build: `composer lint`, `composer test` and `npm run build` must pass, as they do now.

## Out of scope

- Queuing member writes made while offline.
- Rewatch counts or multiple watch dates per title.
- Star ratings or written reviews.
- Anything social or cross-user (pieces 2 to 4).
