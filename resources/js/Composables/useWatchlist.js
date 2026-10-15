import {ref, watch} from 'vue';
import {usePage} from '@inertiajs/vue3';

/**
 * One watchlist for everyone. Guests keep it on the device in IndexedDB; members keep it in
 * their account, and anything saved as a guest is merged into the account on sign-in.
 * Saved titles are also cached by the service worker so the whole watchlist opens offline.
 */

const LEGACY_STORAGE_KEY = 'movie-guru-watchlist';
const DB_NAME = 'movie-guru';
const DB_VERSION = 1;
const STORE_NAME = 'watchlist';

const watchlist = ref([]);
const isWatchlistLoaded = ref(false);

let loadedForUserId;
let loadingPromise = null;

function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);
        request.onupgradeneeded = () => {
            if (!request.result.objectStoreNames.contains(STORE_NAME)) {
                request.result.createObjectStore(STORE_NAME, {keyPath: 'imdb_id'});
            }
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function runTransaction(mode, operation) {
    const db = await openDatabase();
    try {
        return await new Promise((resolve, reject) => {
            const transaction = db.transaction(STORE_NAME, mode);
            const request = operation(transaction.objectStore(STORE_NAME));
            transaction.oncomplete = () => resolve(request?.result);
            transaction.onerror = () => reject(transaction.error);
            transaction.onabort = () => reject(transaction.error);
        });
    } finally {
        db.close();
    }
}

const readGuestItems = () => runTransaction('readonly', (store) => store.getAll());
const saveGuestItem = (item) => runTransaction('readwrite', (store) => store.put(item));
const deleteGuestItem = (imdbId) => runTransaction('readwrite', (store) => store.delete(imdbId));
const clearGuestItems = () => runTransaction('readwrite', (store) => store.clear());

function toWatchlistItem(movie) {
    return {
        imdb_id: movie.imdb_id,
        title: movie.title,
        poster: movie.poster ?? null,
        year: movie.year != null ? String(movie.year) : null,
        saved_at: movie.saved_at ?? Date.now(),
    };
}

/**
 * Moves a watchlist saved by an older version of the app from localStorage into IndexedDB, once.
 */
async function migrateLegacyWatchlist() {
    let legacyItems;
    try {
        legacyItems = JSON.parse(localStorage.getItem(LEGACY_STORAGE_KEY) ?? '[]');
    } catch {
        legacyItems = [];
    }

    if (!Array.isArray(legacyItems) || legacyItems.length === 0) {
        localStorage.removeItem(LEGACY_STORAGE_KEY);
        return;
    }

    const savedAt = Date.now();
    await runTransaction('readwrite', (store) => {
        legacyItems.forEach((movie, index) => store.put(toWatchlistItem({...movie, saved_at: savedAt - index})));
    });
    localStorage.removeItem(LEGACY_STORAGE_KEY);
}

async function loadGuestWatchlist() {
    await migrateLegacyWatchlist();
    const items = await readGuestItems();
    return items.sort((a, b) => b.saved_at - a.saved_at);
}

function xsrfHeaders() {
    const token = document.cookie
        .split('; ')
        .find((cookie) => cookie.startsWith('XSRF-TOKEN='))
        ?.slice('XSRF-TOKEN='.length);

    return token ? {'X-XSRF-TOKEN': decodeURIComponent(token)} : {};
}

async function requestJson(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...xsrfHeaders(),
            ...options.headers,
        },
    });

    if (!response.ok) {
        throw new Error(`Watchlist request failed with status ${response.status}`);
    }

    return response.json();
}

async function loadMemberWatchlist() {
    const guestItems = await loadGuestWatchlist();

    if (guestItems.length === 0) {
        const {data} = await requestJson(route('watchlist.index'));
        return data;
    }

    const {data} = await requestJson(route('watchlist.import.store'), {
        method: 'POST',
        body: JSON.stringify({items: guestItems.map(({saved_at, ...item}) => item)}),
    });
    await clearGuestItems();
    return data;
}

