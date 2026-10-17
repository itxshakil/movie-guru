<script setup>
import {Link, usePage} from '@inertiajs/vue3';
import {useWatchlist} from '@/Composables/useWatchlist.js';
import {NAV_ITEMS} from '@/Layouts/navItems.js';

const page = usePage();
const {watchlist} = useWatchlist();
</script>

<template>
    <nav
        aria-label="Primary"
        class="app-tab-bar fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white/90 pb-[env(safe-area-inset-bottom)] backdrop-blur-md md:hidden dark:border-gray-800 dark:bg-gray-950/90"
    >
        <ul class="mx-auto grid h-16 max-w-md grid-cols-4">
            <li v-for="item in NAV_ITEMS" :key="item.route">
                <Link
                    :aria-current="item.match(page.url) ? 'page' : undefined"
                    :class="item.match(page.url) ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'"
                    :href="route(item.route)"
                    class="relative flex h-full flex-col items-center justify-center gap-1 text-[11px] font-medium"
                >
                    <component :is="item.icon" :filled="item.showsCount ? item.match(page.url) : undefined" class="h-6 w-6"/>
                    {{ item.short }}
                    <span
                        v-if="item.showsCount && watchlist.length > 0"
                        class="absolute top-2 left-1/2 ml-2 rounded-full bg-primary-600 px-1.5 text-[11px] font-semibold leading-4 text-white"
                    >{{ watchlist.length }}</span>
                </Link>
            </li>
        </ul>
    </nav>
</template>
