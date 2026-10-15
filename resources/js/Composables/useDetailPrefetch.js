/**
 * Starts the quick-view detail request on pointerdown, so the response is usually on its
 * way before the click that opens the modal even fires. Each prefetched response is used
 * at most once, so a title opened this way is still requested (and counted) exactly once.
 */

const PREFETCH_TTL_MS = 10_000;
const prefetchedDetails = new Map();

export function fetchDetail(imdbId) {
    return fetch(`/i/${imdbId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    });
}

export function prefetchDetail(imdbId) {
    const existing = prefetchedDetails.get(imdbId);
    if (existing && existing.expiresAt > Date.now()) {
        return;
    }

    const response = fetchDetail(imdbId);
    // A failed prefetch is simply not reused; the modal makes its own request instead.
    response.catch(() => prefetchedDetails.delete(imdbId));
    prefetchedDetails.set(imdbId, {response, expiresAt: Date.now() + PREFETCH_TTL_MS});
}

/**
 * Returns the prefetched response promise for this title, or null when there is none.
 */
export function takePrefetchedDetail(imdbId) {
    const entry = prefetchedDetails.get(imdbId);
    prefetchedDetails.delete(imdbId);

    return entry && entry.expiresAt > Date.now() ? entry.response : null;
}
