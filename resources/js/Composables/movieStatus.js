const DAY_MS = 1000 * 60 * 60 * 24;

const hasValue = (value) => Boolean(value) && value !== 'N/A';

const voteCount = (detail) => (hasValue(detail.imdbVotes) ? parseInt(detail.imdbVotes.replaceAll(',', ''), 10) : 0);

const isTopRated = (detail) => {
    if (!hasValue(detail.imdbRating)) {
        return false;
    }
    const rating = Number(detail.imdbRating);
    const votes = voteCount(detail);

    return (rating >= 8.5 && votes > 80_000) || (votes > 100_000 && rating >= 8);
};

const isHiddenGem = (detail) => {
    const votes = voteCount(detail);

    return Number(detail.imdbRating) > 8.5 && votes < 80_000 && votes > 3_000;
};

/** Released (or releasing) within 30 days of today, either side. */
const isRecentlyReleased = (detail, today) => {
    const releaseDate = new Date(detail.Released);
    if (Number.isNaN(releaseDate.getTime())) {
        return false;
    }

    return Math.ceil(Math.abs(today - releaseDate) / DAY_MS) < 30;
};

/**
 * The one status shown on a detail poster: Trending, New, Top rated or Hidden gem (first match), or null.
 *
 * @param {{imdbRating?: string, imdbVotes?: string, Released?: string}} detail OMDb title detail
 * @param {Date} today
 * @returns {string|null}
 */
export function movieStatusBadge(detail, today = new Date()) {
    const topRated = isTopRated(detail);
    const recentlyReleased = isRecentlyReleased(detail, today);

    if (topRated && recentlyReleased) {
        return 'Trending';
    }
    if (recentlyReleased) {
        return 'New';
    }
    if (topRated) {
        return 'Top rated';
    }

    return isHiddenGem(detail) ? 'Hidden gem' : null;
}

const RATING_SOURCE_LABELS = {'Internet Movie Database': 'IMDb'};

/** Short label for an OMDb rating source, e.g. "Internet Movie Database" to "IMDb". */
export function ratingSourceLabel(source) {
    return RATING_SOURCE_LABELS[source] ?? source;
}
