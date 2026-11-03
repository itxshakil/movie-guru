import './bootstrap';
import '../css/app.css';

import {createApp, h} from 'vue';
import {createInertiaApp, router} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy';
import useInstallPrompt from '@/Composables/useInstalPrompt.js';
import Analytics from '@/Plugins/analytics.js';
import {requestPersistentStorage} from '@/Composables/useLibrary.js';
import ToastNotifications from '@/Components/ToastNotifications.vue';
import {reloadWhenUpdated, routeLaunches} from '@/Composables/appLifecycle.js';
import {showToast} from '@/Composables/toast.js';

const appName = import.meta.env.VITE_APP_NAME || 'Movie Guru';

const broadcast = new BroadcastChannel('service-worker-channel');
broadcast.onmessage = (event) => {
    if (event.data && event.data.type === 'EVENT_TRACKING') {
        const {title, details} = event.data;
        if (typeof window !== 'undefined' && window.gtag) {
            window.gtag('event', title, details);
        }
    }
};

// Installing the app is a strong signal the user wants cached titles and their watchlist kept.
window.addEventListener('appinstalled', requestPersistentStorage);

// The update toast has to survive the reload it causes, so it is shown on the next boot.
const UPDATED_FLAG = 'movie-guru:updated';
reloadWhenUpdated(navigator.serviceWorker, () => {
    sessionStorage.setItem(UPDATED_FLAG, '1');
    window.location.reload();
});
// Called once the app has mounted: before that, ToastNotifications is not listening yet.
const announcePendingUpdate = () => {
    if (sessionStorage.getItem(UPDATED_FLAG)) {
        sessionStorage.removeItem(UPDATED_FLAG);
        showToast('Movie Guru has been updated to the latest version.', 'success');
    }
};
routeLaunches(window.launchQueue, (url) => router.visit(url), () => window.location.href);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    // Wraps every Inertia page swap in the View Transitions API (native
    // support and fallback are handled by Inertia itself - it no-ops when
    // document.startViewTransition is unavailable).
    defaults: {
        visitOptions: (href, options) => ({...options, viewTransition: true}),
    },
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // Toasts live beside the page rather than in a layout, so flashes reach every layout and the
        // component (with its router listener) is mounted exactly once.
        const app = createApp({ render: () => [h(App, props), h(ToastNotifications)] })
            .use(plugin)
            .use(ZiggyVue)
            .use(Analytics)
            .mixin(useInstallPrompt)
            .mixin({
                methods: {
                    route,
                    handleViewTransition(callback) {
                        if (!document.startViewTransition) {
                            callback();
                            return;
                        }
                        document.startViewTransition(callback);
                    }
                }
            })
            .mount(el);
        announcePendingUpdate();
        return app;
    },
    progress: {
        color: '#1d34ff',
        showSpinner: true,
    },
});
