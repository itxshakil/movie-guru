<template>
    <div
        class="dark:bg-gray-900 dark:text-white border rounded-lg shadow-sm group relative flex flex-col justify-between  hover:scale-105 transition-all duration-500 ease-in"
       itemprop="mainEntity" itemscope itemtype="https://schema.org/Movie">
    <div>
      <div
          class="relative h-96 w-full overflow-hidden rounded-lg rounded-b-none bg-white dark:bg-gray-700 aspect-2/3 sm:aspect-16/9 lg:aspect-video group-hover:opacity-75">
        <img :alt="movie.title + ' Poster'" itemprop="image"
             :src="moviePoster(movie)"
             @click="viewDetail(movie.imdb_id)"
             loading="lazy"
             class="h-full w-full object-cover object-center italic hover:scale-105 transition-all duration-500 ease-in">
      </div>
      <h3 class="mt-2 p-2 pb-1 text-sm text-gray-500 dark:text-gray-400">
        <span class="capitalize">{{ movie.type }}  </span> -
        <span v-if="movie.release_date"
              :title="movie.release_date ? 'Released on '+ movie.release_date : 'Release Year'"
              class="mt-2 pr-2 text-sm text-gray-500 dark:text-gray-400" itemprop="datePublished"
              v-text="movie.release_date"></span>
        <template v-else>
          {{ movie.year }}
        </template>
      </h3>
      <div class="flex justify-between items-end px-2 py-0">
        <p itemprop="name" class="text-base font-semibold text-gray-900 dark:text-white"
           v-text="movie.title"></p>
      </div>

      <div v-if="basicRating" class="flex items-center ml-2 mb-2">
        <svg
            v-for="i in 5"
            :key="i"
            :class="i <= basicRating ? 'text-yellow-500 dark:text-yellow-400' : 'text-gray-300 dark:text-gray-400'"
            aria-hidden="true" class="h-4 w-4 shrink-0" fill="currentColor"
            viewBox="0 0 20 20">
          <path clip-rule="evenodd"
                d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                fill-rule="evenodd"/>
        </svg>
      </div>


      <div class="px-2 pb-3 space-y-1 text-sm text-gray-600 dark:text-gray-300">
        <p v-if="isValue(movie.writer)">
          <strong>Written by </strong>
          <span itemprop="creator" itemscope itemtype="https://schema.org/Person">
            <span itemprop="name">{{ movie.writer }}</span>
          </span>
        </p>

        <p v-if="isValue(movie.director)">
          <strong>Directed by </strong>
          <span itemprop="director" itemscope itemtype="https://schema.org/Person">
            <span itemprop="name">{{ movie.director }}</span>
          </span>
        </p>
          <p v-if="isValue(movie.actors)">
              <strong>Featuring </strong>
              <span v-for="(actor, index) in movie.actors.split(',').slice(0, 3)" :key="index" class="inline-block">
            <span itemprop="actor" itemscope itemtype="https://schema.org/Person">
              <span itemprop="name">{{ actor.trim() }}</span><span v-if="index < 2">,</span>
            </span>
          </span>
          </p>
      </div>

    </div>
        <div class="flex gap-1 align-items-center">
            <button
                class="m-2 mb-4 flex justify-center gap-2 items-center bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-full"
                type="button"
                @click="searchWatchDownload(movie.title)">
                <span>Watch/Download</span>
            </button>

            <button
                class="flex-1 m-2 mb-4 whitespace-nowrap flex justify-center gap-2 items-center bg-primary-500 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded-full group/detail"
                type="button"
                @click="viewDetail(movie.imdb_id)">
                <span class="">View Details</span>
                <svg class="w-5 h-5 text-xs group-hover/detail:translate-x-1.5 transition-all duration-600 ease-out"
                     fill="none"
                     height="24" stroke="currentColor"
                     stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                     width="24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                    <path
                        d="M12 2l.324 .005a10 10 0 1 1 -.648 0l.324 -.005zm.613 5.21a1 1 0 0 0 -1.32 1.497l2.291 2.293h-5.584l-.117 .007a1 1 0 0 0 .117 1.993h5.584l-2.291 2.293l-.083 .094a1 1 0 0 0 1.497 1.32l4 -4l.073 -.082l.064 -.089l.062 -.113l.044 -.11l.03 -.112l.017 -.126l.003 -.075l-.007 -.118l-.029 -.148l-.035 -.105l-.054 -.113l-.071 -.111a1.008 1.008 0 0 0 -.097 -.112l-4 -4z"
                        fill="currentColor" stroke-width="0"/>
                </svg>
            </button>
        </div>
  </div>
</template>
<script setup>
// TODO: Generate media Object
import {computed} from "vue";

const props = defineProps({
    movie: Object
})
const emit = defineEmits(['selected']);

const viewDetail = (imdbID) => {
    emit('selected');
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
    if (Math.random() > 0.5) {
        // Youtube search
        const query = `${title} full movie`;
        const url = `https://www.youtube.com/results?search_query=${encodeURIComponent(query)}`;

        window.open(url, '_blank');
    } else {
        const site = freeSites[Math.floor(Math.random() * freeSites.length)];
        const query = `${site} ${title} full movie`;
        const url = `https://www.google.com/search?q=${encodeURIComponent(query)}`;
        window.open(url, '_blank');
    }
};

</script>
