<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="{{ $metadescription ?? "Discover and explore a world of movies with Movie Guru! Your go-to source for movie information, ratings, and reviews. Whether you're a film enthusiast or just looking for your next cinematic experience, Movie Guru has something for everyone." }}" />
    <meta name="keywords" content="movie, film, ratings, reviews, movie database" />
    <meta name="subject" content="Explore Movies with Movie Guru" />
    <meta name="language" content="en" />
    <meta name="rating" content="General" />
    <meta name="url" content="{{ config('app.url') }}" />
    <meta name="identifier-URL" content="{{ config('app.url') }}" />
    <meta name="owner" content="Movie Guru" />
    <meta name="author" content="Movie Guru Team, info@movieguru.com" />
    <meta name="og:title" content="Discover and Explore Movies with Movie Guru" />
    <meta name="og:url" content="{{ config('app.url') }}" />
    <meta name="og:site_name" content="Movie Guru" />
    <meta name="og:description" content="Your go-to source for movie information, ratings, and reviews. Explore a world of movies with Movie Guru. It's free and easy to use!" />
    <meta name="og:email" content="info@movieguru.com" />
    <meta name="og:country-name" content="India" />
    <meta name="og:image" content="/icons/icon-96x96.png" />
    <meta name="owner" content="Movie Guru" />
    <meta name="author" content="Movie Guru Team, info@movieguru.com" />
    <meta name="theme-color" content="#2A669F" />
    <meta name="msapplication-navbutton-color" content="#2A669F" />
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="apple-touch-icon" href="/icons/ios/192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Movie Guru">
    <link rel="manifest" href="/app.webmanifest" />
    <script type="application/ld+json">
        {
           "@context":"https://schema.org",
           "@type":"Organization",
           "name":"Movie Guru",
           "description":"Discover and explore a world of movies with Movie Guru! Your go-to source for movie information, ratings, and reviews. Whether you're a film enthusiast or just looking for your next cinematic experience, Movie Guru has something for everyone.",
           "url":"https://movieguru.shakiltech.com",
           "sameAs":[
              "https://www.facebook.com/moviegurufb",
              "https://whatsapp.com/channel/0029VaLNhA9HFxOyPamoRO17",
              "https://www.instagram.com/movieguru/"
           ],
           "logo":"https://movieguru.shakiltech.com/icons/ios/64.png",
           "foundingDate":"2019-08-15",
           "founders":[
              {
                 "@type":"Person",
                 "name" :"Shakil Alam",
                 "jobTitle":"Founder"
              }
           ],
           "contactPoint":{
              "@type":"ContactPoint",
              "email":"hello@movie-guru.shakiltech.com",
              "contactType":"customer service"
           }
        }
    </script>

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "url": "https://movieguru.shakiltech.com",
      "potentialAction": {
        "@type": "SearchAction",
        "target": {
          "@type": "EntryPoint",
          "urlTemplate": "https://movieguru.shakiltech.com/search?s={search_term_string}"
        },
        "query-input": "required name=search_term_string"
      }
    }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead

    @if(app()->isProduction())
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-ESD6M429K6"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-ESD6M429K6');

    </script>
    @endif

