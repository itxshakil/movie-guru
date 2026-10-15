<script setup>
import {computed} from 'vue';
import {Head, Link, usePage} from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import WatchedButton from '@/Components/WatchedButton.vue';
import {useLibrary} from '@/Composables/useLibrary.js';

defineOptions({layout: BaseLayout});

const {toWatch, watched, isLibraryLoaded, remove} = useLibrary();

const page = usePage();

const activeTab = computed(() => {
    const tab = new URL(page.url, window.location.origin).searchParams.get('tab');
    return tab === 'watched' ? 'watched' : 'to-watch';
});

const tabs = computed(() => [
    {key: 'to-watch', label: 'To watch', count: toWatch.value.length, href: route('watchlist')},
    {key: 'watched', label: 'Watched', count: watched.value.length, href: route('watchlist', {tab: 'watched'})},
]);

const items = computed(() => (activeTab.value === 'watched' ? watched.value : toWatch.value));

const storageDescription = computed(() => page.props.auth?.user
    ? 'Synced to your account and available offline.'
    : 'Saved on this device and available offline. Sign in to keep them in your account.');

const emptyState = computed(() => (activeTab.value === 'watched'
    ? {title: 'Nothing watched yet', body: 'Mark titles as watched from any movie card.'}
    : {title: 'Your watchlist is empty', body: 'Browse movies and click the Watchlist button to save them here.'}));

const moviePoster = (movie) => (movie.poster && movie.poster !== 'N/A' ? movie.poster : '/assets/images/no-poster.jpg');
</script>

<template>
    <Head>
        <title>{{ activeTab === 'watched' ? 'Watched' : 'My Watchlist' }} - Movie Guru</title>
        <meta content="Movies you want to watch and movies you've watched, available offline." head-key="description" name="description"/>
    </Head>

    <div class="min-h-screen bg-white dark:bg-gray-900 py-12 px-4">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">🎬 My Watchlist</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">{{ storageDescription }}</p>
            </div>

            <nav aria-label="Watchlist sections" class="mb-8 flex gap-1 border-b border-gray-200 dark:border-gray-800">
                <Link
                    v-for="tab in tabs"
                    :key="tab.key"
                    :aria-current="activeTab === tab.key ? 'page' : undefined"
                    :class="activeTab === tab.key
                        ? 'border-primary-600 text-primary-700 dark:border-primary-400 dark:text-primary-300'
                        : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                    :href="tab.href"
                    class="-mb-px border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors"
                    preserve-scroll
                >
                    {{ tab.label }}
                    <span class="ml-1 tabular-nums text-xs font-medium opacity-70">({{ tab.count }})</span>
                </Link>
            </nav>

            <div v-if="!isLibraryLoaded" aria-busy="true" aria-label="Loading your watchlist"
                 class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <div v-for="placeholder in 6" :key="placeholder"
                     class="animate-pulse rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800">
                    <div class="aspect-2/3 bg-gray-200 dark:bg-gray-700"/>
                    <div class="p-2 space-y-2">
                        <div class="h-3 rounded bg-gray-200 dark:bg-gray-700 w-3/4"/>
                        <div class="h-2.5 rounded bg-gray-200 dark:bg-gray-700 w-1/3"/>
                    </div>
                </div>
            </div>

            <div v-else-if="items.length === 0" class="text-center py-24">
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300">{{ emptyState.title }}</h2>
                <p class="mt-2 text-gray-500 dark:text-gray-400">{{ emptyState.body }}</p>
                <Link :href="route('home')"
                      class="mt-6 inline-block px-6 py-3 bg-primary-600 hover:bg-primary-500 text-white font-semibold rounded-xl transition-colors">
                    Browse Movies
                </Link>
            </div>

            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <div
                    v-for="movie in items"
                    :key="movie.imdb_id"
                    class="group relative rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 shadow hover:shadow-lg transition-all duration-300 hover:-translate-y-1 cv-card-compact"
                >
                    <Link :href="route('movie.detail.full', {imdbID: movie.imdb_id})" prefetch="click">
                        <div class="aspect-2/3 overflow-hidden">
                            <img :alt="movie.title + ' Poster'" :src="moviePoster(movie)"
                                 class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                                 loading="lazy"/>
                        </div>
                        <div class="p-2">
                            <p class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2 leading-tight">{{ movie.title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ movie.year }}</p>
                        </div>
                    </Link>

                    <div class="absolute top-2 left-2">
                        <WatchedButton :movie="movie"/>
                    </div>

                    <button
                        v-if="activeTab === 'to-watch'"
                        :aria-label="`Remove ${movie.title} from Watchlist`"
                        class="absolute top-2 right-2 p-1.5 bg-red-500/80 hover:bg-red-600 text-white rounded-full transition-all active:scale-90 focus-visible:opacity-100 [@media(hover:hover)]:opacity-0 [@media(hover:hover)]:group-hover:opacity-100"
                        title="Remove from Watchlist"
                        @click.prevent="remove(movie.imdb_id)"
                    >
                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
