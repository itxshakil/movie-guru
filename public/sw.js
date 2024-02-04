const DEBUG = true;

const APP_CACHE = 'v-0.1.0';
const SEARCH_CACHE = 'search-cache-v-0.1.0';
const INFO_CACHE = 'info-cache-v-0.1.0';
const DYNAMIC_CACHE = 'dynamic-cache-v-0.1.0';
const POSTER_CACHE = 'poster-cache-v-0.1.0';
const STATIC_ASSETS = [
    '/app.webmanifest',
    '/assets/images/screenshots/MOVIE_GURU_HOME_PAGE_SCREENSHOT.png',
    '/assets/images/screenshots/MOVIE_GURU_FEATURES_SCREENSHOT.png',
    '/assets/images/screenshots/MOVIE_GURU_SEARCH_SCREENSHOT.png',
    '/assets/images/screenshots/MOVIE_GURU_INFO_SCREENSHOT.png',
    '/assets/images/screenshots/MOVIE_GURU_INFO_WIDE_SCREENSHOT.png',
];

const basicPathsToCache = [
    '/',
    '/?utmsource=homescreen',
    '/build/manifest.json',
    '/build/assets/Search-ibh2L30M.css',
    '/build/assets/app-sb5t6rgy.css',
    '/build/assets/LoadingSpinner--lahAWOX.js',
    '/build/assets/ApplicationLogo-8nZBYErT.js',
    '/build/assets/InputError-aSGjAI5J.js',
    '/build/assets/InputLabel-JE_19DW1.js',
    '/build/assets/GuestLayout-kXLTpHzw.js',
    '/build/assets/LoadingSpinnerButton-aivUcsrp.js',
    '/build/assets/Dashboard-jVdfxfKH.js',
    '/build/assets/PrimaryButton-ODZwFpsI.js',
    '/build/assets/Show-xtjx2qyd.js',
    '/build/assets/TextInput-OIKtfjIU.js',
    '/build/assets/Edit-o97ab7x_.js',
    '/build/assets/ConfirmPassword-z2X7AbEU.js',
    '/build/assets/ForgotPassword-ic2cwUIE.js',
    '/build/assets/VerifyEmail-jcLZE6hY.js',
    '/build/assets/ResetPassword-TiZdbFaH.js',
    '/build/assets/Error-GNNzaWhY.js',
    '/build/assets/Register-H7Ac3l6r.js',
    '/build/assets/UpdatePasswordForm-Du5eG4Ti.js',
    '/build/assets/UpdateProfileInformationForm-PZcuRV11.js',
    '/build/assets/Login-11jXXC4T.js',
    '/build/assets/Terms-9i_z_Yq4.js',
    '/build/assets/NewsletterForm-vhgOwep0.js',
    '/build/assets/DeleteUserForm-Jxwm5mop.js',
    '/build/assets/PrivacyPolicy-ueTRzfwX.js',
    '/build/assets/Contact-VAbYQvPL.js',
    '/build/assets/AuthenticatedLayout-J_8tdgSB.js',
    '/build/assets/BaseLayout-uGtKn4yc.js',
    '/build/assets/Welcome-vUlu0YnO.js',
    '/build/assets/Search-cskX_pbG.js',
    '/build/assets/DetailCard-6zhVKaqC.js',
    '/build/assets/app-C4OB3lFU.js',
    '/assets/images/no-poster.jpg',
];

// Function to fetch and cache a request
const cacheRequest = async (cacheName, request, maxEntries, maxAge) => {
    try {
        const cache = await caches.open(cacheName);
        const cachedResponses = await cache.keys();

        const now = Date.now();

        // Iterate over the cached responses
        for (const cachedRequest of cachedResponses) {
            const cachedResponse = await cache.match(cachedRequest);

            if (cachedResponse) {
                // Check when this response was cached
                const cachedTime = new Date(
                    cachedResponse.headers.get('date')).getTime();

                // If the response is older than maxAge, delete it and fetch a new one
                if ((now - cachedTime) > maxAge * 1000) {
                    await cache.delete(cachedRequest);

                    const response = await fetch(cachedRequest);
                    const clonedResponse = response.clone();
                    await cache.put(cachedRequest, clonedResponse);
                }
            }
        }

        // Check the number of cached responses again after deleting old ones
        const updatedCachedResponses = await cache.keys();
        if (maxEntries && updatedCachedResponses.length >= maxEntries) {
            await cache.delete(updatedCachedResponses[0]); // Remove the oldest entry
        }

        const response = await fetch(request);
        const clonedResponse = response.clone();
        console.log('Service Worker: Caching New Resource', request.url);
        if (request.method === 'GET') {
            await cache.put(request, clonedResponse);
        }

        return response;
    } catch (error) {
        console.error('Error fetching and caching new data', error);
        throw error; // re-throw the error to be handled by the caller
    }
};

