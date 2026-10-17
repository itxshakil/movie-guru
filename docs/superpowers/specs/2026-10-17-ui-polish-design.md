# UI polish design

Date: 2026-10-17
Status: approved in conversation, awaiting written spec review
Branch: a new branch off `main`

## Context and goal

The live site works, but it looks unfinished.
A review of https://movieguru.shakiltech.com on desktop (1440px) and an emulated phone (390px), plus an inventory of `resources/js`, found:

- Broken pieces:
  - The home search "Explore" button sticks out of the search box, and the box shows a doubled outline.
  - The home hero heading and search box sit off the centre line of the subtitle.
  - Every icon PNG has a black frame baked in around the purple tile, which shows in the header, the footer and the installed app icon.
  - On the detail page, the Watchlist / Watched? / Share / Top Rated pills cover the top of the poster.
  - Movie cards show an empty blue type pill when a title has no type, and clip the descenders of the title.
  - Clicking a card on desktop (1024px and wider) opens the detail page in a new tab (`SearchCard.vue` `openDetail` calls `window.open(..., '_blank')`).
  - A first visit shows both "The app has been updated" and "The app is now available offline".
- Things that read as amateur:
  - Only Figtree 400, 500 and 600 are loaded, but the UI uses 700, 800 and 900, so the browser fakes them.
  - Emoji decorate most headings and some buttons.
  - There are at least 15 hand-rolled button styles beside 4 shared button components, and the shared ones are uppercase with wide tracking.
  - There are at least 6 text input styles; `TextInput` has dark text on a dark background when focused.
  - Card text goes down to 8px, 9px and 10px.
  - Section headings are copy-pasted in three different patterns.
  - Radius, spacing and surfaces differ from section to section.
  - The header has no navigation: Mood, Movie Match and Hidden Gem are not linked from the header or the footer, and phones see only the logo and a bookmark icon.

Goal: keep the current layout and features, fix every broken piece, and make the whole UI consistent through a small set of shared components and tokens.

### Decisions made with the user

- Scope is a **polish system**, not a redesign: same pages, sections and features.
- The logo becomes a **rounded purple tile**: the black frame is cropped out and all icon sizes are regenerated.
- On movie cards, **Watchlist and Watched become icon buttons on the poster's top-right corner**.
- A card click opens the detail page **in the same tab on desktop**; the mobile quick-view sheet stays.
- The local `.env` is switched to SQLite so the real site can be run and screenshotted (done; not committed).

### Success criteria

- No element overflows its container at 390px or 1440px on any public page, in light or dark mode.
- The home hero heading, subtitle, search box and hot searches share one centre line.
- No black frame around the logo or any app icon.
- Every button on a public page renders through `UiButton`, every section heading through `SectionHeading`, and every text field through `TextInput` or `SelectInput`.
- No font weight is synthesized: every weight used is loaded.
- No decorative emoji in headings, labels or buttons.
- Mood, Movie Match and Watchlist are reachable from the header on desktop and from a bottom tab bar on phones.
- A first visit shows at most one service worker toast.

## Section 1: Foundations

### Type

- Load Figtree 400, 500, 600, 700 and 800 from Bunny Fonts in `resources/views/app.blade.php`.
- Replace `font-black` (900) with `font-extrabold` (800) or `font-bold` (700).
- Type scale:

| Role | Classes |
| --- | --- |
| Page title (h1) | `text-3xl sm:text-4xl font-bold tracking-tight` |
| Home hero title | `text-4xl sm:text-6xl font-extrabold tracking-tight` |
| Section title (h2) | `text-2xl font-bold tracking-tight` |
| Section subtitle | `text-sm sm:text-base text-gray-500 dark:text-gray-400` |
| Card title | `text-base font-semibold` |
| Body | `text-sm` or `text-base` |
| Label and meta | `text-xs font-medium`, never below 11px |

- No text smaller than 11px anywhere.

### Shared components

New, in `resources/js/Components/Ui/`:

- `UiButton.vue`
  - Props: `variant` (`primary`, `secondary`, `ghost`, `danger`), `size` (`sm`, `md`, `lg`), `href` (renders an Inertia `Link` when set, otherwise a `<button>`), `type`, `loading`, `disabled`, `block` (full width).
  - One radius (`rounded-xl`), sentence case, the existing `press-spring` effect, a visible focus ring and a spinner while `loading`.
  - Replaces `PrimaryButton`, `SecondaryButton`, `DangerButton`, `LoadingSpinnerButton` and the inline button styles; the old components are deleted once unused.
