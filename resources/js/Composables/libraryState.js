/**
 * Library state rules shared by the client: the same rules the server applies in
 * App\Actions\UpdateLibraryItem. Pure functions only, so they can be tested with Node.
 */

export const STATUS = Object.freeze({TO_WATCH: 'to_watch', WATCHED: 'watched'});
export const REACTIONS = Object.freeze(['liked', 'disliked']);

const toIsoString = (date) => date.toISOString();

export function toLibraryItem(movie, now = new Date()) {
    return {
        imdb_id: movie.imdb_id,
        title: movie.title,
        poster: movie.poster ?? null,
        year: movie.year != null ? String(movie.year) : null,
        status: STATUS.TO_WATCH,
        reaction: null,
        watched_at: null,
        saved_at: toIsoString(now),
    };
}

/**
 * Maps a row from the version 1 "watchlist" store (or the older localStorage list) to a library item.
 */
export function upgradeLegacyItem(row) {
    const savedAt = typeof row.saved_at === 'number'
        ? new Date(row.saved_at)
        : new Date(row.saved_at ?? 0);

    return {
        ...toLibraryItem(row, Number.isNaN(savedAt.getTime()) ? new Date(0) : savedAt),
    };
}

function assertReaction(reaction) {
    if (reaction !== null && !REACTIONS.includes(reaction)) {
        throw new Error(`Unknown reaction "${reaction}"`);
    }
}

export function applyLibraryChange(item, change, now = new Date()) {
    switch (change.type) {
        case 'add-to-watchlist':
            return {...item, status: STATUS.TO_WATCH, reaction: null, watched_at: null};

        case 'mark-watched': {
            const reaction = change.reaction ?? null;
            assertReaction(reaction);
            const isAlreadyWatched = item.status === STATUS.WATCHED && item.watched_at;
            return {
                ...item,
                status: STATUS.WATCHED,
                reaction,
                watched_at: isAlreadyWatched ? item.watched_at : toIsoString(now),
            };
        }

        case 'set-reaction': {
            if (item.status !== STATUS.WATCHED) {
                throw new Error('A reaction needs the title to be watched');
            }
            const reaction = change.reaction ?? null;
            assertReaction(reaction);
            return {...item, reaction};
        }

        default:
            throw new Error(`Unknown library change "${change.type}"`);
    }
}

const newestFirst = (key) => (a, b) => Date.parse(b[key] ?? 0) - Date.parse(a[key] ?? 0);

export function splitLibrary(items) {
    return {
        toWatch: items.filter((item) => item.status === STATUS.TO_WATCH).sort(newestFirst('saved_at')),
        watched: items.filter((item) => item.status === STATUS.WATCHED).sort(newestFirst('watched_at')),
    };
}

export const IMPORT_CHUNK_SIZE = 500;

const IMDB_ID_PATTERN = /^tt\d{7,8}$/;
const TITLE_MAX = 255;
const YEAR_MAX = 10;
const POSTER_MAX = 500;

const truncate = (text, max) => Array.from(text).slice(0, max).join('');
const withinLimit = (value, max) => (value != null && String(value).length <= max ? String(value) : null);

/**
 * Shapes the guest library for POST /library/import so one bad row cannot fail the whole import:
 * rows the server would reject are dropped or trimmed to fit its rules, and duplicates are removed.
 */
export function prepareGuestImport(items) {
    const seen = new Set();

    return items.flatMap((item) => {
        const imdbId = String(item?.imdb_id ?? '').toLowerCase();
        const title = typeof item?.title === 'string' ? truncate(item.title.trim(), TITLE_MAX) : '';
        if (!IMDB_ID_PATTERN.test(imdbId) || title === '' || seen.has(imdbId)) {
            return [];
        }
        seen.add(imdbId);

        const isWatched = item.status === STATUS.WATCHED;
        const watchedAt = isWatched && item.watched_at && !Number.isNaN(Date.parse(item.watched_at))
            ? item.watched_at
            : null;

        return [{
            imdb_id: imdbId,
            title,
            poster: withinLimit(item.poster, POSTER_MAX),
            year: withinLimit(item.year, YEAR_MAX),
            status: isWatched ? STATUS.WATCHED : STATUS.TO_WATCH,
            reaction: isWatched && REACTIONS.includes(item.reaction) ? item.reaction : null,
            watched_at: watchedAt,
        }];
    });
}

export function chunk(items, size = IMPORT_CHUNK_SIZE) {
    const chunks = [];
    for (let index = 0; index < items.length; index += size) {
        chunks.push(items.slice(index, index + size));
    }
    return chunks;
}
