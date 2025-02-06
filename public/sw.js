const DEBUG = true;
const broadcast = new BroadcastChannel('service-worker-channel');
const broadcastChannel = new BroadcastChannel('toast-notifications');

const APP_CACHE = 'v-4.11';
const SEARCH_CACHE = 'search-cache-v-4.11';
const INFO_CACHE = 'info-cache-v-4.11';
const DYNAMIC_CACHE = 'dynamic-cache-v-4.11';
const POSTER_CACHE = 'poster-cache-v-4.11';

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
    '/build/assets/Search-3mKUvf_4.css',
    '/build/assets/SearchCard-Df8yoEfg.css',
    '/build/assets/BaseLayout-pP2Wt1YA.css',
    '/build/assets/app-B9PvwnzM.css',
    '/build/assets/LoadingSpinner-BXRTGhSQ.js',
    '/build/assets/ApplicationLogo-D4zDxYfH.js',
    '/build/assets/InputError-Bgx30MBz.js',
    '/build/assets/InputLabel-CV84wD8h.js',
    '/build/assets/GuestLayout-0_rgDOnF.js',
    '/build/assets/LoadingSpinnerButton-VtLsMS4W.js',
    '/build/assets/PrimaryButton-D9GgbFHp.js',
    '/build/assets/Dashboard-pWjqhry5.js',
    '/build/assets/TextInput-_4aMg3ew.js',
    '/build/assets/Edit-QCmeWBKi.js',
    '/build/assets/ConfirmPassword-CAhXPfmq.js',
    '/build/assets/ForgotPassword-BBbDvbws.js',
    '/build/assets/VerifyEmail-CjpjYamz.js',
    '/build/assets/Show-DSVxtU_1.js',
    '/build/assets/ResetPassword-CEQSgKHE.js',
    '/build/assets/Error-DHDu2L5T.js',
    '/build/assets/Register-D0dvTrKu.js',
    '/build/assets/UpdatePasswordForm-DQwOwPXU.js',
    '/build/assets/UpdateProfileInformationForm-BOJ9bcdg.js',
    '/build/assets/Login-CpiPotxI.js',
    '/build/assets/Terms-C52iB6uL.js',
    '/build/assets/NewsletterForm-szNtWui9.js',
    '/build/assets/PrivacyPolicy-DzLI_0wq.js',
    '/build/assets/DeleteUserForm-BE-9RQLl.js',
    '/build/assets/SearchCard-CQNOzmaG.js',
    '/build/assets/Contact-TxWHd97u.js',
    '/build/assets/AuthenticatedLayout-CvMWH7lW.js',
    '/build/assets/Search-BuBFGrtJ.js',
    '/build/assets/Welcome-N2U8UGT6.js',
    '/build/assets/BaseLayout-CxI3g56n.js',
    '/build/assets/DetailCard-BZ0E7VHC.js',
    '/build/assets/app-B7Ovby6S.js',
    '/build/assets/Search-3mKUvf_4.css',
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
    log('Periodic sync', event);
    if (event.tag === 'weekly-trending-notification') {
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
    } else if (event.tag === 'daily-notification') {
        dailyNotification();
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

        broadcastTrackingEvent(`APP_UPDATED`, {
            event_category: 'APP_UPDATED',
            event_label: APP_CACHE,
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

        broadcastTrackingEvent('notification_closed', {
            event_category: 'notification_closed',
            event_label: "Notify closed",
        });

    } else {
        event.waitUntil(
            (async () => {
                const notification = event.notification;
                const data = notification.data || {};
                const urlToOpen = data.url || '/';

                // Get the time of day for event label (morning, afternoon, etc.)
                const hourOfDay = new Date().getHours();
                let timeOfDayLabel = '';
                if (hourOfDay >= 5 && hourOfDay < 12) {
                    timeOfDayLabel = 'Morning';
                } else if (hourOfDay >= 12 && hourOfDay < 16) {
                    timeOfDayLabel = 'Afternoon';
                } else if (hourOfDay >= 16 && hourOfDay < 20) {
                    timeOfDayLabel = 'Evening';
                } else {
                    timeOfDayLabel = 'Late Night';
                }

                // Broadcast tracking event for notification opened
                broadcastTrackingEvent('notification_opened', {
                    event_category: 'notification_opened',
                    event_label: "Notify opened",
                });

                broadcastTrackingEvent(`noti_op ${timeOfDayLabel}`, {
                    event_category: 'noti_op',
                    event_label: timeOfDayLabel,
                });

                // Check if there's an open window client
                try {
                    const clientList = await clients.matchAll({type: 'window', includeUncontrolled: true});
                    const openWindow = clientList.find(client => client.url === urlToOpen && client.visibilityState === 'visible');

                    if (openWindow) {
                        // If there's already an open window, focus it
                        openWindow.focus();
                    } else {
                        // If no open window, open a new window
                        if (clients.openWindow) {
                            if (urlToOpen.startsWith('http://') || urlToOpen.startsWith('https://')) {
                                await clients.openWindow(urlToOpen);
                            } else {
                                await clients.openWindow('/');
                            }
                        } else {
                            // Fallback for non-Service Worker environments (e.g., browser window)
                            if (urlToOpen.startsWith('http://') || urlToOpen.startsWith('https://')) {
                                window.open(urlToOpen, '_blank');
                            } else {
                                window.open('/', '_blank');
                            }
                        }
                    }
                } catch (err) {
                    // Handle potential errors, e.g., if clients.matchAll fails
                    console.error('Error matching client windows:', err);
                    // Fallback to opening the home URL if there's an issue
                    if (clients.openWindow) {
                        await clients.openWindow('/');
                    }
                }
            })()
        );
    }
}

self.addEventListener('sync', async (event) => {
    if (event.tag === 'offline-search-sync') {
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
            badge: 'https://movieguru.shakiltech.com/icons/ios/192.png',
            icon: 'https://movieguru.shakiltech.com/icons/ios/72.png',
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

        broadcastTrackingEvent(`OFFLINE_SYNCED_SUCCESS`, {
            event_category: 'OFFLINE_SYNCED_SUCCESS',
            event_label: searchQuery,
        });

        broadcastTrackingEvent(`${searchQuery}offline`, {
            event_category: 'OFFLINE_SYNCED_SUCCESS',
            event_label: searchQuery,
        });
    } catch (error) {
        logError('Error occurred while fetching offline request', error);
    }

}

function dailyNotification() {
    const now = new Date();
    const hourOfDay = now.getHours();
    const dayOfWeek = now.getDay();
    const month = now.getMonth();
    const date = now.getDate();

    // Time-of-day specific messages
    const morningMessages = [
        "🌅 Morning Glory! Time to kickstart your day with some cinematic magic! 🎬",
        "☀️ Rise and Shine! Your movie adventure begins now. Grab your coffee! ☕",
        "🌞 Good Morning! The screen is set for your next movie masterpiece. 🍿",
        "🎬 Movie time, before the world wakes up. Start your day with a plot twist! 😎",
        "🛋️ Who needs a gym when you have a couch? Let's binge! 💪",
        "☕ Morning fuel: Coffee + Movie! What’s your choice today? 🎥",
        "🌅 Let’s make today legendary—start with a movie! 🍿",
        "👀 Morning movie magic is the best way to wake up. Let’s go! 🎬",
        "⏰ Early bird catches the best flicks. Grab your popcorn! 🍿",
        "🎥 Good vibes and good movies—because mornings should be epic!"
    ];

    const afternoonMessages = [
        "🎥 Afternoon Delights: The perfect time to unwind with a great movie. 🌟",
        "🌤️ Midday Marvels: The movie world is waiting—what's on your watchlist today?",
        "🍿 Afternoon Binge: The spotlight's on you, star! Which movie are you craving today?",
        "☕ Coffee break? More like movie break! 🎬 Time to relax!",
        "⏳ The afternoon slump just met its match: Movie time! 🎥",
        "🛋️ Take a break, press play, and let the movie marathon begin. 🍿",
        "🌞 The afternoon sun is setting, but the binge session is just beginning. 🌇",
        "🎬 Afternoon movie therapy is the best kind of therapy. Let’s do this!",
        "📅 Your afternoon just got better: Movie marathon mode activated!",
        "🍫 Afternoon treat: A little movie magic for your soul!"
    ];

    const eveningMessages = [
        "🌇 Evening Vibes: It's showtime! Let the marathon begin! 🌟",
        "🌙 Prime Time: Your perfect movie companion for the evening is just a click away. 🎬",
        "✨ Evening Rush: The movies are calling! Ready for your next great binge? 🍿",
        "🍿 Movie night just got real—who's ready for the first feature? 🎥",
        "🎬 The night is young and so are the movies. Get comfy and press play!",
        "🛋️ Time for your evening relaxation therapy. Movies await! 🎥",
        "🌙 Evening lights, movie nights. What's on your watchlist tonight?",
        "🎥 From sunset to screen: Your perfect movie awaits! 🌇",
        "✨ The night belongs to movie lovers. What’s your pick tonight?",
        "🌌 The stars are out, the movie is about to start. Let’s go, movie lover! 🍿"
    ];

    const lateNightMessages = [
        "🌙 Late Night Cinematic Bliss: Perfect time to dream with a great film. 🌙",
        "🌌 Night Owl's Binge: Your late-night movie escape awaits... 🎬",
        "🌑 Nighttime Flicks: Time to unwind and let the movie magic happen. ✨",
        "🎬 Who needs sleep when there's a movie marathon waiting? 🌙",
        "🦉 Late night movies: Because who doesn't want to live on the edge? 🎥",
        "🌠 Stars on the screen and stars in the sky—perfect time for a late-night binge! 🍿",
        "🎥 The night calls for a good flick. Get comfy, the show’s about to start. 🌙",
        "🌌 Midnight movie vibes—popcorn in hand, movie on screen. 🎬",
        "🌙 No better time to let the screen take you to another world. Movie time!",
        "🛏️ Late night, movie lights. Time to end the day with a film!"
    ];

    // Special Day Messages (New Year, Christmas, Weekend, Sunday, Monday)
    const holidayMessages = {
        newYear: [
            "🎉 New Year, New Flicks! Time to kick off the year with a movie binge! 🍿",
            "🎆 Happy New Year! What better way to start than with a movie marathon? 🎬",
            "✨ Cheers to new beginnings! Ring in the new year with your favorite films! 🍾"
        ],
        christmas: [
            "🎄 Ho Ho Ho! It’s Christmas movie time! Grab the eggnog and let’s go! 🍿",
            "🎅 Tis the season for a movie marathon! What’s your holiday classic? 🎥",
            "🌟 Merry Christmas! Cozy up with a movie and enjoy the magic of the season! 🎬"
        ],
        fridayEvening: [
            "🎉 Friday night’s here! Time to put the workweek to rest and press play on some epic movies. 🍿",
            "✨ The weekend's calling, and it starts with a movie marathon. Get comfy, it's showtime! 🎬",
            "🥳 Friday feels: It’s time to kick back with popcorn and let the movie magic take over! 🍿",
            "🌟 Friday night vibes = Movie mode activated. What’s your first flick? 🍿🎥",
            "🕺 The work week’s done! Time to dive into a movie binge that lasts all night. Who's in? 🍿",
            "🎥 Friday’s here to save you from reality. Choose your movie and get comfy! 🛋️",
            "🍿 Start your weekend off right: Great movies, cozy vibes, and no alarms tomorrow! 🎬",
            "🎉 It’s Friday night! Time to do absolutely nothing except watch movies. Let’s go! 🛋️",
            "📅 Weekend = movies + popcorn. Let’s make this Friday night unforgettable! 🎥🍿",
            "⚡ The weekend begins NOW! Movies, snacks, and zero responsibilities. 🛋️🍿"
        ],
        weekend: [
            "🎉 Weekend vibes: Time to kick back, relax, and enjoy a movie marathon! 🍿",
            "🛋️ The weekend is here—let’s get comfy and binge-watch all the movies! 🎬",
            "🍿 It’s the weekend! Movie time, snack time, all the good times! 🎥",
            "🌞 Saturday's here! Perfect day for an all-day movie binge. What’s first on the list? 🍿",
            "🎬 Saturday vibes: Settle in, relax, and let the movie magic begin! ✨",
            "🍿 It’s Saturday, let’s get comfy with a marathon! Who’s in for movie madness? 🎥",
            "🎉 Saturdays are for doing nothing... except watching movies. Ready to roll? 🎬",
            "🕶️ Lazy Saturday = Movie marathon day! Grab the popcorn and hit play. 🍿",
            "🎥 Saturday mood: Movies, popcorn, and zero plans. Who needs them anyway? 🍿🍿",
            "🎬 Ready for the weekend binge fest? Saturday's perfect for it! Grab your popcorn! 🍿",
            "☀️ Saturday morning movie vibes are calling... and you MUST answer. 🎥",
            "🍕 Saturday = Movie night all day long. Ready to get cozy and indulge? 🎬",
            "🍿 Saturday's calling: The best way to spend it is in front of the screen. Let’s roll!"
        ],
        sunday: [
            "🌞 Sunday Funday: The perfect day for a movie binge. Let’s do this! 🍿",
            "🎬 Sunday means movie day. Ready to end your weekend in cinematic style? 🍿",
            "🛋️ Cozy Sunday, popcorn in hand. Movie marathon, here we come! 🎥",
            "🌞 Sunday chill mode: Let’s get comfy with some movies, snacks, and zero stress! 🍿",
            "🎬 Sunday Funday starts now! Ready for some cozy movie magic? 🍿",
            "☕ Sunday mornings + movies = Perfection. Grab your popcorn and start the show! 🍿",
            "🎉 Sunday is for movie marathons. Who's in for the ultimate binge session? 🍿🎥",
            "🌟 Sunday + movies = The ultimate relaxation recipe. Get ready to dive in! 🎬",
            "🍿 Sundays were made for lazy movie days. Let’s start your binge right. 🎥",
            "🎥 Sunday vibes: Relax, rewind, and enjoy a movie marathon. 🛋️🍿",
            "🍕 Sunday + movies = The perfect combination. Are you ready to binge-watch? 🎬",
            "🎬 Sundays are for unwinding and watching the best flicks. Grab the popcorn! 🍿",
            "🌙 Sunday night = A good movie, good food, and good vibes. Let’s do this! 🎥🍿"
        ],
        monday: [
            "⏰ Monday Motivation: Get ready to conquer the week with a movie escape! 🎬",
            "📅 It’s Monday—time to start the week with a bang! Movie time! 🍿",
            "🌞 Monday blues? Turn them into movie gold. What’s on today’s list? 🎥"
        ]
    };

    // Function to pick a random message based on the time of day and special days
    const messages = (function () {
        if (month === 11 && date === 25) {  // Christmas
            return holidayMessages.christmas;
        } else if (month === 0 && date === 1) {  // New Year
            return holidayMessages.newYear;
        } else if (dayOfWeek === 5 && hourOfDay >= 18) {  // Friday Evening
            return holidayMessages.fridayEvening;
        } else if (dayOfWeek === 0) {  // Sunday
            return holidayMessages.sunday;
        } else if (dayOfWeek === 6) {  // Saturday (Weekend)
            return holidayMessages.weekend;
        } else if (dayOfWeek === 1) {  // Monday
            return holidayMessages.monday;
        } else if (hourOfDay >= 5 && hourOfDay < 12) {
            return morningMessages;
        } else if (hourOfDay >= 12 && hourOfDay < 16) {
            return afternoonMessages;
        } else if (hourOfDay >= 16 && hourOfDay < 20) {
            return eveningMessages;
        } else {
            return lateNightMessages;
        }
    })();

    const randomMessage = messages[Math.floor(Math.random() * messages.length)];

    self.registration.showNotification('🎬 Your Movie Awaits!', {
        tag: 'alert',
        body: randomMessage,
        badge: 'https://movieguru.shakiltech.com/icons/ios/192.png',
        icon: 'https://movieguru.shakiltech.com/icons/ios/72.png',
        actions: [
            {
                action: 'close',
                title: 'Not Now',
            },
            {
                action: 'open',
                type: 'button',
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

    // Get the time of day as a label (morning, afternoon, evening, etc.)
    let timeOfDayLabel = '';
    if (hourOfDay >= 5 && hourOfDay < 12) {
        timeOfDayLabel = 'Morning';
    } else if (hourOfDay >= 12 && hourOfDay < 16) {
        timeOfDayLabel = 'Afternoon';
    } else if (hourOfDay >= 16 && hourOfDay < 20) {
        timeOfDayLabel = 'Evening';
    } else {
        timeOfDayLabel = 'Late Night';
    }

    broadcastTrackingEvent('notification_sent', {
        event_category: 'notification_sent',
        event_label: "Notify sent",
    });

    broadcastTrackingEvent(timeOfDayLabel, {
        event_category: timeOfDayLabel,
        event_label: timeOfDayLabel,
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
        icon: 'https://movieguru.shakiltech.com/icons/ios/72.png',
        badge: 'https://movieguru.shakiltech.com/icons/ios/192.png',
        actions: [
            {action: 'close', title: 'Not Now'},
            {action: 'open', title: 'Check It Out!'},
        ],
        data: notificationData,
        requireInteraction: true,
        vibrate: [200, 100, 200],
    });

    broadcastTrackingEvent('trending_notification_sent', {
        event_category: 'trending_notification_sent',
        event_label: "Trending Notify sent",
    });
}

function broadcastTrackingEvent(eventType, details) {
    broadcast.postMessage({
        type: 'EVENT_TRACKING',
        title: eventType,
        details: details,
        timestamp: new Date().toISOString(),
    });
}