- `SectionHeading.vue`
  - Props: `title`, `subtitle` (optional), `as` (`h2` default), `align` (`left` default, `center`).
  - Slot `action` for a right-side control such as "Another gem" or "See all".
  - Replaces the centred blue eyebrow plus big paragraph pattern and the ad hoc h2s.
- `UiChip.vue`
  - Props: `as` (`button`, `a`, `span`), `active`, `size` (`sm`, `md`).
  - Used for hot searches, genres and picked titles.
- `SelectInput.vue`, styled like `TextInput`.
- `TextInput.vue` is fixed in place: one border, one background, one focus ring, and readable text in both themes.
  `InputLabel` keeps its API; the auth pages' uppercase override is removed.

### Surfaces and spacing

- One card surface: `rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900`.
- Inner panels (for example the Movie Match share URL): `rounded-xl bg-gray-50 dark:bg-gray-800/60`.
- Sections: `py-12 sm:py-16`, content width `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`.
- Grid gaps: `gap-6` for card grids.

### Emoji and colour

- Decorative emoji are removed from headings, labels and buttons.
  Where a cue helps, a small line icon in the existing SVG style is used.
- Emoji stay where they carry meaning: the like and dislike reactions, the mood tile icons from the server, and notification, share and calendar text.
- The blue `primary` palette stays.
  The `secondary` and `ternary` palettes are removed from `app.css` once nothing uses them.

## Section 2: Header, logo, navigation and home hero

### Logo and icons

- Crop the black frame from `public/icons/ios/1024.png` (the purple tile spans 76 to 946 in both axes) and regenerate every PNG under `public/icons/ios`, `public/icons/android` and `public/icons/windows11`, plus `public/favicon.ico`, at their current sizes.
- The regeneration is a script in `scripts/` so it can be rerun; it reads the frame bounds instead of hard-coding them.
- `ApplicationLogo.vue` renders the tile at the size its caller sets, with `rounded-[22%]` corners and `dark:ring-1 dark:ring-white/10`.
- The manifest icons keep their paths, so `public/app.webmanifest` does not change.

### Header

- Logo tile plus "Movie Guru" on every screen size.
- Desktop (`md` and up): links Discover (home), Mood, Movie Match and Watchlist with its count badge; the current page is highlighted with `aria-current="page"`.
- The Window Controls Overlay search keeps working.

### Bottom tab bar on phones

- New `AppTabBar.vue`, shown below `md`: Home, Mood, Match and Watchlist, each an icon over a short label, at least 48px tall, with `aria-current` on the active tab.
- It sits above the safe area (`env(safe-area-inset-bottom)`), and pages get matching bottom padding so nothing hides behind it.
- Toasts, the reaction prompt, the install banner and the detail page's sticky "Where to watch" button move up so they clear the tab bar.

### Home hero

- One centred column for the heading, subtitle, search and hot searches.
- Heading: "Discover. Explore. Enjoy." without emoji.
- The search box is one bordered pill whose children are flex items, not absolutely positioned: search icon, input, mic or clear button, and the Explore button.
  It has one focus ring on the pill (`focus-within`), not a ring on the input plus a border.
- On phones the Explore button shows only the arrow or search icon, as today.
- Hot searches: a "Hot searches" label and `UiChip`s, centred and wrapping.

### Hidden Gem

- From `md` up: a wide featured card with the poster on the left and the title, meta, plot and actions on the right.
- On phones: the normal movie card.
- "Another gem" moves into the `SectionHeading` action slot.

### First-visit toasts

- Reproduce in a clean browser profile first.
- Expected: a first install shows only "The app is now available offline".
- "The app has been updated" shows only when a previous service worker version controlled the page.

### Ad offerwall

The "Unlock more content" dialog is Google AdSense's offerwall, configured in the AdSense dashboard, not in code.
It is out of scope; the user may turn it off or delay it there.

## Section 3: Cards and the detail page

### Movie card (`SearchCard.vue`)

- Poster in a real 2:3 frame (`aspect-2/3`; the current `_aspect-2/3` is a typo).
- Top-right of the poster: two round icon buttons on a dark glass circle (`bg-black/55 backdrop-blur`), each at least 36px:
  - Watchlist: bookmark outline, filled when saved.
  - Watched: eye; check plus the reaction emoji when watched.
    It opens the existing Like / Dislike / Not watched popover.
  - Both have `aria-label`s that name the title and `aria-pressed`.
