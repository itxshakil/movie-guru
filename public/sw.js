const DEBUG = true;
const broadcast = new BroadcastChannel('service-worker-channel');
const broadcastChannel = new BroadcastChannel('toast-notifications');

const APP_CACHE = 'v-0.6.0';
const SEARCH_CACHE = 'search-cache-v-0.6.0';
const INFO_CACHE = 'info-cache-v-0.6.0';
const DYNAMIC_CACHE = 'dynamic-cache-v-0.6.0';
const POSTER_CACHE = 'poster-cache-v-0.6.0';
const STATIC_ASSETS = [
    '/app.webmanifest',
    '/assets/images/screenshots/MOVIE_GURU_HOME_PAGE_SCREENSHOT.png',
    '/assets/images/screenshots/MOVIE_GURU_FEATURES_SCREENSHOT.png',
    '/assets/images/screenshots/MOVIE_GURU_SEARCH_SCREENSHOT.png',
    '/assets/images/screenshots/MOVIE_GURU_INFO_SCREENSHOT.png',
    '/assets/images/screenshots/MOVIE_GURU_INFO_WIDE_SCREENSHOT.jpeg',
    '/assets/images/screenshots/MOVIE_GURU_HOME_WIDE_SCREENSHOT.png',
    '/assets/images/screenshots/MOVIE_GURU_FEAURES_WIDE_SCREENSHOT.png',
];

const basicPathsToCache = [
    '/',
    '/?utmsource=homescreen',
    '/build/manifest.json',
    '/build/assets/Search-gQQPsr-3.css',
    '/build/assets/app-oUeIzzkn.css',
    '/build/assets/LoadingSpinner-KDhPflkM.js',
    '/build/assets/ApplicationLogo-HXYs5I2s.js',
    '/build/assets/InputError-7OGyqHAs.js',
    '/build/assets/InputLabel-g_rpLVi0.js',
    '/build/assets/GuestLayout-5oNk8hEZ.js',
    '/build/assets/Dashboard-B09Geg_l.js',
    '/build/assets/LoadingSpinnerButton-g4LC3Z4y.js',
    '/build/assets/PrimaryButton-WBc0-2IW.js',
    '/build/assets/Show-_rLkZ4IL.js',
    '/build/assets/TextInput-2Up8gzHR.js',
    '/build/assets/Edit-kkj9c2CQ.js',
    '/build/assets/ConfirmPassword-Mb3n05Yh.js',
    '/build/assets/ForgotPassword-Vk84PtBU.js',
    '/build/assets/VerifyEmail-s-aW4qHL.js',
    '/build/assets/ResetPassword-tj2BViva.js',
    '/build/assets/Error-duniLSnW.js',
    '/build/assets/Register-Xlj4JpNK.js',
    '/build/assets/UpdatePasswordForm-lhZF_7C9.js',
    '/build/assets/UpdateProfileInformationForm-XULMHS2S.js',
    '/build/assets/Login-ZcS-QcC7.js',
    '/build/assets/Terms-NWT1yrzO.js',
    '/build/assets/NewsletterForm-S4FOiXY6.js',
    '/build/assets/DeleteUserForm-Hjy0-Xer.js',
    '/build/assets/PrivacyPolicy-Muy5Je53.js',
    '/build/assets/Contact-eDPqLaVN.js',
    '/build/assets/AuthenticatedLayout-LRNJCVr-.js',
    '/build/assets/Welcome-y9UBLUfs.js',
    '/build/assets/Search-CNej41GL.js',
    '/build/assets/DetailCard-8BWQsIjR.js',
    '/build/assets/BaseLayout-vwata4FY.js',
    '/build/assets/app-mc97GYR-.js',
    '/assets/images/no-poster.jpg',
];

function log(message, ...optionalParams) {
    if (DEBUG) {
        console.log(
            `%c Service Worker %c ${message}`,
            'background: #333; color: #fff; border-radius: 0.1em; padding: 0 0.3em; margin-right: 0.5em;',
            'background: #3498db; color: #fff; border-radius: 0.1em; padding: 0 0.3em;',
            ...optionalParams
        );
    }
}

function logError(message, ...optionalParams) {
    if (DEBUG) {
        console.error(
            `%c Service Worker %c ${message}`,
            'background: #f00; color: #fff; border-radius: 0.1em; padding: 0 0.3em; margin-right: 0.5em;',
            'background: #3498db; color: #fff; border-radius: 0.1em; padding: 0 0.3em;',
            ...optionalParams
        );
    }
}

