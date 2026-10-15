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
    <meta name="theme-color" content="#030712" />
    <meta name="msapplication-navbutton-color" content="#030712" />
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
    <noscript>
        <div style="padding: 2rem; text-align: center; font-family: sans-serif;">
            Movie Guru requires JavaScript to run. Please enable it in your browser to continue.
        </div>
    </noscript>
    <script type="text/javascript">
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', registerServiceWorker);
            window.addEventListener('online', processOfflineSearchQueue);
        }

        async function registerServiceWorker() {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js');
                await navigator.serviceWorker.ready;
                registerPeriodicSyncs(registration);
                processOfflineSearchQueue();
                markTrendingSeen();
            } catch (error) {
                console.error('Service Worker registration failed:', error);
            }
        }

        function registerPeriodicSyncs(registration) {
            if (!('periodicSync' in registration)) {
                return;
            }

            registerPeriodicSync(registration, 'weekly-trending-notification', 24 * 60 * 60 * 1000);
            registerPeriodicSync(registration, 'daily-notification', (25 * 60 * 60 * 1000) + (1 * 60 * 60 * 1000) + (7 * 60 * 100));
            registerPeriodicSync(registration, 'content-refresh', 12 * 60 * 60 * 1000);
        }

        async function registerPeriodicSync(registration, tag, minInterval) {
            try {
                await registration.periodicSync.register(tag, {minInterval});
            } catch (error) {
                console.error(`Periodic Sync registration failed for ${tag}:`, error);
            }
        }

        /**
         * Visiting the app clears the "new trending titles" badge set by the content-refresh sync.
         */
        function markTrendingSeen() {
            navigator.serviceWorker.controller?.postMessage({type: 'MARK_TRENDING_SEEN'});
        }

        /**
         * Fallback for browsers without Background Sync: ask the worker to drain the
         * offline search queue whenever the page is (back) online.
         */
        function processOfflineSearchQueue() {
            if (navigator.onLine && navigator.serviceWorker.controller) {
                navigator.serviceWorker.controller.postMessage({type: 'PROCESS_SEARCH_QUEUE'});
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
                deferredPrompt.userChoice.then(() => {
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
    </script>

</body>
</html>
