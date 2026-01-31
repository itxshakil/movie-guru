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
             @error="handlePosterError"
             loading="lazy"
             class="h-full w-full object-cover object-center italic transition-transform duration-500 group-hover:scale-105 cursor-pointer">
      </div>
        <h3 class="mt-2 px-3 pb-1 text-[10px] font-bold tracking-wider uppercase text-gray-400 dark:text-gray-500 flex items-center">
            <span class="bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md text-primary-600 dark:text-primary-400">{{
                    movie.type
                }}</span>
            <span v-if="movie.release_date || movie.year" class="mx-2 opacity-50">•</span>
        <span v-if="movie.release_date"
              :title="movie.release_date ? 'Released on '+ movie.release_date : 'Release Year'"
              class="font-medium" itemprop="datePublished"
              v-text="movie.release_date"></span>
            <template v-else-if="movie.year">
          {{ movie.year }}
        </template>
      </h3>
        <div class="px-3 py-1 flex items-start justify-between gap-2">
            <p class="text-xl font-black text-gray-900 dark:text-white line-clamp-2 leading-tight flex-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors"
               itemprop="name"
           v-text="movie.title"></p>
            <button
                class="mt-1 p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors rounded-full hover:bg-primary-50 dark:hover:bg-primary-900/20 active:scale-90 shrink-0"
                title="Share Movie"
                @click.stop="$emit('share', movie)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2.5"/>
                </svg>
            </button>
      </div>

        <div v-if="Number(movie.imdb_rating)" class="flex items-center ml-3 mb-2.5">
            <div
                class="flex items-center bg-gray-50 dark:bg-gray-800/50 px-2 py-1 rounded-lg border border-gray-100 dark:border-gray-800">
                <svg
                    v-for="i in 5"
                    :key="i"
                    :class="i <= basicRating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                    aria-hidden="true" class="h-3.5 w-3.5 shrink-0" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path clip-rule="evenodd"
                          d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                          fill-rule="evenodd"/>
                </svg>
                <span class="ml-1.5 text-xs font-black text-gray-700 dark:text-gray-200">{{
                        (movie.imdb_rating / 2).toFixed(1)
                    }}</span>
            </div>
            <span v-if="movie.imdb_votes"
                  class="ml-auto mr-3 text-[10px] text-gray-400 dark:text-gray-500 font-bold tracking-tight">
                {{ formatVotes(movie.imdb_votes) }}
            </span>
      </div>


        <div class="px-3 pb-4 space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
            <p v-if="isValue(movie.director)" class="line-clamp-1 flex items-center">
                <span
                    class="font-bold text-gray-400 dark:text-gray-500 uppercase text-[8px] tracking-wider bg-gray-50 dark:bg-gray-800/50 px-1.5 py-0.5 rounded border border-gray-100 dark:border-gray-800 shrink-0">Dir</span>
                <span class="ml-2 text-gray-900 dark:text-gray-100 font-extrabold truncate" itemprop="director"
                      itemscope itemtype="https://schema.org/Person">
            <span itemprop="name">{{ movie.director }}</span>
          </span>
        </p>
            <p v-if="isValue(movie.actors)" class="line-clamp-1 flex items-center">
                <span
                    class="font-bold text-gray-400 dark:text-gray-500 uppercase text-[8px] tracking-wider bg-gray-50 dark:bg-gray-800/50 px-1.5 py-0.5 rounded border border-gray-100 dark:border-gray-800 shrink-0">Cast</span>
                <span class="ml-2 text-gray-900 dark:text-gray-100 font-extrabold truncate">
            <span v-for="(actor, index) in movie.actors.split(',').slice(0, 2)" :key="index" class="inline-block">
            <span itemprop="actor" itemscope itemtype="https://schema.org/Person">
              <span itemprop="name">{{ actor.trim() }}</span><span v-if="index < 1" class="mr-1">,</span>
            </span>
            </span>
          </span>
          </p>
            <div v-if="movie.genre"
                 class="flex flex-wrap gap-1 mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                <span v-for="genre in movie.genre.split(',').slice(0, 3)" :key="genre"
                      class="text-[8px] px-1.5 py-0.5 rounded bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 font-black border border-primary-100 dark:border-primary-800/50 uppercase tracking-tight">
                    {{ genre.trim() }}
                </span>
            </div>
      </div>

    </div>
        <div class="flex gap-2 p-3 pt-0 mt-auto">
            <button
                class="flex-1 flex justify-center items-center bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-900 dark:text-white font-extrabold py-2.5 px-3 rounded-xl transition-all duration-200 text-sm active:scale-95"
                type="button"
                @click.stop="searchWatchDownload(movie.title)">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2"/>
                    <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2"/>
                </svg>
                <span>Watch</span>
            </button>

            <button
                class="flex-1 flex justify-center gap-2 items-center bg-primary-600 hover:bg-primary-700 text-white font-extrabold py-2.5 px-3 rounded-xl group/detail transition-all duration-200 text-sm shadow-lg shadow-primary-500/20 active:scale-95"
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
