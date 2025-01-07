<script setup>
import {Head, useForm} from '@inertiajs/vue3';
import NewsletterForm from '@/Components/NewsletterForm.vue';
import {computed, ref} from 'vue';
import LoadingSpinnerButton from '@/Components/LoadingSpinnerButton.vue';
import Show from '@/Components/Show.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import SearchCard from '@/Components/SearchCard.vue';

// TODO: Generate Search result response Object
const props = defineProps({
    searchResults: Object,
    search: String,
    page: String,
    movieType: {
        type: String,
        default: 'all',
    },
    year: Number,
    movieTypes: Array,
    nextUrl: String,
    trendingQueries: Array,
});

const showSuggestions = ref(false);
const filteredTrendingQueries = ref([...props.trendingQueries]);

const hideSuggestions = () => {
  setTimeout(() => (showSuggestions.value = false), 200); // Delay to allow click event to trigger
};

const filterTrendingQueries = () => {
  filteredTrendingQueries.value = props.trendingQueries.filter((query) =>
      query.toLowerCase().includes(form.s.toLowerCase())
  );
};

const selectTrendingQuery = (query) => {
  form.s = query; // Set the trending query as the search input
  showSuggestions.value = false; // Hide suggestions
  doSearch(); // Trigger the search
};


const form = useForm({
    s: props.search,
    type: props.movieType,
    year: props.year,
    page: 1,
});

const nextURLLink = ref(props.nextUrl);
const movieList = ref(props.searchResults.Search);
const loading = ref(false);
const selectedIMDBId = ref(null);
const timeout = ref(0);
const maxRetry  = ref(3);

const doSearch = () => {
    form.get(route('search'), {
        preserveScroll: true
    });
};

const loadMore = () => {
    loading.value = true;
    axios.get(nextURLLink.value).then(response => {
        if (response.data.searchResults.Response === 'False') {
            alert('Something went wrong. Please try again later. S-100');
            loading.value = false
            return;
        }

        movieList.value = movieList.value.concat(response.data.searchResults.Search);
        nextURLLink.value = response.data.nextUrl;
    }).catch((error) => {
        if(error.response.status === 429 && maxRetry.value){
            const retryAfter = error.response.headers['retry-after'];
            timeout.value = setTimeout(()=>{
                loadMore()
            }, retryAfter * 1000);

            maxRetry.value--;
            return;
        }
        alert(`Something went wrong. Error -RE-${response.status}`)
        loading.value = false
    }).finally(() => {
        loading.value = false;
    });
};

const viewDetail = (imdb_id, sectionName) => {
  selectedIMDBId.value = imdb_id;

  // Google Analytics event
  if (window.gtag) {
    window.gtag('event', 'view_detail', {
      event_category: 'View Detail',
      event_label: sectionName,
      value: imdb_id
    });
  } else {
    console.warn("Google Analytics not initialized.");
  }
};

defineOptions({ layout: BaseLayout })

const pageTitle = props.searchResults.Response === 'False' ?
    `No results found for ${props.search}` :
    `${props.searchResults.totalResults} results found for ` + props.search;
const pageDescription = `Explore an extensive database of movies with detailed information, reviews, and ratings. Find your next favorite film effortlessly with our user-friendly search feature. Discover ${props.searchResults.totalResults} movies related to ${props.search} and dive into the world of entertainment.`;

const pageUrl = window.location.href;

const ogImage = computed(() => {
    if (movieList.value && movieList.value.length > 0) {
        return moviePoster(movieList.value[0]);
    } else {
        return '/assets/images/no-poster.jpg';
    }
});

