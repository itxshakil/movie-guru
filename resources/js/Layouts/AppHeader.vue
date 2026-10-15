<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {Link, router} from '@inertiajs/vue3';
import {ref} from 'vue';
import {useWatchlist} from '@/Composables/useWatchlist.js';

const {watchlist} = useWatchlist();

const titleBarSearch = ref('');

const submitTitleBarSearch = () => {
    const query = titleBarSearch.value.trim();
    if (query === '') {
        return;
    }

    router.get(route('search'), {s: query});
};
</script>

<template>
    <header
        class="app-header sticky top-0 z-50 w-full bg-white/80 dark:bg-gray-950/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 transition-all duration-300">
        <div class="app-header__safe-area h-[env(safe-area-inset-top)] w-full bg-inherit"></div>
        <nav aria-label="Global" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4 h-16">
            <div class="flex lg:flex-1 items-center gap-4">
                <Link class="app-header__control -m-1.5 p-1.5 flex items-center gap-2 group" href="/">
                    <ApplicationLogo class="app-header__logo transition-transform duration-300 group-hover:scale-105"/>
                    <span class="app-header__name text-xl font-bold tracking-tight text-gray-900 dark:text-white hidden sm:block">Movie Guru</span>
                </Link>
            </div>

            <!-- Only shown when the installed desktop app draws its own title bar (Window Controls Overlay). -->
            <form
                :action="route('search')"
                class="app-header__search app-header__control hidden flex-1 max-w-md"
                method="get"
                role="search"
                @submit.prevent="submitTitleBarSearch"
            >
                <label class="sr-only" for="title-bar-search">Search movies</label>
                <input
                    id="title-bar-search"
                    v-model="titleBarSearch"
                    autocomplete="off"
                    class="w-full rounded-md border-0 bg-gray-100 dark:bg-gray-800 px-3 py-1 text-sm text-gray-900 dark:text-white placeholder:text-gray-500 focus:ring-2 focus:ring-primary-500"
                    enterkeyhint="search"
                    name="s"
                    placeholder="Search movies..."
                    type="search"
                />
            </form>

            <div class="flex items-center gap-4 lg:flex-1 lg:justify-end">
                <Link
                    :href="route('watchlist')"
                    class="app-header__control app-header__watchlist relative flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2"/>
                    </svg>
                    <span class="hidden sm:inline">Watchlist</span>
                    <span
                        v-if="watchlist.length > 0"
                        class="app-header__count absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary-600 text-xs font-bold text-white"
                    >{{ watchlist.length }}</span>
                </Link>
            </div>
        </nav>
    </header>
</template>

<style scoped>
nav {
    transition: height 0.3s ease;
}

/* Compact header once the page has scrolled away from the top, reclaiming
   vertical space, using CSS scroll-state queries instead of a scroll listener. */
@supports (container-type: scroll-state) {
    @container scroll-state(scrollable: top) {
        nav {
            height: 3.25rem;
        }
    }
}

/* Installed desktop app with Window Controls Overlay: the header becomes the
   title bar. It is confined to the titlebar-area-* rectangle so it never sits
   under the OS window controls, it is draggable, and its controls are not. */
@media (display-mode: window-controls-overlay) {
    .app-header {
        padding-left: env(titlebar-area-x, 0px);
        padding-right: calc(100% - env(titlebar-area-x, 0px) - env(titlebar-area-width, 100%));
        app-region: drag;
        -webkit-app-region: drag;
    }

    .app-header__safe-area {
        display: none;
    }

    nav {
        height: env(titlebar-area-height, 2.25rem);
        max-width: none;
        padding-inline: 0.75rem;
    }

    .app-header__control {
        app-region: no-drag;
        -webkit-app-region: no-drag;
    }

    .app-header__logo {
        height: 1.5rem;
    }

    .app-header__name {
        font-size: 0.875rem;
    }

    .app-header__search {
        display: block;
    }

    .app-header__watchlist {
        padding-block: 0.125rem;
    }

    .app-header__count {
        top: -0.125rem;
        height: 1rem;
        width: 1rem;
        font-size: 0.625rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    nav {
        transition: none;
    }
}
</style>
