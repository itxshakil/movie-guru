<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Movie Guru') }}</title>

    <!-- <meta name="description" content="{{ $metadescription ?? "Your go-to source for movie information, ratings, and reviews. Whether you're a film enthusiast or just looking for your next cinematic experience, Movie Guru has something for everyone." }}" /> -->
    <meta name="keywords" content="movie, film, ratings, reviews, movie database" />
    <!-- <meta name="subject" content="Explore Movies with Movie Guru" /> -->
    <meta name="language" content="en" />
    <meta name="rating" content="General" />
    <meta name="url" content="{{ config('app.url') }}" />
    <meta name="identifier-URL" content="{{ config('app.url') }}" />
    <meta name="owner" content="Movie Guru" />
    <meta name="author" content="Movie Guru Team, info@movieguru.com" />
    <!-- <meta name="og:title" content="Discover and Explore Movies with Movie Guru" /> -->
    <!-- <meta name="og:url" content="{{ config('app.url') }}" /> -->
    <meta name="og:site_name" content="Movie Guru" />
    <!-- <meta name="og:description" content="Your go-to source for movie information, ratings, and reviews. Whether you're a film enthusiast or just looking for your next cinematic experience, Movie Guru has something for everyone." /> -->
    <meta name="og:email" content="info@movieguru.com" />
    <meta name="og:country-name" content="India" />
    <!-- <meta name="og:image" content="{{ asset('/icons/icon-96x96.png')}}" /> -->

    <!-- <meta name="twitter:card" content="Your go-to source for movie information, ratings, and reviews. Whether you're a film enthusiast or just looking for your next cinematic experience, Movie Guru has something for everyone." />
    <meta property="twitter:domain" content="{{ config('app.url') }}" />
    <meta property="twitter:url" content="{{ config('app.url') }}" />
    <meta name="twitter:title" content="Open source alternative of IMDB" />
    <meta name="twitter:description" content="Your go-to source for movie information, ratings, and reviews. Whether you're a film enthusiast or just looking for your next cinematic experience, Movie Guru has something for everyone." />
    <meta name="twitter:image" content="{{ asset('/icons/icon-96x96.png')}}" /> -->

    <meta name="owner" content="Movie Guru" />
    <meta name="author" content="Movie Guru Team, info@movieguru.com" />
    <meta name="theme-color" content="#2A669F" />
    <meta name="msapplication-navbutton-color" content="#2A669F" />
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="apple-touch-icon" href="/icons/ios/192.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Movie Guru">
    <link rel="manifest" href="/app.webmanifest" />
    <script type="application/ld+json">
        {
           "@@context":"https://schema.org",
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
      "@@context": "https://schema.org",
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

    @production
        <script type="text/javascript">
            (function(c,l,a,r,i,t,y){
                c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
            })(window, document, "clarity", "script", "n8tsavdltm");
        </script>
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
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4132498105758259"
                crossorigin="anonymous"></script>
    @endproduction

</head>
<body class="font-sans antialiased accent-primary-500 dark:bg-gray-900 dark:text-white">
    @inertia
    <script type="text/javascript">
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                registerServiceWorker();
            });
        }

        // Modular function for registering the Service Worker
        async function registerServiceWorker() {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js');
                handleServiceWorkerState(registration);
            } catch (error) {
                console.error('Service Worker registration failed:', error);
            }
        }

        function handleServiceWorkerState(registration) {
            let serviceWorker = registration.installing || registration.waiting || registration.active;

            if (serviceWorker) {
                console.log('Service Worker state:', serviceWorker.state);
                serviceWorker.addEventListener('statechange', (e) => {
                    console.log('Service Worker state:', e.target.state);

                    if (e.target.state === 'activated') {
                        console.log('Service Worker activated');
                        setupSyncAndPeriodicSync(registration);
                    }
                });
            }

            // Listen for updates to the Service Worker
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                newWorker.addEventListener('statechange', () => {
                    console.log('Service Worker update state:', newWorker.state);
                });
            });
        }

        function setupSyncAndPeriodicSync(registration) {
            // Sync Manager
            if ('SyncManager' in window) {
                registration.sync.register('offline-search-sync').catch(error => {
                    console.error('Offline sync registration failed:', error);
                });
            }

            // Periodic Sync
            if ('periodicSync' in registration) {
                registerPeriodicSync(registration, 'weekly-trending-notification', 24 * 60 * 60 * 1000); // Once a day
                registerPeriodicSync(registration, 'daily-notification', ((25 * 60 * 60 * 1000) + (1 * 60 * 60 * 1000) + (7 * 60 * 100))); // Random
            }
        }

        async function registerPeriodicSync(registration, tag, minInterval) {
            try {
                await registration.periodicSync.register(tag, {minInterval});
                console.log(`Periodic Sync registered for ${tag}`);
            } catch (error) {
                console.error(`Periodic Sync registration failed for ${tag}:`, error);
            }
        }

        // PWA Install Prompt Management
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });

        document.addEventListener('DOMContentLoaded', () => {
            document.body.addEventListener('click', (e) => {
                if (e.target.classList.contains('install-prompt')) {
                    if (!isAppInstalled()) {
                        e.preventDefault();
                        showInstallPrompt();
                    }
                }
            });
        });

        function showInstallPrompt() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
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

        function isAppInstalled() {
            return (
                ('standalone' in navigator && navigator.standalone) ||
                window.matchMedia('(display-mode: standalone)').matches
            );
        }

        // Broadcast Channel for Offline Sync
        const broadcast = new BroadcastChannel('service-worker-channel');
        broadcast.addEventListener('message', (event) => {
            if (event.data?.type === 'OFFLINE_SYNC_EVENT') {
                const offlineRequestUrl = localStorage.getItem('offlineRequestUrl');
                if (offlineRequestUrl) {
                    broadcast.postMessage({type: 'OFFLINE_SYNC_REQUEST', url: offlineRequestUrl});
                }
            } else if (event.data?.type === 'OFFLINE_SYNC_FETCHED') {
                localStorage.removeItem('offlineRequestUrl');
            } else if (event.data?.type === 'EVENT_TRACKING') {
                if (window.gtag) {
                    const title = event.data.title;
                    const details = event.data.details ?? {};
                    window.gtag('event', title, details);
                }
            }
        });

        window.addEventListener('online', () => {
            const offlineRequestUrl = localStorage.getItem('offlineRequestUrl');
            if (offlineRequestUrl && navigator.onLine) {
                broadcast.postMessage({type: 'OFFLINE_SYNC_REQUEST', url: offlineRequestUrl});
            }
        });

        // Handle stored offline requests
        const offlineRequestUrl = localStorage.getItem('offlineRequestUrl');
        if (offlineRequestUrl && navigator.onLine) {
            broadcast.postMessage({type: 'OFFLINE_SYNC_REQUEST', url: offlineRequestUrl});
        }
    </script>

</body>
</html>
