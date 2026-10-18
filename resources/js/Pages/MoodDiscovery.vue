<script setup>
import {Head, Link} from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import MoodPrompt from '@/Components/MoodPrompt.vue';
import PosterTile from '@/Components/PosterTile.vue';
import SectionHeading from '@/Components/Ui/SectionHeading.vue';

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
</script>

<template>
    <Head>
        <title>Mood Discovery - Movie Guru</title>
        <meta content="Find movies that match your mood." head-key="description" name="description"/>
    </Head>

    <div class="min-h-screen bg-white dark:bg-gray-900 py-12 px-4">
        <div class="mx-auto max-w-7xl">

            <!-- Header -->
            <div class="mb-10 text-center">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">What's your mood?</h1>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Pick a vibe and we'll find the perfect film for
                    you.</p>
            </div>

            <!-- Mood picker -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 mb-12">
                <Link
                    v-for="mood in moods"
                    :key="mood.slug"
                    :class="selectedMood?.slug === mood.slug
                        ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300'
                        : 'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700 text-gray-700 dark:text-gray-300'"
                    :href="route('mood.show', mood.slug)"
                    prefetch
                    class="flex flex-col items-center gap-2 rounded-2xl border bg-white p-4 text-center transition-colors dark:bg-gray-900"
                >
                    <span class="text-3xl">{{ mood.emoji }}</span>
                    <span class="text-sm font-bold">{{ mood.label }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 leading-snug">{{ mood.description }}</span>
                </Link>
            </div>

            <MoodPrompt :moods="moods"/>

            <!-- Results -->
            <div v-if="selectedMood && movies.length">
                <SectionHeading :title="`${selectedMood.label} picks`"/>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <PosterTile v-for="movie in movies" :key="movie.imdb_id" :movie="movie"/>
                </div>
            </div>

            <div v-else-if="selectedMood && movies.length === 0"
                 class="text-center py-16 text-gray-500 dark:text-gray-400">
                No movies found for this mood yet. Try another!
            </div>
        </div>
    </div>
</template>
