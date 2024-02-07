const DEBUG = true;

const APP_CACHE = 'v-0.3.0';
const SEARCH_CACHE = 'search-cache-v-0.3.0';
const INFO_CACHE = 'info-cache-v-0.3.0';
const DYNAMIC_CACHE = 'dynamic-cache-v-0.3.0';
const POSTER_CACHE = 'poster-cache-v-0.3.0';
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
    '/build/assets/app-U--N9XqK.css',
    '/build/assets/LoadingSpinner-Rlm10Kz6.js',
    '/build/assets/ApplicationLogo-yee8g2b-.js',
    '/build/assets/InputError-lnzTIaPt.js',
    '/build/assets/InputLabel-5Ig9d7Lt.js',
    '/build/assets/GuestLayout-qwCmoq2u.js',
    '/build/assets/LoadingSpinnerButton-OSm-ewYf.js',
    '/build/assets/Dashboard-RtR_RsHh.js',
    '/build/assets/PrimaryButton-F9ObH13Q.js',
    '/build/assets/Show-J1oLU0Sn.js',
    '/build/assets/TextInput-StlNCIgW.js',
    '/build/assets/Edit-pKjpX83f.js',
    '/build/assets/ConfirmPassword-EI8SJc5p.js',
    '/build/assets/ForgotPassword-ZN3zni6m.js',
    '/build/assets/VerifyEmail-91rXY6Fm.js',
    '/build/assets/ResetPassword-UZWoj4wg.js',
    '/build/assets/Error-PCH10RAV.js',
    '/build/assets/Register-pbxiHmkX.js',
    '/build/assets/UpdatePasswordForm-EZZL7XCL.js',
    '/build/assets/UpdateProfileInformationForm-aylbUlAW.js',
    '/build/assets/Login-OlqQHU9l.js',
    '/build/assets/Terms-mGbAnRib.js',
    '/build/assets/NewsletterForm-jLArXhXP.js',
    '/build/assets/DeleteUserForm-x1GRpLGc.js',
    '/build/assets/PrivacyPolicy-quS5kbN8.js',
    '/build/assets/Contact-kw1RhTm0.js',
    '/build/assets/AuthenticatedLayout-3V3fDVRJ.js',
    '/build/assets/Welcome-JiOedX3N.js',
    '/build/assets/Search-1O3QAnAR.js',
    '/build/assets/DetailCard-NClBBvtj.js',
    '/build/assets/BaseLayout-m8L9RbyY.js',
    '/build/assets/app-B2tkj5DN.js',
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
            self.clients.matchAll().then((clients) => {
                clients.forEach((client) => {
                    client.postMessage({
                        type: 'NETWORK_STATUS',
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
                    alert('You need to allow push notifications.');
                }
            } catch (e) {
                log("Error while requesting and/or showing notification.", e);
            }
        }
    }
});

self.addEventListener('sync', async (event) => {
    const offlineRequestUrl = localStorage.getItem('offlineRequestUrl');

    if (offlineRequestUrl) {
        // Perform actions to notify the user about the stored offline request
        self.registration.showNotification('Content is Ready!', {
            body: 'The requested content is now available. Click to view.',
            badge: '/icons/ios/152.png',
            icon: '/icons/ios/152.png',
            actions: [
                {
                    action: 'open',
                    title: 'View Content',
                },
                {
                    action: 'close',
                    title: 'Not Now',
                },
            ],
            data: {
                url: offlineRequestUrl,
            },
            requireInteraction: true,
            vibrate: [100, 50, 100],
            renotify: true,
        });

        // Clear the stored offline request URL
        localStorage.removeItem('offlineRequestUrl');

        cacheRequest(DYNAMIC_CACHE, new Request(offlineRequestUrl), 15, 2 * 24 * 60 * 60);
    }
});

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
                action: 'open',
                title: 'Find Today\'s Binge',
            },
            {
                action: 'close',
                title: 'Not Now',
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