self.addEventListener('install', event => {
    console.log('Service Worker: Installed');
    event.waitUntil(
        caches.open(APP_CACHE).then(cache => {
            console.log('Service Worker: Caching Files');
            return cache.addAll(basicPathsToCache).catch(error => {
                console.error('Service Worker: Error caching files', error);
            });
        }).catch(error => {
            console.error('Service Worker: Error opening cache', error);
        }),
    );
    // Ensure the new service worker activates as soon as it's done installing
    self.skipWaiting().then(() => console.log('Service Worker: Skip waiting')).catch(error => console.error('Service Worker: Error skipping waiting',
        error));
});

self.addEventListener('activate', (e) => {
    console.log('Service Worker: Activated');
    e.waitUntil(
        caches.keys().then((keyList) => Promise.all(keyList.map((key) => {
            if (key !== APP_CACHE) {
                console.log('Service Worker: Removing Old Cache', key);
                return caches.delete(key);
            }
        }))),
    );
    // Take control of all pages under this service worker's scope immediately
    self.clients.claim().then(() => console.log('Service Worker: Claimed clients')).catch(error => console.error('Service Worker: Error claiming clients',
        error));
});

// Event listener for fetching requests
self.addEventListener('fetch', async event => {
    const request = event.request;

    // Cache-first strategy for static assets
    if (STATIC_ASSETS.includes(request.url)) {
        event.respondWith(
            caches.match(request).then(cachedResponse => {
                return cachedResponse || fetch(request).then(response => {
                    return caches.open(APP_CACHE).then(cache => {
                        if (request.method === 'GET') {
                            console.log('Service Worker: Caching New Resource',
                                request.url);
                            const clonedResponse = response.clone();
                            return cache.put(request, clonedResponse).catch(error => {
                                console.error(
                                    'Service Worker: Error putting response into cache',
                                    error);
                            }).then(() => response);
                        }
                        return response;
                    }).catch(error => {
                        console.error('Service Worker: Error opening cache',
                            error);
                        return response;
                    });
                }).catch(error => {
                    console.error('Fetch request failed for URL', request.url,
                        error);
                    return new Response('Fetch request failed');
                });
            }).catch(error => {
                console.error('Service Worker: Error matching cache', error);
                return fetch(request);
            }),
        );
    }

    // Cache basic request paths and refresh weekly
    if (request.url.startsWith(self.location.origin)) {
        if (request.url.includes('/search')) {
            event.respondWith(
                caches.match(request).then(cachedResponse => {
                    return cachedResponse ||
                        cacheRequest(SEARCH_CACHE, request, 10,
                            5 * 24 * 60 * 60);
                }),
            );
        } else if (request.url.includes('/i/')) {
            event.respondWith(
                caches.match(request).then(cachedResponse => {
                    return cachedResponse ||
                        cacheRequest(INFO_CACHE, request, 10, 2 * 24 * 60 * 60);
                }),
            );
        } else {
            event.respondWith(
                caches.match(request).then(cachedResponse => {
                    return cachedResponse ||
                        cacheRequest(DYNAMIC_CACHE, request, 15, 2 * 60 * 60);
                }),
            );
        }
    } else if (request.url.includes('m.media-amazon.com/images/')) {
        event.respondWith(
            caches.match(request).then(cachedResponse => {
                return cachedResponse ||
                    cacheRequest(POSTER_CACHE, request, 15,
                        7 * 24 * 60 * 60);
            }),
        );
    } else if (request.url.includes('bunny.net')) {
        event.respondWith(
            caches.match(request).then(cachedResponse => {
                return cachedResponse ||
                    cacheRequest(APP_CACHE, request, null,
                        7 * 24 * 60 * 60);
            }),
        );
    } else {
        event.respondWith(fetch(event.request));
    }
});
// Listen for messages from the page to the service worker
self.addEventListener('message', event => {
    console.log('Service Worker: Message Received', event.data);
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting().then(r => console.log('Service Worker: Skip waiting')).catch(
            error => console.info('Service Worker: Error skipping waiting'));
    }
});

// on notificationclick event. if event action is close then close either open the home url
self.addEventListener('notificationclick', (event) => {
    if (event.action === 'close') {
        event.notification.close();
    } else {
        event.waitUntil(clients.matchAll({
            type: 'window',
        }).then((clientList) => {
            if (clients.openWindow) {
                return clients.openWindow('/');
            }
        }));
    }
});

self.addEventListener('periodicsync', async (event) => {
    if (event.tag === 'notificationSync') {
        const now = new Date();
        const movieTimeStart = 19; // 7 PM
        const movieTimeEnd = 21;   // 9 PM

        if ((now.getDay() === 0 && now.getHours() >= movieTimeStart &&
                now.getHours() < movieTimeEnd) ||
            (now.getDay() === 0 && now.getHours() >= 9 && now.getHours() <
                13)) {
            if (Notification.permission === 'granted') {
                rollOpeningCredits(); // 🎬 Let the Weekend Movie Magic begin!
            } else {
                try {
                    const permission = await Notification.requestPermission();
                    if (permission === 'granted') {
                        rollOpeningCredits();
                    } else {
                        alert('You need to allow push notifications.');
                    }
                } catch (e) {
                    console.log(e);
                }
            }
        }
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