</head>
<body class="font-sans antialiased accent-primary-500">
    @inertia
    <script type="text/javascript">
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        let serviceWorker;
                        if (registration.installing) {
                            console.log('Service Worker installing');
                            serviceWorker = registration.installing;
                        } else if (registration.waiting) {
                            console.log('Service Worker installed');
                            serviceWorker = registration.waiting;
                        } else if (registration.active) {
                            console.log('Service Worker active');
                            serviceWorker = registration.active;
                        }

                        if (serviceWorker) {
                            console.log('Service Worker state:', serviceWorker.state);
                            serviceWorker.addEventListener('statechange', (e) => {
                                console.log('Service Worker state:', e.target.state);

                                if (e.target.state === 'activated') {
                                    console.log('Service Worker activated');

                                    if ("sync" in registration) {
                                        registration.sync.register('offlineSync');
                                    }

                                    if ("periodicSync" in registration) {
                                        registration.periodicSync.register('notificationSync', {
                                            minInterval: 5 * 60 * 60 * 1000, // 5 hours
                                        });
                                    }

                                    // check if there is sync manager
                                    if ('SyncManager' in window) {
                                        navigator.serviceWorker.ready
                                            .then(registration => {
                                                return registration.sync.register('offlineSync');
                                            })
                                            .catch(error => {
                                                console.log('Sync registration failed:', error);
                                            });
                                    }
                                }
                            });

                            serviceWorker.addEventListener('updatefound', () => {
                                console.log('Service Worker update found');
                                serviceWorker = registration.installing;
                                serviceWorker.addEventListener('statechange', (e) => {
                                    console.log('Service Worker state:', e.target.state);
                                });
                            });

                            // Listen for the "controllerchange" event, which indicates that the service worker has become the active worker
                            navigator.serviceWorker.addEventListener('controllerchange', () => {
                                // console.log('Service Worker controllerchange');
                            });

                            // Listen for the "message" event, which indicates that the service worker has sent a message to the client
                            navigator.serviceWorker.addEventListener('message', (event) => {
                                // console.log('Received a message from the service worker:', event.data);
                            });

                            // Listen for the "error" event, which indicates that the service worker has encountered an error
                            navigator.serviceWorker.addEventListener('error', (event) => {
                                // console.log('Service Worker error:', event.error);
                            });

                            // Listen for the "offline" event, which indicates that the user has lost their internet connection
                            window.addEventListener('offline', () => {
                                // console.log('You are offline');
                            });

                            // Listen for the "online" event, which indicates that the user has regained their internet connection
                            window.addEventListener('online', () => {
                                // console.log('You are online');
                            });

                            // Listen for the "beforeinstallprompt" event, which indicates that the user can be prompted to install the PWA
                            window.addEventListener('beforeinstallprompt', (e) => {
                                // console.log('beforeinstallprompt event:', e);
                            });

                            // Listen for the "appinstalled" event, which indicates that the PWA has been installed
                            window.addEventListener('appinstalled', (e) => {
                                // console.log('appinstalled event:', e);
                            });

                            // Listen for the "fetch" event, which indicates that the browser has made a network request
                            window.addEventListener('fetch', (e) => {
                                // console.log('fetch event:', e);
                            });

                            // Listen for the "message" event, which indicates that the browser has received a message from the service worker
                            window.addEventListener('message', (e) => {
                                // console.log('message event:', e);
                            });
                        }
                    })
                    .catch(error => {
                        console.log('Service Worker registration failed:', error);
                    });
            });
        }

        // Save the installation prompt
        let deferredPrompt;

        // Listen for the beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            // Stash the event so it can be triggered later.
            deferredPrompt = e;
        });

        // Function to show the install prompt
        function showInstallPrompt() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                // Wait for the user to respond to the prompt
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    deferredPrompt = null;
                });
            }
        }

        // Function to check if the app is already installed
        function isAppInstalled() {
            // Check the navigator.standalone property for iOS devices
            if ('standalone' in navigator && navigator.standalone) {
                return true;
            }
            // Check the display-mode media query for Android devices
            if (window.matchMedia('(display-mode: standalone)').matches) {
                return true;
            }
            return false;
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.body.addEventListener('click', (e) => {
                if (e.target.classList.contains('install-prompt')) {
                    if (!isAppInstalled()) {
                        e.preventDefault();
                        showInstallPrompt();
                    }
                }
            });
        })

        const broadcast = new BroadcastChannel('service-worker-channel');
        broadcast.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'OFFLINE_SYNC_EVENT') {
                const offlineRequestUrl = localStorage.getItem('offlineRequestUrl');

                if (offlineRequestUrl) {
                    broadcast.postMessage({
                        type: 'OFFLINE_SYNC_REQUEST'
                        , url: offlineRequestUrl
                    });
                }
            }else if( event.data && event.data.type === 'OFFLINE_SYNC_FETCHED' ){
                localStorage.removeItem('offlineRequestUrl');
            }
        });
    </script>
</body>
</html>
