import {computed, ref, watch} from 'vue';
import {usePage} from '@inertiajs/vue3';
import {applyLibraryChange, splitLibrary, STATUS, toLibraryItem, upgradeLegacyItem} from '@/Composables/libraryState.js';

/**
 * One library per person: titles to watch and titles already watched (optionally liked or disliked).
 * Guests keep it on the device in IndexedDB; members keep it in their account, and a guest library
 * is merged into the account on sign-in. Saved titles are cached by the service worker for offline use.
 */

const LEGACY_STORAGE_KEY = 'movie-guru-watchlist';
const LIBRARY_OWNER_KEY = 'movie-guru-watchlist-owner';
const DB_NAME = 'movie-guru';
const DB_VERSION = 2;
const LEGACY_STORE = 'watchlist';
const STORE_NAME = 'library';

const library = ref([]);
const isLibraryLoaded = ref(false);
const libraryTabs = computed(() => splitLibrary(library.value));

let loadedForUserId;
let loadingPromise = null;

/**
 * Version 2 replaces the version 1 "watchlist" store with "library", copying every saved
 * title as "to_watch" inside the upgrade transaction so nothing is lost.
 */
function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);
        request.onupgradeneeded = () => {
            const db = request.result;
            const libraryStore = db.objectStoreNames.contains(STORE_NAME)
                ? request.transaction.objectStore(STORE_NAME)
                : db.createObjectStore(STORE_NAME, {keyPath: 'imdb_id'});

            if (db.objectStoreNames.contains(LEGACY_STORE)) {
                const legacyRows = request.transaction.objectStore(LEGACY_STORE).getAll();
                legacyRows.onsuccess = () => {
                    legacyRows.result.forEach((row) => libraryStore.put(upgradeLegacyItem(row)));
                    db.deleteObjectStore(LEGACY_STORE);
                };
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

/**
 * Moves a watchlist saved by a much older version of the app from localStorage into IndexedDB, once.
 */
async function migrateLegacyLocalStorage() {
    let legacyItems;
    try {
        legacyItems = JSON.parse(localStorage.getItem(LEGACY_STORAGE_KEY) ?? '[]');
    } catch {
        legacyItems = [];
    }

    if (Array.isArray(legacyItems) && legacyItems.length > 0) {
        const savedAt = Date.now();
        await runTransaction('readwrite', (store) => {
            legacyItems.forEach((movie, index) => store.put(upgradeLegacyItem({...movie, saved_at: savedAt - index})));
        });
    }

    localStorage.removeItem(LEGACY_STORAGE_KEY);
}

async function loadGuestLibrary() {
    await migrateLegacyLocalStorage();
    return readGuestItems();
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
        throw new Error(`Library request failed with status ${response.status}`);
    }

    return response.status === 204 ? null : response.json();
}

async function loadMemberLibrary() {
    const guestItems = await loadGuestLibrary();

    if (guestItems.length === 0) {
        const {data} = await requestJson(route('library.index'));
        return data;
    }

    const {data} = await requestJson(route('library.import.store'), {
        method: 'POST',
        body: JSON.stringify({items: guestItems.map(({saved_at, ...item}) => item)}),
    });
    await clearGuestItems();
    return data;
}

function loadLibrary(userId) {
    if (loadedForUserId === userId && loadingPromise) {
        return loadingPromise;
    }

    loadedForUserId = userId;
    loadingPromise = (userId ? loadMemberLibrary() : loadGuestLibrary())
        .then((items) => {
            if (loadedForUserId === userId) {
                library.value = items;
                isLibraryLoaded.value = true;
                cacheForOffline(items, {reset: hasLibraryOwnerChanged(userId)});
            }
        })
        .catch((error) => {
            console.error('Loading the library failed', error);
            loadingPromise = null;
        });

    return loadingPromise;
}

function postToServiceWorker(message) {
    navigator.serviceWorker?.controller?.postMessage(message);
}

/**
 * Cached offline pages embed the signed-in user, so they are rebuilt when a different person
 * (or a guest) uses the app on this device. Remembered across page loads.
 */
function hasLibraryOwnerChanged(userId) {
    const owner = userId ? `user:${userId}` : 'guest';
    try {
        const previousOwner = localStorage.getItem(LIBRARY_OWNER_KEY);
        localStorage.setItem(LIBRARY_OWNER_KEY, owner);
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
        type: 'CACHE_LIBRARY',
        items: items.map(({imdb_id, poster}) => ({imdb_id, poster})),
        reset,
    });
}