function loadWatchlist(userId) {
    if (loadedForUserId === userId && loadingPromise) {
        return loadingPromise;
    }

    loadedForUserId = userId;
    loadingPromise = (userId ? loadMemberWatchlist() : loadGuestWatchlist())
        .then((items) => {
            if (loadedForUserId === userId) {
                watchlist.value = items;
                isWatchlistLoaded.value = true;
                cacheForOffline(items, {reset: hasWatchlistOwnerChanged(userId)});
            }
        })
        .catch((error) => {
            console.error('Loading the watchlist failed', error);
            loadingPromise = null;
        });

    return loadingPromise;
}

function postToServiceWorker(message) {
    navigator.serviceWorker?.controller?.postMessage(message);
}

const WATCHLIST_OWNER_KEY = 'movie-guru-watchlist-owner';

/**
 * Cached offline pages embed the signed-in user, so they are rebuilt when a different person
 * (or a guest) uses the app on this device. Remembered across page loads.
 */
function hasWatchlistOwnerChanged(userId) {
    const owner = userId ? `user:${userId}` : 'guest';
    try {
        const previousOwner = localStorage.getItem(WATCHLIST_OWNER_KEY);
        localStorage.setItem(WATCHLIST_OWNER_KEY, owner);
        return previousOwner !== null && previousOwner !== owner;
    } catch {
        return true;
    }
}

function cacheForOffline(items, {reset = false} = {}) {
    if (items.length === 0 && !reset) {
        return;
    }

    postToServiceWorker({
        type: 'CACHE_WATCHLIST',
        items: items.map(({imdb_id, poster}) => ({imdb_id, poster})),
        reset,
    });
}

/**
 * Asks the browser not to evict cached titles and the on-device watchlist under storage pressure.
 */
export function requestPersistentStorage() {
    if (!navigator.storage?.persist) {
        return;
    }

    navigator.storage.persisted()
        .then((isPersisted) => isPersisted || navigator.storage.persist())
        .catch((error) => console.error('Requesting persistent storage failed', error));
}

function showToast(message) {
    new BroadcastChannel('toast-notifications').postMessage({message, level: 'danger'});
}

export function useWatchlist() {
    const page = usePage();

    watch(
        () => page.props.auth?.user?.id ?? null,
        (userId) => loadWatchlist(userId),
        {immediate: true},
    );

    const isMemberSignedIn = () => Boolean(page.props.auth?.user);

    const isMovieInWatchlist = (imdbId) => {
        return watchlist.value.some((m) => m.imdb_id === imdbId);
    };

    const addMovie = async (movie) => {
        const item = toWatchlistItem(movie);
        watchlist.value = [item, ...watchlist.value];

        try {
            if (isMemberSignedIn()) {
                await requestJson(route('watchlist.store'), {
                    method: 'POST',
                    body: JSON.stringify({imdb_id: item.imdb_id, title: item.title, poster: item.poster, year: item.year}),
                });
            } else {
                await saveGuestItem(item);
            }
        } catch (error) {
            console.error('Saving to the watchlist failed', error);
            watchlist.value = watchlist.value.filter((m) => m.imdb_id !== item.imdb_id);
            showToast(`Couldn't save "${item.title}" to your watchlist. Please try again.`);
            return;
        }

        requestPersistentStorage();
        cacheForOffline([item]);
    };

    const removeMovie = async (imdbId) => {
        const previousWatchlist = watchlist.value;
        watchlist.value = watchlist.value.filter((m) => m.imdb_id !== imdbId);

        try {
            if (isMemberSignedIn()) {
                await requestJson(route('watchlist.destroy', {imdbId}), {method: 'DELETE'});
            } else {
                await deleteGuestItem(imdbId);
            }
        } catch (error) {
            console.error('Removing from the watchlist failed', error);
            watchlist.value = previousWatchlist;
            showToast("Couldn't remove that title from your watchlist. Please try again.");
            return;
        }

        postToServiceWorker({type: 'UNCACHE_WATCHLIST_TITLE', imdbId});
    };

    const toggleMovie = (movie) => {
        return isMovieInWatchlist(movie.imdb_id) ? removeMovie(movie.imdb_id) : addMovie(movie);
    };

    return {
        watchlist,
        isWatchlistLoaded,
        isMovieInWatchlist,
        toggleMovie,
        removeMovie,
    };
}