// Function to fetch and cache a request
const cacheRequest = async (cacheName, request, maxEntries, maxAge) => {
    // If cache is not supported or request is not a GET request, fall back to network
    if (!('caches' in self) || request.method !== 'GET') {
        return fetch(request);
    }

    try {
        const cache = await caches.open(cacheName);
        const cachedResponse = await cache.match(request);

        if (cachedResponse) {
            const cachedTime = new Date(cachedResponse.headers.get('date')).getTime();
            const now = Date.now();
            const isCacheOld = (now - cachedTime) > maxAge * 1000;
            if (isCacheOld) {
                await cache.delete(request);
            } else {
                return cachedResponse;
            }
        }

        const response = await fetch(request);
        if (response.ok && request.method === 'GET') {
            const clonedResponse = response.clone();
            log('Caching New Resource', request.url);
            await cache.put(request, clonedResponse);

            const updatedCachedResponses = await cache.keys();
            if (maxEntries && updatedCachedResponses.length >= maxEntries) {
                await cache.delete(updatedCachedResponses[0]); // Remove the oldest entry
            }
        }

        return response;
    } catch (error) {
        // if it get /search and network is not available, then we will prompt the user to check their network and notify when it is back
        if (request.url.includes('/search') && navigator.onLine === false) {
            // send message to the client
            self.clients.matchAll()
            .then((clients) => {
                clients.forEach((client) => {
                    client.postMessage({
                        type: 'OFFLINE_SEARCH_DETECTED',
                        status: 'offline',
                        url: request.url,
                    });
                });
            });
        }

        logError('Error fetching and caching new data', error);
        throw error; // re-throw the error to be handled by the caller
    }
};

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(APP_CACHE)
            .then(cache => cache.addAll(basicPathsToCache))
            .then(() => self.skipWaiting())
            .catch(error => {
                logError('Error during installation', error);
                throw error;
            })
    );
});

self.addEventListener('activate', async (e) => {
    log('Activated');
    try {
        const keyList = await caches.keys();
        const promises = keyList.map((key) => {
            if (key !== APP_CACHE) {
                log('Removing Old Cache', key);
                return caches.delete(key);
            }
        });
        await Promise.all(promises);
    } catch (error) {
        logError('Error removing old cache', error);
    }

    try {
        await self.clients.claim();
        log('Claimed clients');
    } catch (error) {
        logError('Error claiming clients', error);
    }
});

async function handleAssetRequest(request) {
    let cache;
    try {
        cache = await caches.open(APP_CACHE);
    } catch (error) {
        logError('Error opening cache', error);
        // If cache opening fails, fall back to network request
        return fetch(request);
    }

    const cachedResponse = await cache.match(request);
    if (cachedResponse) return cachedResponse;

    let response;
    try {
        response = await fetch(request);
    } catch (error) {
        logError('Fetch request failed for URL', request.url, error);
        throw error; // Rethrow the error so it can be handled by the caller
    }

    if (response.ok && request.method === 'GET') {
        log('Caching New Resource', request.url);
        const clonedResponse = response.clone();
        try {
            await cache.put(request, clonedResponse);
        } catch (error) {
            logError('Error putting response into cache', error);
        }
    }

    return response;
}

// Event listener for fetching requests
self.addEventListener('fetch', async event => {
    const request = event.request;

    // Cache-first strategy for static assets
    if (STATIC_ASSETS.includes(request.url)) {
        event.respondWith(handleAssetRequest(request));
    } else if (request.url.startsWith(self.location.origin)) {
        if (request.url.includes('/search')) {
            event.respondWith(cacheRequest(SEARCH_CACHE, request, 15, 5 * 24 * 60 * 60));
        } else if (request.url.includes('/i/')) {
            event.respondWith(cacheRequest(INFO_CACHE, request, 10, 2 * 24 * 60 * 60));
        } else {
            event.respondWith(cacheRequest(DYNAMIC_CACHE, request, 15, 2 * 24 * 60 * 60));
        }
    } else if (request.url.includes('m.media-amazon.com/images/')) {
        event.respondWith(cacheRequest(POSTER_CACHE, request, 15, 7 * 24 * 60 * 60));
    } else if (request.url.includes('bunny.net') && false) {
        event.respondWith(cacheRequest(APP_CACHE, request, null, 7 * 24 * 60 * 60));
    } else {
        event.respondWith(fetch(event.request));
    }
});
// Listen for messages from the page to the service worker
self.addEventListener('message', event => {
    log('Message Received', event.data);
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting().then(r => log('Skip waiting')).catch(
            error => logError('Error skipping waiting', error));
    }
});

