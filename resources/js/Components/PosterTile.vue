<script setup>
import {Link} from '@inertiajs/vue3';

/**
 * A poster, title and year linking to the detail page. The default slot is overlaid on the poster's top-right
 * corner for icon actions.
 */
defineProps({
    movie: {
        type: Object,
        required: true,
    },
});

const moviePoster = (movie) => (movie.poster && movie.poster !== 'N/A' ? movie.poster : '/assets/images/no-poster.jpg');
</script>

<template>
    <div
        class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white transition-colors hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700 cv-card-compact"
    >
        <Link :href="route('movie.detail.full', {imdbID: movie.imdb_id})" prefetch="click">
            <div class="aspect-2/3 overflow-hidden bg-gray-100 dark:bg-gray-800">
                <img :alt="movie.title + ' poster'" :src="moviePoster(movie)"
                     class="h-full w-full object-cover object-center transition-transform duration-300 group-hover:scale-[1.02]"
                     loading="lazy"/>
            </div>
            <div class="p-3">
                <p class="line-clamp-2 text-sm font-semibold leading-snug text-gray-900 dark:text-white">{{ movie.title }}</p>
                <p v-if="movie.year" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ movie.year }}</p>
            </div>
        </Link>
        <div v-if="$slots.default" class="absolute top-2 right-2 flex gap-2">
            <slot/>
        </div>
    </div>
</template>
