import {ref} from 'vue';

const STORAGE_KEY = 'movie-guru-watchlist';

const watchlist = ref(loadWatchlist());

function loadWatchlist() {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '[]');
    } catch {
        return [];
    }
}

function saveWatchlist() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(watchlist.value));
}

export function useWatchlist() {
    const isMovieInWatchlist = (imdbId) => {
        return watchlist.value.some((m) => m.imdb_id === imdbId);
    };

    const toggleMovie = (movie) => {
        const index = watchlist.value.findIndex((m) => m.imdb_id === movie.imdb_id);
        if (index === -1) {
            watchlist.value = [...watchlist.value, movie];
        } else {
            watchlist.value = watchlist.value.filter((m) => m.imdb_id !== movie.imdb_id);
        }
        saveWatchlist();
    };

    const removeMovie = (imdbId) => {
        watchlist.value = watchlist.value.filter((m) => m.imdb_id !== imdbId);
        saveWatchlist();
    };

    return {
        watchlist,
        isMovieInWatchlist,
        toggleMovie,
        removeMovie,
    };
}
