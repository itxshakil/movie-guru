<script setup>
import {Head, Link, router} from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';

defineOptions({layout: BaseLayout});

const props = defineProps({
    watchlist: {
        type: Array,
        default: () => [],
    },
});

const moviePoster = (movie) => {
    return movie.poster && movie.poster !== 'N/A' ? movie.poster : '/assets/images/no-poster.jpg';
};

const removeMovie = (imdbId) => {
    router.delete(route('watchlist.destroy', {imdbId}), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head>
        <title>My Watchlist — Movie Guru</title>
        <meta content="Your personal movie watchlist." head-key="description" name="description"/>
    </Head>

    <div class="min-h-screen bg-white dark:bg-gray-900 py-12 px-4">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white"
                    style="font-feature-settings: 'kern' 1, 'liga' 1;">My Watchlist</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">Movies you've saved to watch later.</p>
            </div>

            <div v-if="watchlist.length === 0" class="text-center py-24">
                <svg class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path
                        d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/>
                </svg>
                <h2 class="mt-4 text-xl font-semibold text-gray-700 dark:text-gray-300">Your watchlist is empty</h2>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Browse movies and save them to your watchlist.</p>
                <Link
                    :href="route('home')"
                    class="mt-6 inline-block px-6 py-3 bg-primary-600 hover:bg-primary-500 text-white font-semibold rounded-xl transition-colors"
                >
                    Browse Movies
                </Link>
            </div>

            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <div
                    v-for="movie in watchlist"
                    :key="movie.imdb_id"
                    class="group relative rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 shadow"
                >
                    <Link :href="route('movie.detail.full', { imdbID: movie.imdb_id })">
                        <div class="aspect-[2/3] overflow-hidden">
                            <img
                                :alt="movie.title + ' Poster'"
                                :src="moviePoster(movie)"
                                class="h-full w-full object-cover object-center"
                                loading="lazy"
                            />
                        </div>
                        <div class="p-2">
                            <p class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2 leading-tight">
                                {{ movie.title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ movie.year }}</p>
                        </div>
                    </Link>
                    <button
                        class="absolute top-2 right-2 p-1.5 bg-red-500/80 hover:bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-all"
                        title="Remove from Watchlist"
                        @click.prevent="removeMovie(movie.imdb_id)"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2.5"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
