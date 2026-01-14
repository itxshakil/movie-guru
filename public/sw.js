const DEBUG = false;
const broadcast = new BroadcastChannel('service-worker-channel');
const broadcastChannel = new BroadcastChannel('toast-notifications');

const APP_CACHE = 'v-4.9.0';
const SEARCH_CACHE = 'search-cache-v-4.9.0';
const INFO_CACHE = 'info-cache-v-4.9.0';
const DYNAMIC_CACHE = 'dynamic-cache-v-9.0';
const POSTER_CACHE = 'poster-cache-v-4.9.0';

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
    '/build/assets/Search-e4G1Minw.css',
    '/build/assets/Show-Df8yoEfg.css',
    '/build/assets/BaseLayout-pP2Wt1YA.css',
    '/build/assets/app-DSy5iFBc.css',
    '/build/assets/LoadingSpinner-BU7dk2m5.js',
    '/build/assets/ApplicationLogo-BQHs3_bv.js',
    '/build/assets/InputError-DjZrqUel.js',
    '/build/assets/InputLabel-BZGY0ZGV.js',
    '/build/assets/GuestLayout-DGMWhwZV.js',
    '/build/assets/LoadingSpinnerButton-DzWDITnR.js',
    '/build/assets/PrimaryButton-DSVAjU1g.js',
    '/build/assets/Dashboard-DTUke1ab.js',
    '/build/assets/TextInput-SXJDLFhX.js',
    '/build/assets/Edit-yeA2b6Ug.js',
    '/build/assets/ConfirmPassword-BbEev210.js',
    '/build/assets/ForgotPassword-2V5MANYh.js',
    '/build/assets/VerifyEmail-CaquUBbK.js',
    '/build/assets/ResetPassword-4qPJgm4-.js',
    '/build/assets/Error-DcjWU1a8.js',
    '/build/assets/Show-CDCCZoLp.js',
    '/build/assets/Register-CiUtyuj_.js',
    '/build/assets/UpdatePasswordForm-CNLP94KP.js',
    '/build/assets/UpdateProfileInformationForm-DR7O1XRp.js',
    '/build/assets/Login-BWdODVsA.js',
    '/build/assets/Show-lqJcHn7K.js',
    '/build/assets/Terms-DgsXzjK9.js',
    '/build/assets/NewsletterForm-_GYFmV_h.js',
    '/build/assets/PrivacyPolicy-BBfZ-SWc.js',
    '/build/assets/DeleteUserForm-DhcSJa2c.js',
    '/build/assets/Contact-BgPCWz7S.js',
    '/build/assets/AuthenticatedLayout-D1FGyYHH.js',
    '/build/assets/Search-D1vAZIbv.js',
    '/build/assets/Welcome-0glZftcJ.js',
    '/build/assets/BaseLayout-BNjVq-Tr.js',
    '/build/assets/SearchCard-CF7yKGQq.js',
    '/build/assets/app-BHD-2r4M.js',
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
                        openWindow.focus();
                        event.notification.close();
                    } else {
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

                        event.notification.close();
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
            badge: 'https://movieguru.shakiltech.com/icons/android/android-launchericon-96-96.png',
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

async function dailyNotification() {
    if (Notification.permission !== 'granted') return;

    const cache = await caches.open(DYNAMIC_CACHE);
    const lastNotif = await cache.match('last-notification-time');
    const now = new Date();

    if (lastNotif) {
        const lastTime = new Date(await lastNotif.text());
        const hoursSinceLast = (now - lastTime) / (1000 * 60 * 60);
        if (hoursSinceLast < 20) return; // Max once per day
    }

    const hourOfDay = now.getHours();
    const dayOfWeek = now.getDay();
    const month = now.getMonth();
    const date = now.getDate();

    // Time-of-day specific messages
    const morningMessages = [
        "🌅 Morning Glory! Time to kickstart your day with some cinematic magic! 🎬 (Better than emails, right?)",
        "☀️ Rise and Shine! Your movie adventure begins now. Grab your coffee! ☕ (Or two, no judgment.)",
        "🌞 Good Morning! The screen is set for your next movie masterpiece. 🍿 (Don’t worry, pants are optional.)",
        "🎬 Movie time, before the world wakes up. Start your day with a plot twist! 😎",
        "🛋️ Who needs a gym when you have a couch? Let's binge! 💪 (Calories don’t count in the morning.)",
        "☕ Morning fuel: Coffee + Movie! What’s your choice today? 🎥 (Espresso shots = extra plot twists.)",
        "🌅 Let’s make today legendary—start with a movie! 🍿",
        "👀 Morning movie magic is the best way to wake up. Let’s go! 🎬 (Sorry, alarm clock.)",
        "⏰ Early bird catches the best flicks. Grab your popcorn! 🍿 (Productivity can wait.)",
        "🎥 Good vibes and good movies—because mornings should be epic!",
        "⚡ Start the day with drama, action, or laughter—your choice, superstar! 🌟",
        "💡 Morning hack: Movies first, responsibilities later. 😉",
        "🍳 Breakfast is optional. Popcorn is mandatory. 🍿",
        "🚀 Launch your day with a blockbuster, not an alarm clock. 🎬",
        "🌞 Mornings are brighter with a little movie chaos. Ready? 🎥"
    ];

    const afternoonMessages = [
        "🎥 Afternoon Delights: The perfect time to unwind with a great movie. 🌟 (Meetings who?)",
        "🌤️ Midday Marvels: The movie world is waiting—what's on your watchlist today? (Better than a nap.)",
        "🍿 Afternoon Binge: The spotlight's on you, star! Which movie are you craving today? (Snacks mandatory.)",
        "☕ Coffee break? More like movie break! 🎬 Time to relax! (Boss doesn’t need to know.)",
        "⏳ The afternoon slump just met its match: Movie time! 🎥 (Goodbye yawns, hello popcorn.)",
        "🛋️ Take a break, press play, and let the movie marathon begin. 🍿 (Best productivity hack.)",
        "🌞 The afternoon sun is setting, but the binge session is just beginning. 🌇 (Curtains closed = cinema vibes.)",
        "🎬 Afternoon movie therapy is the best kind of therapy. Let’s do this! (No co-pay required.)",
        "📅 Your afternoon just got better: Movie marathon mode activated! (Zero regrets.)",
        "🍫 Afternoon treat: A little movie magic for your soul! (Better than sugar rush.)",
        "🍫 Afternoon treat: A little movie magic for your soul!",
        "⚡ Power up your afternoon with some cinematic fuel! 🎬",
        "🥤 Lunchtime is done. Showtime has just begun! 🍿",
        "🎯 Afternoon slump cure = one epic movie scene. Guaranteed. 🎥",
        "🌞 Bright sun outside, brighter screen inside. Let’s roll! 🍿",
        "📺 Plot twist: Your productivity today = 0. Your movie marathon = 100. 🔥",
    ];

    const eveningMessages = [
        "🌇 Evening Vibes: It's showtime! Let the marathon begin! 🌟 (Dinner can wait.)",
        "🌙 Prime Time: Your perfect movie companion for the evening is just a click away. 🎬 (Couch included.)",
        "✨ Evening Rush: The movies are calling! Ready for your next great binge? 🍿 (Don’t ghost them.)",
        "🍿 Movie night just got real—who's ready for the first feature? 🎥 (Double features = win.)",
        "🎬 The night is young and so are the movies. Get comfy and press play! (Blankets recommended.)",
        "🛋️ Time for your evening relaxation therapy. Movies await! 🎥 (Stress = gone.)",
        "🌙 Evening lights, movie nights. What's on your watchlist tonight? (Stars + screen = perfection.)",
        "🎥 From sunset to screen: Your perfect movie awaits! 🌇 (Golden hour, but indoors.)",
        "✨ The night belongs to movie lovers. What’s your pick tonight? (No curfew here.)",
        "🍕 Pizza, popcorn, and pure movie magic—this is your evening plan. 🍿 (Calories = happiness points.)",
        "🌌 The stars are out, the movie is about to start. Let’s go, movie lover! 🍿",
        "🍕 Pizza, popcorn, and pure movie magic—this is your evening plan. 🍿",
        "🎬 Prime time = YOUR time. Let’s roll the opening credits. 🌟",
        "💡 Idea: Cancel all plans. Watch movies instead. 😉",
        "🌙 Moonlight + Movies = The perfect night combo. 🎥",
        "🎧 Add soundtracks to your soul—start your binge now! 🍿"
    ];

    const lateNightMessages = [
        "🌙 Late Night Cinematic Bliss: Perfect time to dream with a great film. 🌙 (Sleep is overrated.)",
        "🌌 Night Owl's Binge: Your late-night movie escape awaits... 🎬 (Eyes wide open.)",
        "🌑 Nighttime Flicks: Time to unwind and let the movie magic happen. ✨ (Insomnia cure unlocked.)",
        "🎬 Who needs sleep when there's a movie marathon waiting? 🌙 (Your pillow understands.)",
        "🦉 Late night movies: Because who doesn't want to live on the edge? 🎥 (Risk level: popcorn crumbs.)",
        "🌠 Stars on the screen and stars in the sky—perfect time for a late-night binge! 🍿 (Galaxy brain move.)",
        "🎥 The night calls for a good flick. Get comfy, the show’s about to start. 🌙 (Blanket fortress optional.)",
        "🌌 Midnight movie vibes—popcorn in hand, movie on screen. 🎬 (Perfect combo.)",
        "🔥 The city sleeps, but your movie playlist doesn’t. 🎥 (Legendary energy.)",
        "🦉 Night owls unite—movie marathons are our superpower! 🍿 (We don’t do mornings.)",
        "🌙 No better time to let the screen take you to another world. Movie time!",
        "🛏️ Late night, movie lights. Time to end the day with a film!",
        "🌙 Midnight = perfect time for a cinematic escape. Don’t fight it. 🎬",
        "🚀 Sleep is overrated. Movies are eternal. 🌌",
        "👀 Late-night binge? You’re living the dream. 🍿",
    ];

    // Special Day Messages (New Year, Christmas, Weekend, Sunday, Monday)
    const holidayMessages = {
        newYear: [
            "🎉 New Year, New Flicks! Time to kick off the year with a movie binge! 🍿 (Resolution: binge responsibly.)",
            "🎆 Happy New Year! What better way to start than with a movie marathon? 🎬 (Hangover cure: movies.)",
            "✨ Cheers to new beginnings! Ring in the new year with your favorite films! 🍾 (Blockbusters > fireworks.)",
            "🎇 Resolution idea: More movies, less stress. 💡 (Best self-care hack.)",
            "🎆 New year = new watchlist! Let’s crush it together. 🎬",
            "🍾 Ring in the year with plot twists and popcorn explosions. 🍿",
            "🎇 Resolution idea: More movies, less stress. 💡"
        ],
        christmas: [
            "🎄 Ho Ho Ho! It’s Christmas movie time! Grab the eggnog and let’s go! 🍿 (Santa approves.)",
            "🎅 Tis the season for a movie marathon! What’s your holiday classic? 🎥 (Bonus points: pajamas all day.)",
            "🌟 Merry Christmas! Cozy up with a movie and enjoy the magic of the season! 🎬 (Holiday spirit = 100.)",
            "❄️ Snow, cocoa, and Christmas classics. Movie heaven unlocked! 🍿 (Snow optional.)",
            "🎁 The best gift = cozy movies all day long. 🎬",
            "✨ Forget the chimney, Santa’s bringing movies straight to your screen! 🎅"
        ],
        fridayEvening: [
            "🎉 Friday night’s here! Time to put the workweek to rest and press play on some epic movies. 🍿 (No alarms tomorrow.)",
            "✨ The weekend's calling, and it starts with a movie marathon. Get comfy, it's showtime! 🎬 (Best happy hour = movies.)",
            "🥳 Friday feels: It’s time to kick back with popcorn and let the movie magic take over! 🍿 (Emails ignored.)",
            "🕺 The work week’s done! Time to dive into a movie binge that lasts all night. Who's in? 🍿 (After-party = Netflix.)",
            "🍿 Start your weekend off right: Great movies, cozy vibes, and no alarms tomorrow! 🎬 (Freedom unlocked.)",
            "🌟 Friday night vibes = Movie mode activated. What’s your first flick? 🍿🎥",
            "🎥 Friday’s here to save you from reality. Choose your movie and get comfy! 🛋️",
            "🍿 Start your weekend off right: Great movies, cozy vibes, and no alarms tomorrow! 🎬",
            "🎉 It’s Friday night! Time to do absolutely nothing except watch movies. Let’s go! 🛋️",
            "📅 Weekend = movies + popcorn. Let’s make this Friday night unforgettable! 🎥🍿",
            "⚡ The weekend begins NOW! Movies, snacks, and zero responsibilities. 🛋️🍿",
            "🎬 Weekend loading... First step: Movies! 🍿",
            "🍹 Drinks? Nah. Popcorn towers? Absolutely. 🍿",
            "⚡ Friday = Permission to binge without guilt. 🎥",
            "🥳 End the week strong—with movies, not emails. 🍿",
            "🎉 Your boss doesn’t know this, but movies are mandatory tonight. 😉"
        ],
        weekend: [
            "🎉 Weekend vibes: Time to kick back, relax, and enjoy a movie marathon! 🍿 (Laundry can wait.)",
            "🛋️ The weekend is here—let’s get comfy and binge-watch all the movies! 🎬 (Pajamas mandatory.)",
            "🍿 It’s the weekend! Movie time, snack time, all the good times! 🎥 (Snacks > chores.)",
            "🎬 Saturday vibes: Settle in, relax, and let the movie magic begin! ✨ (Errands cancelled.)",
            "🎯 Weekend checklist: Snacks ✅ Movies ✅ Zero responsibilities ✅ (Best list ever.)",
            "🌞 Saturday's here! Perfect day for an all-day movie binge. What’s first on the list? 🍿",
            "🍿 It’s Saturday, let’s get comfy with a marathon! Who’s in for movie madness? 🎥",
            "🎉 Saturdays are for doing nothing... except watching movies. Ready to roll? 🎬",
            "🕶️ Lazy Saturday = Movie marathon day! Grab the popcorn and hit play. 🍿",
            "🎥 Saturday mood: Movies, popcorn, and zero plans. Who needs them anyway? 🍿🍿",
            "🎬 Ready for the weekend binge fest? Saturday's perfect for it! Grab your popcorn! 🍿",
            "☀️ Saturday morning movie vibes are calling... and you MUST answer. 🎥",
            "🍕 Saturday = Movie night all day long. Ready to get cozy and indulge? 🎬",
            "🍿 Saturday's calling: The best way to spend it is in front of the screen. Let’s roll!",
            "🎯 Weekend checklist: Snacks ✅ Movies ✅ Zero responsibilities ✅",
            "💤 Lazy day = Legendary movie marathon. 🛋️",
            "🍕 Movies taste better on weekends. Fact. 🍿",
            "⚡ Saturday + Screen = Pure happiness unlocked. 🎬",
            "🎉 Your weekend + movies = blockbuster lifestyle. 🌟"
        ],
        sunday: [
            "🌞 Sunday Funday: The perfect day for a movie binge. Let’s do this! 🍿 (Self-care level: unlocked.)",
            "🎬 Sunday means movie day. Ready to end your weekend in cinematic style? 🍿 (Pro tip: nap during credits.)",
            "🛋️ Cozy Sunday, popcorn in hand. Movie marathon, here we come! 🎥 (Reset button engaged.)",
            "🌞 Sunday chill mode: Let’s get comfy with some movies, snacks, and zero stress! 🍿 (Perfect recharge.)",
            "🍕 Sunday + movies = The perfect combination. Are you ready to binge-watch? 🎬 (Pizza > productivity.)",
            "🎬 Sunday Funday starts now! Ready for some cozy movie magic? 🍿",
            "☕ Sunday mornings + movies = Perfection. Grab your popcorn and start the show! 🍿",
            "🎉 Sunday is for movie marathons. Who's in for the ultimate binge session? 🍿🎥",
            "🌟 Sunday + movies = The ultimate relaxation recipe. Get ready to dive in! 🎬",
            "🍿 Sundays were made for lazy movie days. Let’s start your binge right. 🎥",
            "🎥 Sunday vibes: Relax, rewind, and enjoy a movie marathon. 🛋️🍿",
            "🍕 Sunday + movies = The perfect combination. Are you ready to binge-watch? 🎬",
            "🎬 Sundays are for unwinding and watching the best flicks. Grab the popcorn! 🍿",
            "🌙 Sunday night = A good movie, good food, and good vibes. Let’s do this! 🎥🍿",
            "🛋️ Sundays were MADE for movie naps between scenes. 🍿",
            "☀️ Recharge the soul: Movies > Chores. 🎬",
            "🍫 Sweet Sundays deserve sweet flicks. 🍿",
            "📅 Tomorrow’s Monday... but today, it’s MOVIE DAY! 🎥",
            "⚡ Sunday movie vibes = guaranteed happiness. 🌟"
        ],
        monday: [
            "⏰ Monday Motivation: Get ready to conquer the week with a movie escape! 🎬 (Emails can wait.)",
            "📅 It’s Monday—time to start the week with a bang! Movie time! 🍿 (Meetings postponed.)",
            "🌞 Monday blues? Turn them into movie gold. What’s on today’s list? 🎥 (Better cure than coffee.)",
            "🚀 Start the week strong—with popcorn power! 🍿 (Energy = snacks + movies.)",
            "💼 Work can wait. Movies can’t. 🎬 (Boss doesn’t need to know.)",
            "🚀 Start the week strong—with popcorn power! 🍿",
            "🌞 Monday mood flip: One good movie and the blues disappear. 🎥",
            "⚡ Power move: Watch a movie before emails. Productivity skyrockets! 💡"
        ],
        valentines: [
            "❤️ Movies > Roses. Popcorn > Flowers. Valentine’s = Sorted. 🍿",
            "💘 Love + Buttered Popcorn = The only couple goals you need. 🥰",
            "🌹 Roses wilt, popcorn pops. Movies last forever. 🎬",
            "💕 Fancy dinner? Nah. Cozy movies in PJs = peak Valentine’s hack. 😍",
            "💞 No heartbreaks here—movies are the real soulmates. 🎥",
            "🎬 Love story marathon = Valentine’s dopamine overdose. ❤️",
            "🍫 Chocolates melt… but your watchlist won’t. Happy Valentine’s! 🍿",
            "💖 Best Valentine’s gift? Popcorn bucket + endless screen love. 🎬",
            "🥰 Celebrate love your way—with movies that never ghost. 🍿",
            "🔥 Skip the clichés. Movies + Chill = Valentine’s gold. 💘",
            "🌙 Candlelight? Overrated. Movie light = perfect vibe. 🎥",
            "💌 Nothing says ‘I love you’ like sharing popcorn. 🍿",
            "🎧 Swap cheesy songs with Bollywood love hits marathon. 🎶",
            "💃 Dance, romance, re-watch DDLJ again—classic Valentine’s therapy. ❤️",
            "🎭 Love in 4K > Love letters on paper. Happy Valentine’s! 💌",
            "🚨 Valentine’s Hack: Movies never ask, ‘Where’s this going?’ 🎬",
            "😂 Reminder: Popcorn won’t judge you for being single today. 🍿",
            "🔥 PSA: SRK movies may cause sudden ‘Palat!’ reflexes. 💕",
            "💕 Valentine’s hack: Cozy movie > Expensive dinner. (PJs allowed!)",
            "💞 Movies don’t ghost you. Celebrate love with your screen soulmate. 🎥",
            "🥰 Celebrate love your way—with movies that never break your heart. 🍿",
            "🚀 Single? Perfect. You don’t have to share your popcorn today. 💯",
            "😎 Valentine’s flex: Watching movies solo = Zero drama, full control. 🎬",
            "👯‍♀️ Galentine’s/Palentine’s = Movie marathon with your besties. 🍿",
            "💡 Reminder: You + Couch + Movie = Happily Ever After. 🛋️❤️",
            "😂 Plot twist: Your movie’s more romantic than your last date. 🍿",
            "📽️ Valentine’s isn’t just for couples—your watchlist loves you back. 😉",
            "🌌 Escape the clichés: Dive into fantasy, sci-fi, or thrillers tonight. Your Valentine = cinema magic. ✨"
        ],

        republicDay: [
            "🇮🇳 Republic Day binge: Deshbhakti + Blockbusters = Perfect vibe. 🎬",
            "🎆 Feel the tiranga pride with stories that shaped Bharat. Jai Hind! 🇮🇳",
            "📜 Cinema that celebrates our Constitution, courage & unity. 🎥",
            "🇮🇳 Jai Hind + Popcorn shower = Republic Day marathon mode. 🍿",
            "⚡ Tiranga feels + Patriot flicks = High-voltage Republic Day! 🎇",
            "🎖️ Salute to real heroes, celebrate with reel heroes. 🎬",
            "🕊️ Freedom stories, unity vibes, powerful cinema. Jai Hind! 🇮🇳",
            "🎺 Parade in the streets, patriotism on the screen. 🎥",
            "🎆 Blockbusters that roar ‘Mera Bharat Mahaan’. 🇮🇳",
            "🔥 Patriotic marathons > Boring speeches. 🍿",
            "🇮🇳 Popcorn in one hand, flag in the other. Perfect 26th Jan! 🎬",
            "💪 Bollywood deshbhakti mode: On. 🎥",
            "🎇 Celebrate azadi with goosebump-worthy cinema. 🇮🇳",
            "📺 Tiranga spirit streaming loud & proud! 🎉",
            "🎉 Patriotic playlists + patriotic flicks = double vibes. 🇮🇳",
            "😂 Plot twist: Your neighbor’s louder than the Republic Day parade. 🎺",
            "🚨 Warning: Overdose of deshbhakti movies may cause sudden ‘Jai Hind’ outbursts. 🇮🇳",
            "🔥 Secret tip: Replace parade drumrolls with dhol beats from Lagaan. 🥁",
        ],

        independenceDay: [
            "🇮🇳 Independence Day = Azadi to binge nonstop patriotic sagas! 🎬",
            "🎆 Sky painted with Tiranga, screen lit with blockbusters. Jai Hind! 🇮🇳",
            "🍿 Freedom tastes like popcorn + endless cinema. Happy Azadi! 🎥",
            "🔥 Heroic stories + Deshbhakti beats = Independence Day binge. 🇮🇳",
            "🎇 Azadi vibes on max: Patriotic marathons, desi pride forever. Jai Bharat! 🇮🇳",
            "🎖️ Stories of courage that still give goosebumps. 🎬",
            "📺 Tiranga + Popcorn = Best Independence Day combo. 🍿",
            "🕊️ Freedom to choose your movie marathon = True Azadi. 🎥",
            "🎉 Patriotic cinema > Fireworks outside. Jai Hind! 🇮🇳",
            "💪 Bollywood blockbusters screaming Bharat Mata Ki Jai! 🎆",
            "🎆 Independence feels: National anthem + DDLJ train scene. 🇮🇳",
            "🎇 From border heroes to reel legends—binge them all. 🎥",
            "🍿 This 15th Aug, let cinema unite us all. Jai Bharat! 🇮🇳",
            "🎺 Tiranga pride streaming louder than the parade bands. 🎶",
            "⚡ Azadi marathons that hit harder than crackers. 🎬",
            "🚨 Independence Day cheat code: Replace firecrackers with popcorn pops. 🍿",
            "😂 Warning: Too much deshbhakti may cause you to salute the TV. 📺🇮🇳",
            "🔥 Hack: Deshbhakti movies = best excuse to cry proudly in public. 😢🇮🇳",
        ],

    };

    const regularMessages = [
        "🍿 Movies: the best excuse to ignore reality for 2 hours.",
        "🎬 A film a day keeps the boredom away.",
        "✨ Nothing kills stress faster than pressing ‘Play’.",
        "🔥 Tonight deserves a blockbuster ending.",
        "🌙 Good night, good vibes, great movie.",
        "🎞️ Why live one life when you can live a hundred through films?",
        "💡 Movies: cheaper than therapy, and just as effective.",
        "🚪 Step into another world—your ticket is just one click.",
        "🎥 Even Mondays look better through a movie lens.",
        "⏳ Time spent watching movies is never wasted.",
        "🚨 Warning: Watching movies today may cause uncontrollable happiness 🍿",
        "🤯 Plot twist: You’re the main character, and popcorn is your sidekick.",
        "🍟 Fact: Fries + Movies = scientifically proven mood booster. Don’t @ me.",
        "🦸‍♂️ Hero mode unlocked: Press play and save the world (from boredom).",
        "📺 99% productivity lost, 100% satisfaction gained. Worth it.",
        "😂 Rare achievement unlocked: Binge Legend Status 🏆",
        "🍕 Pizza delivery guy is basically your co-star tonight.",
        "👽 If aliens invade, they better bring snacks for movie night.",
        "🥱 Sleep is optional. Movies are eternal. (Doctors hate this one trick!)",
        "🎉 Surprise cameo: YOU in a blanket burrito binge session.",
    ];

    const rareMessages = [
        "🚨 Warning: Side effect of today’s movie may include popcorn overdose.",
        "🕵️ You’ve unlocked ‘Cinephile Mode.’ Proceed with extreme excitement.",
        "🤯 Did you know? Popcorn tastes 83% better during suspense scenes.",
        "⚡ Movie nights cure boredom faster than WiFi fixes depression.",
        "🎲 Dare mode: pick the weirdest title you see and roll with it.",
        "🪄 Fun fact: Movies secretly double your charisma score.",
        "🐼 Rare message unlocked. You’re officially in the Movie Elite Club.",
        "💃 Mandatory rule: musicals require loud, shameless singing.",
        "🚀 Tonight’s screening: Escape From Reality (runtime: infinite).",
        "🍿 You’ve been randomly selected for Unlimited Popcorn Powers.* *Terms: imagination only."
    ];

    const legendaryMessages = [
        "👑 Congrats. You’ve just unlocked the **Director’s Cut of Life**. Spoiler: You win.",
        "🎬 Plot twist: You’re not watching the movie. The movie is watching YOU.",
        "🚨 This message is rarer than free Netflix. Take a bow, chosen one.",
        "🦄 Achievement unlocked: **Cinematic Immortal**. Your end credits will never roll.",
        "⚡ Breaking news: Hollywood has cast you as the lead in *Reality 2*.",
        "🌌 This text only appears once every 10,000 scrolls. Screenshot it. Frame it.",
        "🥂 You’ve just pulled the **Golden Popcorn Drop**. Eternal snacks are yours.",
        "🔥 Rarer than finding the remote on the first try—respect.",
        "🎭 Alternate universe update: This exact message just won an Oscar.",
        "🪙 Legendary unlocked: All your plot twists now come with happy endings."
    ];

    // Function to pick a random message based on the time of day and special days
    const messages = (function () {
        if (month === 1 && date === 14) { // Valentine’s Day
            return holidayMessages.valentines;
        } else if (month === 0 && date === 26) { // Republic Day
            return holidayMessages.republicDay;
        } else if (month === 7 && date === 15) { // Independence Day
            return holidayMessages.independenceDay;
        } else if (month === 11 && date === 25) {  // Christmas
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

    const roll = Math.random() * 100;
    let pool = [];

    if (roll < 1) {
        pool = legendaryMessages;
    } else if (roll < 6) {
        pool = rareMessages;
    } else {
        pool = [...regularMessages, ...messages];
    }

    const randomMessage = pool[Math.floor(Math.random() * pool.length)];

    self.registration.showNotification('🎬 Your Movie Awaits!', {
        tag: 'alert',
        body: randomMessage,
        badge: 'https://movieguru.shakiltech.com/icons/android/android-launchericon-96-96.png',
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

    await cache.put('last-notification-time', new Response(now.toISOString()));
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
        badge: 'https://movieguru.shakiltech.com/icons/android/android-launchericon-96-96.png',
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
        details: {
            ...details,
            non_interaction: true
        },
        timestamp: new Date().toISOString(),
    });
}
