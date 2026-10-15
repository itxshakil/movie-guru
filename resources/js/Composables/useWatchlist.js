import {useLibrary} from '@/Composables/useLibrary.js';

export {requestPersistentStorage} from '@/Composables/useLibrary.js';

/**
 * The "to watch" side of the library, kept for the header count and WatchlistButton.
 */
export function useWatchlist() {
    const {toWatch, isLibraryLoaded, isInWatchlist, addToWatchlist, remove} = useLibrary();

    return {
        watchlist: toWatch,
        isWatchlistLoaded: isLibraryLoaded,
        isMovieInWatchlist: isInWatchlist,
        toggleMovie: (movie) => (isInWatchlist(movie.imdb_id) ? remove(movie.imdb_id) : addToWatchlist(movie)),
        removeMovie: remove,
    };
}
