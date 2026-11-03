import {shareOrCopyLink} from './useFileSharing.js';
import {showToast} from './toast.js';

/**
 * Shares a title through the native share sheet, or copies its link where there is none,
 * and says which happened through the toast.
 *
 * @param {{title: string, url: string}} item
 * @param {{nav?: Navigator, notify?: (message: string, level: string) => void}} [deps] seams for tests
 * @returns {Promise<'shared'|'copied'|'cancelled'|'failed'>}
 */
export async function shareTitle({title, url}, {nav = navigator, notify = showToast} = {}) {
    try {
        const outcome = await shareOrCopyLink({url, title, text: `Check out ${title} on Movie Guru!`}, nav);
        if (outcome === 'copied') {
            notify('Link copied', 'success');
        }
        return outcome;
    } catch {
        notify('Could not share or copy the link', 'danger');
        return 'failed';
    }
}

/**
 * Shares a movie from a card or the quick view. Cards pass search rows (title, imdb_id),
 * the quick view passes OMDb detail (Title, imdbID).
 */
export function shareMovie(movie) {
    if (!movie) return;
    navigator.vibrate?.(10);
    shareTitle({
        title: movie.Title ?? movie.title,
        url: route('movie.detail.full', {imdbID: movie.imdbID ?? movie.imdb_id}),
    });
}