/**
 * Asks the browser not to evict cached titles and the on-device library under storage pressure.
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

const findItem = (imdbId) => library.value.find((item) => item.imdb_id === imdbId) ?? null;

export function useLibrary() {
    const page = usePage();

    watch(
        () => page.props.auth?.user?.id ?? null,
        (userId) => loadLibrary(userId),
        {immediate: true},
    );

    const isMemberSignedIn = () => Boolean(page.props.auth?.user);

    const persist = async (item) => {
        if (!isMemberSignedIn()) {
            await saveGuestItem(item);
            return;
        }

        await requestJson(route('library.update', {imdbId: item.imdb_id}), {
            method: 'PUT',
            body: JSON.stringify({
                title: item.title,
                poster: item.poster,
                year: item.year,
                status: item.status,
                reaction: item.reaction,
            }),
        });
    };

    const destroy = async (imdbId) => {
        if (isMemberSignedIn()) {
            await requestJson(route('library.destroy', {imdbId}), {method: 'DELETE'});
        } else {
            await deleteGuestItem(imdbId);
        }
    };

    /**
     * Applies a change optimistically, saves it, and restores this action's snapshot on failure.
     */
    const commit = async (nextItem, failureMessage) => {
        const snapshot = library.value;
        library.value = [nextItem, ...snapshot.filter((item) => item.imdb_id !== nextItem.imdb_id)];

        try {
            await persist(nextItem);
        } catch (error) {
            console.error('Saving the library failed', error);
            library.value = snapshot;
            showToast(navigator.onLine ? failureMessage : "Couldn't sync, try again when you're online.");
            return false;
        }

        requestPersistentStorage();
        cacheForOffline([nextItem]);
        return true;
    };

    const addToWatchlist = async (movie) => {
        const existing = findItem(movie.imdb_id);
        const next = existing
            ? applyLibraryChange(existing, {type: 'add-to-watchlist'})
            : toLibraryItem(movie);
        await commit(next, `Couldn't save "${next.title}" to your watchlist. Please try again.`);
    };

    const markWatched = async (movie, reaction = null) => {
        const existing = findItem(movie.imdb_id) ?? toLibraryItem(movie);
        await commit(
            applyLibraryChange(existing, {type: 'mark-watched', reaction}),
            `Couldn't mark "${existing.title}" as watched. Please try again.`,
        );
    };

    const setReaction = async (imdbId, reaction) => {
        const existing = findItem(imdbId);
        if (!existing || existing.status !== STATUS.WATCHED) {
            return;
        }
        await commit(
            applyLibraryChange(existing, {type: 'set-reaction', reaction}),
            "Couldn't save your reaction. Please try again.",
        );
    };

    const remove = async (imdbId) => {
        const snapshot = library.value;
        library.value = snapshot.filter((item) => item.imdb_id !== imdbId);

        try {
            await destroy(imdbId);
        } catch (error) {
            console.error('Removing from the library failed', error);
            library.value = snapshot;
            showToast(navigator.onLine
                ? "Couldn't remove that title. Please try again."
                : "Couldn't sync, try again when you're online.");
            return;
        }

        postToServiceWorker({type: 'UNCACHE_LIBRARY_TITLE', imdbId});
    };

    return {
        library,
        toWatch: computed(() => libraryTabs.value.toWatch),
        watched: computed(() => libraryTabs.value.watched),
        isLibraryLoaded,
        isInWatchlist: (imdbId) => findItem(imdbId)?.status === STATUS.TO_WATCH,
        isWatched: (imdbId) => findItem(imdbId)?.status === STATUS.WATCHED,
        reactionFor: (imdbId) => findItem(imdbId)?.reaction ?? null,
        addToWatchlist,
        markWatched,
        setReaction,
        unmarkWatched: remove,
        remove,
    };
}
