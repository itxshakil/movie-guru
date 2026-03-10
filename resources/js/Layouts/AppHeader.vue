<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {Link} from '@inertiajs/vue3';
import {useWatchlist} from '@/Composables/useWatchlist.js';

const {watchlist} = useWatchlist();
</script>

<template>
    <header
        class="sticky top-0 z-50 w-full bg-white/80 dark:bg-gray-950/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 transition-all duration-300">
        <div class="h-[env(safe-area-inset-top)] w-full bg-inherit"></div>
        <nav aria-label="Global" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <div class="flex lg:flex-1 items-center gap-4">
                <Link class="-m-1.5 p-1.5 flex items-center gap-2 group" href="/">
                    <ApplicationLogo class="transition-transform duration-300 group-hover:scale-105"/>
                    <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white hidden sm:block">Movie Guru</span>
                </Link>
            </div>

            <div class="flex items-center gap-4 lg:flex-1 lg:justify-end">
                <Link
                    :href="route('watchlist')"
                    class="relative flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2"/>
                    </svg>
                    <span class="hidden sm:inline">Watchlist</span>
                    <span
                        v-if="watchlist.length > 0"
                        class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary-600 text-xs font-bold text-white"
                    >{{ watchlist.length }}</span>
                </Link>
            </div>
        </nav>
    </header>
</template>
