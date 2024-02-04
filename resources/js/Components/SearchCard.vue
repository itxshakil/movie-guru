<template>
  <div class="dark:bg-gray-900 dark:text-white border rounded-lg shadow group relative flex flex-col justify-between" itemscope itemprop="mainEntity" itemtype="https://schema.org/Movie">
    <div>
      <div
          class="relative h-80 w-full overflow-hidden rounded-lg rounded-b-none bg-white dark:bg-gray-700 aspect-h-3 aspect-w-2 sm:aspect-h-1 sm:aspect-w-2 lg:aspect-h-1 lg:aspect-w-1 group-hover:opacity-75 sm:h-64">
        <img itemprop="image" :alt="movie.Title + ' Poster'"
             :src="moviePoster(movie)"
             loading="lazy"
             class="h-full w-full object-cover object-center italic">
      </div>
      <h3 class="mt-2 p-2 pb-1 text-sm text-gray-500 dark:text-gray-400">
        <span class="capitalize">{{ movie.Type }}  </span> - {{ movie.Year }}
      </h3>
      <div class="flex justify-between items-end px-2 py-0">
        <p itemprop="name" class="text-base font-semibold text-gray-900 dark:text-white"
           v-text="movie.Title"></p>

      </div>
    </div>
    <button
        class="m-2 mb-4 whitespace-nowrap flex justify-center gap-2 items-center bg-primary-500 hover:bg-primary-700 text-white font-bold py-1 px-2 rounded-full"
        type="button"
        @click="viewDetail(movie.imdbID)">
      <span class="">View Details</span>
      <svg class="w-5 h-5 text-xs" fill="none" height="24" stroke="currentColor"
           stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
           width="24" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
        <path
            d="M12 2l.324 .005a10 10 0 1 1 -.648 0l.324 -.005zm.613 5.21a1 1 0 0 0 -1.32 1.497l2.291 2.293h-5.584l-.117 .007a1 1 0 0 0 .117 1.993h5.584l-2.291 2.293l-.083 .094a1 1 0 0 0 1.497 1.32l4 -4l.073 -.082l.064 -.089l.062 -.113l.044 -.11l.03 -.112l.017 -.126l.003 -.075l-.007 -.118l-.029 -.148l-.035 -.105l-.054 -.113l-.071 -.111a1.008 1.008 0 0 0 -.097 -.112l-4 -4z"
            fill="currentColor" stroke-width="0"/>
      </svg>
    </button>
  </div>
</template>
<script setup>
// TODO: Generate media Object
defineProps({
    movie: Object
})
const emit = defineEmits(['selected']);

const viewDetail = (imdbID) => {
    emit('selected');
};

const moviePoster = (movie)=>{
    return movie.Poster && movie.Poster !== 'N/A' ? movie.Poster : '/assets/images/no-poster.jpg';
}
</script>