- Body, in order:
  - Type badge, only when the title has a type.
  - Title, `text-base font-semibold`, two lines at most, with enough line height that descenders are not clipped.
  - Meta line: year, type and star rating, for example "2021 · Movie · ★ 4.0".
  - Director on one line.
  - At most two genre chips (`UiChip size="sm"`).
- One action row: "Where to watch" (`UiButton` secondary) and "Details" (`UiButton` primary).
- The whole card is an Inertia `Link` to the detail page:
  - Desktop: plain click navigates in the same tab; Ctrl, Cmd and middle click open a new tab.
  - Below 1024px: a plain click opens the existing quick-view sheet, as today.
  - The prefetch-on-press behaviour stays.
- `WatchlistButton.vue` and `WatchedButton.vue` get an `icon` presentation used by the card, so the card no longer overrides them with `!important` classes.
  Their existing labelled presentation stays for the detail page.
- Compact grids (`--card-density: compact`) use tighter padding but the same structure.

### Poster tiles

The Watchlist and Mood poster tiles use the same radius and the same corner icon buttons as the card.

### Detail page (`DetailCard.vue`)

- The poster shows no buttons.
  A status badge (Trending, New, Top Rated or Hidden Gem) sits in its bottom-left corner.
- Under the title, one wrapping action row: Watchlist, Watched, Share and Share as image, plus Add to calendar for unreleased titles, as `UiButton` secondary and ghost buttons.
- Title `text-3xl font-bold`, then a meta line ("2010 · Adventure, Sci-Fi, Thriller · 2h 28m · PG-13") and the star rating with the review count.
- Where to watch: every provider tile has the same radius, border, padding and height.
  The affiliate tile keeps a primary border but has the same shape.
- Plot: in full on desktop, clamped with "Read more" on phones.
- Ratings: IMDb, Rotten Tomatoes and Metacritic as three stat tiles in a row, score large and source small.
- Details: Director, Writer, Cast, Awards, Country and Box office as a two-column definition list (`dl`), label in gray, value in body text, no stripes.
- The trailer section and the related sections (Fresh releases, Recommended, More like this) use `SectionHeading`, left-aligned, without emoji.

## Section 4: Other pages

- Search results:
  - The search bar matches the home pill.
  - Type and year filters are one row of `SelectInput` and `TextInput`.
  - Heading "41 results for 'inception'" (or "No results for ...").
  - "Search" and "Load more" are `UiButton`s.
  - The install box is a normal card.
- Watchlist: title without emoji; tabs, empty, error and skeleton states and poster tiles use the shared styles.
- Mood: mood tiles use the card surface and keep their emoji icons; results use the shared poster tile.
- Movie Match: no emoji header or headings; one card style; all buttons are `UiButton`, with WhatsApp kept green.
- Contact, Error, Terms and Privacy: shared headings, cards and buttons; the Error page's `dark:texxt-white` typo is fixed.
- Auth pages and Profile: `UiButton` and the fixed `TextInput`; no uppercase buttons or labels.
- Footer: new logo, links to Mood, Movie Match and Watchlist, and the copyright year from the current date.
- Newsletter form and widget: one style; the input outline is visible on light backgrounds.

## Error handling and accessibility

- Every icon-only button has an `aria-label`; toggles use `aria-pressed`.
- Focus rings stay visible on every interactive element, including the card link.
- The tab bar and header links use `aria-current="page"`.
- Colour contrast: body text and labels meet WCAG AA in both themes.
- Reduced motion keeps disabling the press and entrance animations.

## Testing and verification

- Before and after screenshots of every public page at 390px (touch emulation) and 1440px, in light and dark, using a DevTools-protocol screenshot script against the local site.
  Each page is also checked programmatically for horizontal overflow (`scrollWidth > innerWidth`).
- The search and detail pages need a real OMDb key in the local `.env`; without one they return the app's 503 fallback and are checked against the live site instead.
- Pest feature tests for any server change (for example the footer year or a new route), and the existing suite stays green.
- Node tests (`npm run test:js`) for logic in new components, for example the `UiButton` link-or-button choice if it is extracted into a function.
- `composer lint`, `php artisan test --compact`, `npm run test:js` and `npm run build` pass before every commit.

## Out of scope

- New features, new pages or changes to page structure beyond what is listed.
- A new colour palette or font.
- The AdSense offerwall.
- Server push notifications (issue #23).
