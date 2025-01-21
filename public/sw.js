const DEBUG = true;
const broadcast = new BroadcastChannel('service-worker-channel');
const broadcastChannel = new BroadcastChannel('toast-notifications');

const APP_CACHE = 'v-3.1';
const SEARCH_CACHE = 'search-cache-v-3.1';
const INFO_CACHE = 'info-cache-v-3.1';
const DYNAMIC_CACHE = 'dynamic-cache-v-3.1';
const POSTER_CACHE = 'poster-cache-v-3.1';
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
    '/?utm_source=homescreen',
    '/build/manifest.json',
    '/build/assets/Search-DSwcpwDw.css',
    '/build/assets/BaseLayout-pP2Wt1YA.css',
    '/build/assets/SearchCard-XTlQ7yWv.css',
    '/build/assets/app-Do2fUH22.css',
    '/build/assets/LoadingSpinner-CjYwbiOI.js',
    '/build/assets/ApplicationLogo-FmGeh9E8.js',
    '/build/assets/InputError-DxEK4CsN.js',
    '/build/assets/InputLabel-OFoun0OF.js',
    '/build/assets/GuestLayout-BK-LRdcT.js',
    '/build/assets/LoadingSpinnerButton-B9IS-vxb.js',
    '/build/assets/PrimaryButton-DJSl_Jqe.js',
    '/build/assets/Dashboard-DEJQeDA3.js',
    '/build/assets/TextInput-YPsmopln.js',
    '/build/assets/Edit-muq-RBUe.js',
    '/build/assets/ConfirmPassword-0b1jFFoF.js',
    '/build/assets/ForgotPassword-DyPtjUmE.js',
    '/build/assets/VerifyEmail--dqMV3tj.js',
    '/build/assets/Show-CPsY4UXU.js',
    '/build/assets/ResetPassword-pTcy4wIQ.js',
    '/build/assets/Error-B7DB05Ji.js',
    '/build/assets/Register-CtwhX4Is.js',
    '/build/assets/UpdatePasswordForm-DZoeXejj.js',
    '/build/assets/UpdateProfileInformationForm-kFzw0lQq.js',
    '/build/assets/Login-CT4Z1mf5.js',
    '/build/assets/NewsletterForm-DJFeiMRv.js',
    '/build/assets/Terms-CgZla7ZF.js',
    '/build/assets/PrivacyPolicy-CpMjPjj6.js',
    '/build/assets/DeleteUserForm-BZzaBNpg.js',
    '/build/assets/SearchCard-CudmcOcy.js',
    '/build/assets/Contact-DbVHjTy5.js',
    '/build/assets/AuthenticatedLayout-BfNgM_aN.js',
    '/build/assets/Search-DYqCNw3g.js',
    '/build/assets/Welcome-DPXbdXiR.js',
    '/build/assets/BaseLayout-CqZ2zf6M.js',
    '/build/assets/DetailCard-BI4KA-Q9.js',
    '/build/assets/app-DSLH8jDv.js',
    '/build/assets/Search-DSwcpwDw.css',
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
                            message: 'You appear to be offline. Your search request will be processed once you\'re back online.',
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
            .then(() => {
                self.skipWaiting();
                // Notify the clients that the app is available offline
                broadcastChannel.postMessage({
                    type: 'APP_AVAILABLE_OFFLINE',
                    message: 'The app is now available offline. Enjoy your movie binge!',
                    level: 'success',
                });
            })
            .catch(error => {
                logError('Error during installation', error);
                throw error;
            })
    );
});

self.addEventListener('periodicsync', async (event) => {
    if (event.tag === 'weeklyTrendingNotification') {
        const now = new Date();
        const isSaturday = now.getDay() === 6; // Saturday is day 6
        const isNotificationTime = now.getHours() > 18; // 6 PM is hour 18

        if (isSaturday && isNotificationTime) {
            // Check if notification permission is granted before showing
            if (Notification.permission === 'granted') {
                sendTrendingNotification();
            } else {
                try {
                    const permission = await Notification.requestPermission();
                    if (permission === 'granted') {
                        sendTrendingNotification();
                    } else {
                        broadcastChannel.postMessage({
                            type: 'NOTIFICATION_PERMISSION_DENIED',
                            message: 'You need to allow push notifications.',
                            level: 'danger',
                        });
                    }
                } catch (error) {
                    broadcastChannel.postMessage({
                        type: 'NOTIFICATION_PERMISSION_DENIED',
                        message: 'Error while requesting and/or showing notification.',
                        level: 'danger',
                    });

                    log("Error while requesting and/or showing notification.", e);
                }
            }
        }
    } else if (event.tag === 'notificationSync') {
        rollOpeningCredits();
    }
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

        broadcastChannel.postMessage({
            type: 'APP_UPDATED',
            message: 'The app has been updated and is ready to use.',
            level: 'success',
        });
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

self.addEventListener('sync', async (event) => {
    if (event.tag === 'offlineSync') {
        broadcast.postMessage({type: 'OFFLINE_SYNC_EVENT'});
    }
});

broadcast.onmessage = (event) => {
    if (event.data.type === 'OFFLINE_SYNC_REQUEST') {
        const offlineRequestUrl = event.data.url;
        offlineSyncRequest(offlineRequestUrl);
    }
};

async function offlineSyncRequest(offlineRequestUrl) {
    if (!offlineRequestUrl) {
        return;
    }

    try {
        await cacheRequest(DYNAMIC_CACHE, new Request(offlineRequestUrl), 15, 2 * 24 * 60 * 60);

        const url = new URL(offlineRequestUrl);
        const searchParams = new URLSearchParams(url.search);
        const searchQuery = searchParams.get('s') || 'search';
        broadcast.postMessage({
            type: 'OFFLINE_SYNC_FETCHED',
            message: `Your request for ${searchQuery} request is ready and waiting for you. Check notification to view and explore the results. 🚀👀`,
            level: 'success',
        });

        // Perform actions to notify the user about the stored offline request
        self.registration.showNotification('Content is Ready!', {
            body: `Your request for ${searchQuery} request is ready and waiting for you. Check notification to view and explore the results. 🚀👀`,
            badge: 'https://movieguru.shakiltech.com/icons/ios/152.png',
            icon: 'https://movieguru.shakiltech.com/icons/ios/152.png',
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
    } catch (error) {
        logError('Error occurred while fetching offline request', error);
    }

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
        badge: 'https://movieguru.shakiltech.com/icons/ios/152.png',
        icon: 'https://movieguru.shakiltech.com/icons/ios/152.png',
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

function sendTrendingNotification() {
    const notificationTitle = "🔥 What's Trending This Weekend?";
    const notificationBody = "Don't miss out on the latest trends! 🍿 Tap to discover your next favorite binge-worthy experience.";
    const notificationData = {url: '/'};

    self.registration.showNotification(notificationTitle, {
        body: notificationBody,
        icon: 'https://movieguru.shakiltech.com/icons/ios/152.png',
        badge: 'https://movieguru.shakiltech.com/icons/ios/152.png',
        actions: [
            {action: 'close', title: 'Not Now'},
            {action: 'open', title: 'Check It Out!'},
        ],
        data: notificationData,
        requireInteraction: true,
        vibrate: [200, 100, 200],
    });
}

