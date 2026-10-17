/** Shape shared by every Where to watch tile: layout, radius, border width and transition. */
export const WATCH_TILE_SHAPE = 'flex rounded-xl border transition-colors';

/** Colours for an ordinary provider tile. */
export const WATCH_TILE_NEUTRAL = 'border-gray-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/60';

/** Colours for the affiliate tile: the same shape, highlighted in primary, including on hover. */
export const WATCH_TILE_FEATURED = 'border-primary-500 bg-primary-50 hover:bg-primary-100 '
    + 'dark:border-primary-500 dark:bg-primary-950/40 dark:hover:bg-primary-950/60';

/** The link fills the tile and carries the padding, so the whole tile is clickable. */
export const WATCH_TILE_LINK = 'flex min-w-0 flex-1 items-center gap-3 p-3';
