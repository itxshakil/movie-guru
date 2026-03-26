<script setup>
import {Head, Link} from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';

defineOptions({layout: BaseLayout});

defineProps({
    moods: {
        type: Array,
        default: () => [],
    },
    selectedMood: {
        type: Object,
        default: null,
    },
    movies: {
        type: Array,
        default: () => [],
    },
});

const moviePoster = (movie) => {
    return movie.poster && movie.poster !== 'N/A' ? movie.poster : '/assets/images/no-poster.jpg';
};
</script>

<template>
    <Head>
        <title>Mood Discovery — Movie Guru</title>
        <meta content="Find movies that match your mood." head-key="description" name="description"/>
    </Head>

    <div class="min-h-screen bg-white dark:bg-gray-900 py-12 px-4">
        <div class="mx-auto max-w-7xl">

            <!-- Header -->
            <div class="mb-10 text-center">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight"
                    style="font-feature-settings: 'kern' 1, 'liga' 1;">What's your mood?</h1>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Pick a vibe and we'll find the perfect film for
                    you.</p>
            </div>

            <!-- Mood picker -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 mb-12">
                <Link
                    v-for="mood in moods"
                    :key="mood.slug"
                    :class="selectedMood?.slug === mood.slug
                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300'
                        : 'border-gray-100 dark:border-gray-800 hover:border-gray-200 dark:hover:border-gray-700 text-gray-700 dark:text-gray-300'"
                    :href="route('mood.show', mood.slug)"
                    class="flex flex-col items-center gap-2 p-4 rounded-2xl border bg-white dark:bg-gray-900 transition-colors text-center"
                >
                    <span class="text-3xl">{{ mood.emoji }}</span>
                    <span class="text-sm font-bold">{{ mood.label }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 leading-snug">{{ mood.description }}</span>
                </Link>
            </div>

            <!-- Results -->
            <div v-if="selectedMood && movies.length" class="space-y-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ selectedMood.emoji }} {{ selectedMood.label }} picks
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <Link
                        v-for="movie in movies"
                        :key="movie.imdb_id"
                        :href="route('movie.detail.full', { imdbID: movie.imdb_id })"
                        class="group rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 shadow"
                    >
                        <div class="aspect-[2/3] overflow-hidden">
                            <img
                                :alt="movie.title"
                                :src="moviePoster(movie)"
                                class="h-full w-full object-cover object-center"
                                loading="lazy"
                            />
                        </div>
                        <div class="p-2">
                            <p class="text-xs font-bold text-gray-900 dark:text-white line-clamp-2 leading-tight">
                                {{ movie.title }}</p>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">{{ movie.year }}</p>
                        </div>
                    </Link>
                </div>
            </div>

            <div v-else-if="selectedMood && movies.length === 0"
                 class="text-center py-16 text-gray-500 dark:text-gray-400">
                No movies found for this mood yet. Try another!
            </div>
        </div>
    </div>
</template>
