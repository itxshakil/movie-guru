<template>
    <div
        class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800/60 rounded-2xl shadow-sm group relative flex flex-col justify-between
               hover:border-gray-200 dark:hover:border-gray-700
               transition-colors duration-200 cursor-pointer overflow-hidden"
        itemprop="mainEntity" itemscope itemtype="https://schema.org/Movie"
        @click="openDetail(movie.imdb_id, movie.title)"
    >
        <div>
            <!-- Poster -->
            <div class="relative w-full overflow-hidden rounded-t-2xl bg-gray-100 dark:bg-gray-800 _aspect-2/3">
                <img
                    :alt="movie.title + ' Poster'"
                    :src="moviePoster(movie)"
                    class="h-full w-full object-cover object-center block"
                    itemprop="image"
                    loading="lazy"
                    @error="handlePosterError"
                >
            </div>

            <!-- Meta row: type + date -->
            <div class="mt-3 px-3 flex items-center gap-1.5">
                <span
                    class="text-[9px] font-black tracking-widest uppercase px-2 py-0.5 rounded-full bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-800/50">
                    {{ movie.type }}
                </span>
                <span
                    v-if="(movie.release_date && movie.release_date !== 'N/A') || (movie.year && movie.year !== 'N/A')"
                    class="text-[10px] text-gray-400 dark:text-gray-500 font-medium" itemprop="datePublished">
                    {{ movie.release_date !== 'N/A' ? movie.release_date : movie.year }}
                </span>
            </div>

            <!-- Title -->
            <div class="px-3 pt-1.5 pb-1">
                <p
                    class="text-[15px] font-bold text-gray-900 dark:text-white line-clamp-2 leading-snug"
                    itemprop="name"
                    style="font-feature-settings: 'kern' 1, 'liga' 1;"
                    v-text="movie.title"
                />
            </div>

            <!-- Rating -->
            <div v-if="Number(movie.imdb_rating)" class="flex items-center gap-2 px-3 mb-2">
                <div class="flex items-center gap-0.5">
                    <svg
                        v-for="i in 5"
                        :key="i"
                        :class="i <= basicRating ? 'text-yellow-400' : 'text-gray-200 dark:text-gray-700'"
                        aria-hidden="true"
                        class="h-3 w-3 shrink-0 transition-colors"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path clip-rule="evenodd"
                              d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                              fill-rule="evenodd"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 tabular-nums">{{
                        (movie.imdb_rating / 2).toFixed(1)
                    }}</span>
                <span v-if="movie.imdb_votes" class="text-[10px] text-gray-400 dark:text-gray-500 font-medium ml-auto">
                    {{ formatVotes(movie.imdb_votes) }}
                </span>
            </div>

            <!-- Director / Cast -->
            <div class="px-3 pb-3 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                <p v-if="isValue(movie.director)" class="flex items-center gap-1.5 line-clamp-1">
                    <span
                        class="shrink-0 text-[8px] font-black tracking-widest uppercase text-gray-400 dark:text-gray-600 w-6">Dir</span>
                    <span class="truncate font-semibold text-gray-700 dark:text-gray-300" itemprop="director" itemscope
                          itemtype="https://schema.org/Person">
                        <span itemprop="name">{{ movie.director }}</span>
                    </span>
                </p>
                <p v-if="isValue(movie.actors)" class="flex items-center gap-1.5 line-clamp-1">
                    <span
                        class="shrink-0 text-[8px] font-black tracking-widest uppercase text-gray-400 dark:text-gray-600 w-6">Cast</span>
                    <span class="truncate font-semibold text-gray-700 dark:text-gray-300">
                        <span v-for="(actor, index) in movie.actors.split(',').slice(0, 2)" :key="index"
                              itemprop="actor" itemscope itemtype="https://schema.org/Person">
                            <span itemprop="name">{{ actor.trim() }}</span><span v-if="index < 1">, </span>
                        </span>
                    </span>
                </p>

                <!-- Genre tags -->
                <div v-if="movie.genre"
                     class="flex flex-wrap gap-1 pt-2 border-t border-gray-100 dark:border-gray-800/60">
                    <span
                        v-for="genre in movie.genre.split(',').slice(0, 3)"
                        :key="genre"
                        class="text-[8px] px-1.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-bold uppercase tracking-wide"
                    >
                        {{ genre.trim() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="flex gap-2 p-3 pt-0 mt-auto">
            <WatchlistButton
                :movie="movie"
                class="flex-none !bg-gray-100 dark:!bg-gray-800 hover:!bg-gray-200 dark:hover:!bg-gray-700 !text-gray-900 dark:!text-white !text-xs !font-bold !py-2.5 !px-3 !rounded-xl !transition-all !duration-200"
            />
            <button
                class="flex-1 flex justify-center items-center gap-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold py-2.5 px-3 rounded-xl transition-colors duration-200 text-xs"
                type="button"
                @click.stop="searchWatchDownload(movie.title)"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2"/>
                </svg>
                Watch
            </button>
            <button
                class="flex-1 flex justify-center items-center gap-1.5 bg-primary-600 hover:bg-primary-500 text-white font-bold py-2.5 px-3 rounded-xl group/detail transition-colors duration-200 text-xs shadow-md shadow-primary-500/25"
                type="button"
                @click.stop="openDetail(movie.imdb_id, movie.title)"
            >
                Details
                <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover/detail:translate-x-0.5"
                     fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                     viewBox="0 0 24 24">
                    <path d="M5 12h14m-7-7 7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>
</template>
<script setup>
import {computed, inject} from "vue";
import WatchlistButton from "@/Components/WatchlistButton.vue";

const props = defineProps({
    movie: Object
})
const emit = defineEmits(['selected', 'share']);

const gtag = inject("gtag");

const viewDetail = (imdbID) => {
    // Vibrate on mobile if supported
    if (typeof navigator !== 'undefined' && 'vibrate' in navigator) {
        navigator.vibrate(10);
    }
    emit('selected');
};

const openDetail = (imdbID, title) => {
    // Vibrate on mobile if supported
    if (typeof navigator !== 'undefined' && 'vibrate' in navigator) {
        navigator.vibrate(10);
    }

    if (window.innerWidth < 1024) {
        if (document.startViewTransition) {
            document.startViewTransition(() => {
                emit('selected');
            });
        } else {
            emit('selected');
        }
    } else {
        window.open(route('movie.detail.full', {imdbID: imdbID}), '_blank');
    }
};
const isValue = function (value) {
  return value && value !== 'N/A';
};

const moviePoster = (movie)=>{
  return movie.poster && movie.poster !== 'N/A' ? movie.poster : '/assets/images/no-poster.jpg';
}

const handlePosterError = (event) => {
    event.target.src = '/assets/images/no-poster.jpg';
    event.target.classList.add('opacity-40', 'grayscale');
};

const formatVotes = (votes) => {
    if (!votes) return '';
    const num = parseInt(votes.toString().replace(/,/g, ''));
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M votes';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K votes';
    }
    return num + ' votes';
};

const basicRating = computed(() => {
  if (props.movie.imdb_rating) {
    let mappedRating = Math.round(props.movie.imdb_rating / 2);

    mappedRating = Math.max(1, Math.min(5, mappedRating));

    return mappedRating;
  }

  return null;

});

const freeSites = [
    'FMovies',
    '123Movies',
    'SolarMovie',
    'Movies123',
    'GoMovies',
    'Putlocker',
    'WatchSeriesHD',
    '5Movies',
    'Movie4K',
    'Yify Movies',
    'AZMovies',
    'YesMovies',
    'Vumoo',
    'CineBloom',
    'MovieStars',
    'LookMovie',
    'PopcornFlix',
    'Soap2Day',
    'MovieWatcher',
    'PrimeWire',
    'LosMovies',
    'WatchFree',
    'StreamM4u',
    'BMovies',
    'XMovies8',
    'Movie25',
];

const searchWatchDownload = (title) => {
    let platform = '';
    let url = '';
    if (Math.random() > 0.5) {
        // Youtube search
        platform = 'YouTube';
        const query = `${title} full movie`;
        url = `https://www.youtube.com/results?search_query=${encodeURIComponent(query)}`;

        window.open(url, '_blank');
    } else {
        platform = 'Google Search';
        const site = freeSites[Math.floor(Math.random() * freeSites.length)];
        const query = `${site} ${title} full movie`;
        const url = `https://www.google.com/search?q=${encodeURIComponent(query)}`;
        window.open(url, '_blank');
    }
    if (gtag) {
        gtag.trackExternalClick(platform, url);
    }
};

</script>
