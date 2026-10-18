<script setup>
import {computed, ref, watch} from 'vue';
import {Head, Link, usePage} from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import PosterTile from '@/Components/PosterTile.vue';
import WatchedButton from '@/Components/WatchedButton.vue';
import WatchlistButton from '@/Components/WatchlistButton.vue';
import UiButton from '@/Components/Ui/UiButton.vue';
import ReactionPrompt from '@/Components/ReactionPrompt.vue';
import {useLibrary} from '@/Composables/useLibrary.js';

defineOptions({layout: BaseLayout});

const {toWatch, watched, isLibraryLoaded, loadError, retryLoad, lastMarkedWatched} = useLibrary();

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

// A title marked watched on the To watch tab leaves the grid (and its button) at once, so the page asks for the
// reaction instead. Anything marked before this page opened is ignored.
lastMarkedWatched.value = null;
const promptMovie = ref(null);
const tabLinks = ref([]);

watch(lastMarkedWatched, (movie) => {
    if (movie && !items.value.some((item) => item.imdb_id === movie.imdb_id)) {
        promptMovie.value = movie;
    }
});

const closeReactionPrompt = ({hadFocus}) => {
    promptMovie.value = null;
    lastMarkedWatched.value = null;
    if (hadFocus) {
        tabLinks.value.find((link) => link.$el.getAttribute('aria-current') === 'page')?.$el.focus();
    }
};
</script>

<template>
    <Head>
        <title>{{ activeTab === 'watched' ? 'Watched and To watch' : 'To watch and Watched' }} - My Watchlist - Movie Guru</title>
        <meta content="Your watchlist in two tabs: To watch for movies you want to see, and Watched for movies you've seen, liked or disliked. Available offline." head-key="description" name="description"/>
    </Head>

    <div class="min-h-screen py-12 px-4">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">My watchlist</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">{{ storageDescription }}</p>
            </div>

            <nav aria-label="Watchlist sections" class="mb-8 flex gap-1 border-b border-gray-200 dark:border-gray-800">
                <Link
                    v-for="tab in tabs"
                    :key="tab.key"
                    ref="tabLinks"
                    :aria-current="activeTab === tab.key ? 'page' : undefined"
                    :class="activeTab === tab.key
                        ? 'border-primary-600 text-gray-900 dark:text-white'
                        : 'border-transparent text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                    :href="tab.href"
                    class="-mb-px border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors"
                    preserve-scroll
                >
                    {{ tab.label }}
                    <span class="ml-1.5 rounded-full bg-gray-100 px-2 text-xs tabular-nums dark:bg-gray-800">{{ tab.count }}</span>
                </Link>
            </nav>

            <div v-if="!isLibraryLoaded && loadError" class="text-center py-24" role="alert">
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300">We couldn't load your watchlist</h2>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Check your connection and try again.</p>
                <UiButton class="mt-6" size="lg" @click="retryLoad">Try again</UiButton>
            </div>

            <div v-else-if="!isLibraryLoaded" aria-busy="true" aria-label="Loading your watchlist"
                 class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <div v-for="placeholder in 6" :key="placeholder"
                     class="animate-pulse rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-gray-100 dark:bg-gray-800">
                    <div class="aspect-2/3 bg-gray-200 dark:bg-gray-700"/>
                    <div class="p-3">
                        <div class="h-4 rounded bg-gray-200 dark:bg-gray-700 w-3/4"/>
                        <div class="mt-1.5 h-3 rounded bg-gray-200 dark:bg-gray-700 w-1/3"/>
                    </div>
                </div>
            </div>

            <div v-else-if="items.length === 0" class="text-center py-24">
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300">{{ emptyState.title }}</h2>
                <p class="mt-2 text-gray-500 dark:text-gray-400">{{ emptyState.body }}</p>
                <UiButton :href="route('home')" class="mt-6" size="lg">Browse movies</UiButton>
            </div>

            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <PosterTile v-for="movie in items" :key="movie.imdb_id" :movie="movie">
                    <WatchlistButton :movie="movie" variant="icon"/>
                    <WatchedButton :movie="movie" variant="icon"/>
                </PosterTile>
            </div>
        </div>

        <ReactionPrompt v-if="promptMovie" :key="promptMovie.imdb_id" :movie="promptMovie" @close="closeReactionPrompt"/>
    </div>
</template>