const moviePoster = (movie) => {
    return movie.Poster && movie.Poster !== 'N/A' ? movie.Poster : '/assets/images/no-poster.jpg';
}
</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>
        <meta :content="pageDescription" head-key="description" name="description"/>
        <meta property="subject" :content="pageTitle" head-key="subject">

        <meta property="og:url" :content="pageUrl" head-key="og:url">
        <meta property="og:type" content="website">
        <meta property="og:title" :content="pageTitle" head-key="og:title">
        <meta property="og:description" :content="pageDescription" head-key="og:description">
        <meta property="og:image" :content="ogImage" head-key="og:image">

        <!-- Twitter Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta property="twitter:domain" content="movieguru.shakiltech.com">
        <meta property="twitter:url" :content="pageUrl" head-key="twitter:url">
        <meta name="twitter:title" :content="pageTitle" head-key="twitter:title">
        <meta name="twitter:description" :content="pageDescription" head-key="twitter:description">
        <meta name="twitter:image" :content="ogImage" head-key="twitter:image">

        <link rel="canonical" :href="pageUrl" head-key="canonical" />
    </Head>
    <div class="bg-gray-100 dark:bg-gray-900 dark:text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl pt-24 sm:py-24 lg:max-w-none">

                <div class="mt-2 flex flex-col-reverse lg:flex-row gap-2 justify-between w-full items-baseline">
                    <h1 class="mb-4 text-xl font-bold text-gray-900 dark:text-white w-full " v-if="searchResults.Response === 'False'">
                        No results found for {{ search }}
                    </h1>
                    <h1 v-else class="text-xl font-bold text-gray-900 dark:text-white w-full ">
                        {{ searchResults.totalResults }} results found for {{ search }}
                    </h1>

                    <form class="flex flex-col sm:flex-row gap-2 items-center w-full" @submit.prevent="doSearch">
                        <div class="w-full sm:min-w-64 grow">
                            <InputLabel class="sr-only" for="search-input" value="Search"/>
                          <div class="relative">
                            <TextInput
                                type="search"
                                enterkeyhint="search"
                                id="search-input"
                                autocomplete="off"
                                v-model="form.s"
                                class="w-full"
                                placeholder="Search movies and series..."
                                @focus="showSuggestions = true"
                                @blur="hideSuggestions"
                                @input="filterTrendingQueries"
                            />

                            <ul
                                v-if="showSuggestions && filteredTrendingQueries.length > 0"
                                class="absolute bg-white dark:bg-gray-800 shadow-lg rounded-md mt-2 w-full z-10">
                              <li
                                  v-for="query in filteredTrendingQueries"
                                  :key="query"
                                  @mousedown="selectTrendingQuery(query)"
                                  class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                                {{ query }}
                              </li>
                            </ul>
                          </div>
                        </div>
                        <div class="flex gap-2 sm:w-60 w-full">
                            <div class="w-full sm:w-36">
                                <InputLabel class="sr-only" for="movie-type-select" value="Select Type"/>
                                <select v-model="form.type" id="movie-type-select"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 disabled:bg-slate-400 dark:disabled:bg-gray-600 dark:read-only:bg-gray-600 dark:text-gray-300 dark:focus:border-primary-600 dark:focus:ring-primary-600 shadow-sm bg-gray-400/10 placeholder-gray-500 border-transparent transition duration-75 rounded-lg focus:bg-white focus:placeholder-gray-400 focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 dark:focus:text-gray-700 dark:placeholder-gray-400 focus:read-only:text-gray-200">
                                    <option :value="null">All types</option>
                                    <option v-for="type in movieTypes" :value="type" class="capitalize">{{ type }}
                                    </option>
                                </select>
                            </div>
                            <div class="w-full sm:w-20">
                                <InputLabel class="sr-only" for="year-input" value="Year"/>
                                <TextInput v-model="form.year" id="year-input" class="w-full" placeholder="Year" type="number"/>
                            </div>
                        </div>
                        <LoadingSpinnerButton :is-loading="form.processing" class="w-full sm:w-auto justify-center"
                                              value="Search"/>
                    </form>
                </div>

                <div v-if="movieList && movieList.length" class="py-6 space-y-12 sm:grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 sm:gap-6 sm:space-y-0">
                    <SearchCard
                        v-for="movie in movieList"
                        :key="movie.imdb_id"
                        :movie="movie"
                        @selected="viewDetail(movie.imdb_id, 'Search')"
                    />
                </div>

                <div class="flex justify-center mt-2" v-show="nextURLLink">
                    <button v-show="nextURLLink && loading === false"
                            class="bg-primary-500 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded"
                            @click="loadMore">Load More
                    </button>
                    <LoadingSpinnerButton v-show="loading" :isLoading="true" value="Loading..."/>
                </div>
            </div>
        </div>
    </div>

    <NewsletterForm/>

    <div class="install-prompt-container w-full p-4 text-center bg-white border border-gray-200 rounded-lg shadow sm:p-8 dark:bg-gray-800 dark:border-gray-700">
        <h5 class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">Access details from anywhere</h5>
        <p class="mb-5 text-base text-gray-500 sm:text-lg dark:text-gray-400">Stay up to date and move work forward
            with Movie Guru on iOS & Android. Install the app today.</p>
        <div class="items-center justify-center space-y-4 sm:flex sm:space-y-0 sm:space-x-4 rtl:space-x-reverse">
            <div class="install-prompt w-full sm:w-auto bg-gray-800 hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-300 text-white rounded-lg inline-flex items-center justify-center px-4 py-2.5 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                <svg aria-hidden="true" class="me-3 w-7 h-7" data-icon="apple" data-prefix="fab" focusable="false"
                     role="img" viewBox="0 0 384 512" xmlns="http://www.w3.org/2000/svg">
                    <path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5q0 39.3 14.4 81.2c12.8 36.7 59 126.7 107.2 125.2 25.2-.6 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.9 48.6-.7 90.4-82.5 102.6-119.3-65.2-30.7-61.7-90-61.7-91.9zm-56.6-164.2c27.3-32.4 24.8-61.9 24-72.5-24.1 1.4-52 16.4-67.9 34.9-17.5 19.8-27.8 44.3-25.6 71.9 26.1 2 49.9-11.4 69.5-34.3z"
                          fill="currentColor"></path>
                </svg>
                <div class="text-left rtl:text-right">
                    <div class="mb-1 text-xs">Install on the</div>
                    <div class="-mt-1 font-sans text-sm font-semibold">Mac App Store</div>
                </div>
            </div>
            <div class="install-prompt w-full sm:w-auto bg-gray-800 hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-300 text-white rounded-lg inline-flex items-center justify-center px-4 py-2.5 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                <svg aria-hidden="true" class="me-3 w-7 h-7" data-icon="google-play" data-prefix="fab"
                     focusable="false" role="img" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                    <path d="M325.3 234.3L104.6 13l280.8 161.2-60.1 60.1zM47 0C34 6.8 25.3 19.2 25.3 35.3v441.3c0 16.1 8.7 28.5 21.7 35.3l256.6-256L47 0zm425.2 225.6l-58.9-34.1-65.7 64.5 65.7 64.5 60.1-34.1c18-14.3 18-46.5-1.2-60.8zM104.6 499l280.8-161.2-60.1-60.1L104.6 499z"
                          fill="currentColor"></path>
                </svg>
                <div class="text-left rtl:text-right">
                    <div class="mb-1 text-xs">Get in on</div>
                    <div class="-mt-1 font-sans text-sm font-semibold">Google Play</div>
                </div>
            </div>
        </div>
    </div>

    <Show v-if="selectedIMDBId" :imdbID="selectedIMDBId" @close="selectedIMDBId = null;"/>
</template>
<style scoped>
    @media (display-mode: standalone) {
        .install-prompt-container {
            display: none;
        }
    }

    ul {
      transition: all 0.2s ease-in-out;
    }
</style>