function handleNotificationClick(event) {
    if (event.action === 'close') {
        event.notification.close();
    } else {
        event.waitUntil(clients.matchAll({
            type: 'window',
        }).then((clientList) => {
            const notification = event.notification;
            const data = notification.data || {};
            const urlToOpen = data.url || '/';

            // Check if clients.openWindow is available
            if (clients.openWindow) {
                // Check if the URL is valid before trying to open a window
                if (urlToOpen.startsWith('http://') || urlToOpen.startsWith('https://')) {
                    return clients.openWindow(urlToOpen);
                } else {
                    // Fallback to opening the home URL
                    return clients.openWindow('/');
                }
            } else {
                // Fallback to opening the home URL using a different method if clients.openWindow is not available
                if (urlToOpen.startsWith('http://') || urlToOpen.startsWith('https://')) {
                    // Use a different method to open the URL if clients.openWindow is not available
                    window.open(urlToOpen, '_blank');
                } else {
                    // Fallback to opening the home URL
                    window.open('/', '_blank');
                }
            }
        }));
    }
}

self.addEventListener('periodicsync', async (event) => {
    if (event.tag !== 'notificationSync') {
        // return;
    }
    const now = new Date();
    const movieTimeStart = 19; // 7 PM
    const movieTimeEnd = 21;   // 9 PM

    if ((now.getDay() === 0 && now.getHours() >= movieTimeStart &&
        now.getHours() < movieTimeEnd) ||
        (now.getDay() === 0 && now.getHours() >= 9 && now.getHours() <
            13)) {
        if (Notification.permission === 'granted') {
            rollOpeningCredits();
        } else {
            try {
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    rollOpeningCredits();
                } else {
                    broadcastChannel.postMessage({
                        type: 'NOTIFICATION_PERMISSION_DENIED',
                        data: {
                            message: 'You need to allow push notifications.',
                            level: 'danger',
                        },
                    });
                }
            } catch (e) {
                broadcastChannel.postMessage({
                    type: 'NOTIFICATION_PERMISSION_DENIED',
                    data: {
                        message: 'Error while requesting and/or showing notification.',
                        level: 'danger',
                    },
                });

                log("Error while requesting and/or showing notification.", e);
            }
        }
    }
});

self.addEventListener('sync', async (event) => {
    if (event.tag === 'offlineSync') {
        broadcast.postMessage({ type: 'OFFLINE_SYNC_EVENT' });
    }
});

broadcast.onmessage = (event) => {
    if (event.data.type === 'OFFLINE_SYNC_REQUEST') {
        const offlineRequestUrl = event.data.url;
        offlinceSyncRequest(offlineRequestUrl);
    }
};

function offlinceSyncRequest(offlineRequestUrl) {
    if (!offlineRequestUrl) {
        return;
    }

    // Perform actions to notify the user about the stored offline request
    self.registration.showNotification('Content is Ready!', {
        body: 'Your requested content is ready and waiting for you. Click to view and explore the results.🚀👀',
        badge: '/icons/ios/152.png',
        icon: '/icons/ios/152.png',
        actions: [
            {
                action: 'close',
                title: 'Not Now',
            },
            {
                action: 'open',
                title: 'Check Now',
            },
        ],
        data: {
            url: offlineRequestUrl,
        },
        requireInteraction: true,
        vibrate: [100, 50, 100],
    });

    broadcast.postMessage({ type: 'OFFLINE_SYNC_FETCHED' });
    cacheRequest(DYNAMIC_CACHE, new Request(offlineRequestUrl), 15, 2 * 24 * 60 * 60);
}

function rollOpeningCredits() {
    let sceneDescription = 'Welcome to the Weekend Movie Binge-fest! 🌟 Grab your popcorn and let\'s dive into a binge-worthy experience.';

    const now = new Date();
    if (now.getHours() >= 12 && now.getHours() < 16) {
        sceneDescription = 'Afternoon Binge: Which movie are stealing the spotlight in your life today? 🎥';
    } else if (now.getHours() >= 16 && now.getHours() < 20) {
        sceneDescription = 'Evening Marathon: It\'s showtime! Time to binge in the starring role. 🌟';
    } else {
        sceneDescription = 'Late Night Binge: Plan your tomorrow and cue the dreams. Good night, star! 🌙';
    }

    self.registration.showNotification('My Binge', {
        tag: 'alert',
        body: sceneDescription,
        badge: '/icons/ios/152.png',
        icon: '/icons/ios/152.png',
        actions: [
            {
                action: 'close',
                title: 'Not Now',
            },
            {
                action: 'open',
                title: 'Find Today\'s Binge',
            },
        ],
        data: {
            url: '/',
        },
        requireInteraction: true,
        vibrate: [200, 100, 200, 200, 100, 200, 200, 100, 200],
        renotify: true,
    });
}

self.addEventListener('push', (event) => {
    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body,
            icon: data.icon,
            badge: data.badge,
            image: data.image,
            vibrate: data.vibrate,
            data: {
                url: data.url,
            },
            actions: data.actions,
        };

        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

self.addEventListener('notificationclick', handleNotificationClick);
