import './bootstrap';
import '../css/app.css';

import {createApp, h} from 'vue';
import {createInertiaApp} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy';
import useInstallPrompt from '@/Composables/useInstalPrompt.js';
import Analytics from '@/Plugins/analytics.js';

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

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
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
    },
    progress: {
        color: '#1d34ff',
        showSpinner: true,
    },
});
