<template>
    <div
        class="dark:bg-gray-900 dark:text-white border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm group relative flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-out active:scale-[0.98] cursor-pointer"
        itemprop="mainEntity" itemscope itemtype="https://schema.org/Movie"
        @click="openDetail(movie.imdb_id, movie.title)">
    <div>
      <div
          class="relative h-96 w-full overflow-hidden rounded-t-2xl bg-gray-100 dark:bg-gray-800 aspect-2/3">
        <img :alt="movie.title + ' Poster'" itemprop="image"
             :src="moviePoster(movie)"
             @click="openDetail(movie.imdb_id, movie.title)"
             loading="lazy"
             class="h-full w-full object-cover object-center italic transition-transform duration-500 group-hover:scale-105 cursor-pointer">
      </div>
        <h3 class="mt-2 p-3 pb-1 text-xs font-medium tracking-wide uppercase text-gray-500 dark:text-gray-400">
            <span class="capitalize">{{ movie.type }}  </span>
            <span v-if="movie.release_date || movie.year" class="mx-1">•</span>
        <span v-if="movie.release_date"
              :title="movie.release_date ? 'Released on '+ movie.release_date : 'Release Year'"
              class="" itemprop="datePublished"
              v-text="movie.release_date"></span>
            <template v-else-if="movie.year">
          {{ movie.year }}
        </template>
      </h3>
        <div class="px-3 py-1">
            <p class="text-lg font-bold text-gray-900 dark:text-white line-clamp-2 leading-tight" itemprop="name"
           v-text="movie.title"></p>
      </div>

        <div v-if="basicRating" class="flex items-center ml-3 mb-2">
            <svg
                v-for="i in 5"
                :key="i"
                :class="i <= basicRating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                aria-hidden="true" class="h-4 w-4 shrink-0" fill="currentColor"
                viewBox="0 0 20 20">
                <path clip-rule="evenodd"
                      d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                      fill-rule="evenodd"/>
            </svg>
            <span class="ml-2 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ movie.imdb_rating }}</span>
      </div>


        <div class="px-3 pb-4 space-y-1 text-sm text-gray-600 dark:text-gray-400">
            <p v-if="isValue(movie.director)" class="line-clamp-1">
                <span class="font-medium text-gray-500 dark:text-gray-500">Dir:</span>
                <span class="ml-1" itemprop="director" itemscope itemtype="https://schema.org/Person">
            <span itemprop="name">{{ movie.director }}</span>
          </span>
        </p>
            <p v-if="isValue(movie.actors)" class="line-clamp-1">
                <span class="font-medium text-gray-500 dark:text-gray-500">Cast:</span>
                <span v-for="(actor, index) in movie.actors.split(',').slice(0, 2)" :key="index"
                      class="inline-block ml-1">
            <span itemprop="actor" itemscope itemtype="https://schema.org/Person">
              <span itemprop="name">{{ actor.trim() }}</span><span v-if="index < 1">,</span>
            </span>
          </span>
          </p>
      </div>

    </div>
        <div class="flex gap-2 p-3 pt-0 mt-auto">
            <button
                class="flex-1 flex justify-center items-center bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-900 dark:text-white font-bold py-2.5 px-3 rounded-xl transition-colors duration-200 text-sm"
                type="button"
                @click.stop="searchWatchDownload(movie.title)">
                <span>Watch</span>
            </button>

            <button
                class="flex-1 flex justify-center gap-2 items-center bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-3 rounded-xl group/detail transition-all duration-200 text-sm shadow-lg shadow-primary-500/20"
                type="button"
                @click.stop="openDetail(movie.imdb_id, movie.title)">
                <span>Details</span>
                <svg class="w-4 h-4 transition-transform duration-300 group-hover/detail:translate-x-1"
                     fill="none"
                     stroke="currentColor"
                     stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" viewBox="0 0 24 24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 12h14m-7-7 7 7-7 7"/>
                </svg>
            </button>
        </div>
  </div>
</template>
<script setup>
// TODO: Generate media Object
import {computed, inject} from "vue";

const props = defineProps({
    movie: Object
})
const emit = defineEmits(['selected']);

const gtag = inject("gtag");

const viewDetail = (imdbID) => {
    // Vibrate on mobile if supported
    if ('vibrate' in navigator) {
        navigator.vibrate(10);
    }
    emit('selected');
};

const openDetail = (imdbID, title) => {
    // Vibrate on mobile if supported
    if ('vibrate' in navigator) {
        navigator.vibrate(10);
    }

    if (window.innerWidth < 1024) {
        emit('selected');
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
