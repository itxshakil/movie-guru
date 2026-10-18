<script setup>
import {Head, useForm} from '@inertiajs/vue3';
import NewsletterForm from '@/Components/NewsletterForm.vue';
import {computed, inject, nextTick, ref} from 'vue';
import Show from '@/Components/Show.vue';
import TextInput from '@/Components/TextInput.vue';
import SectionHeading from '@/Components/Ui/SectionHeading.vue';
import SelectInput from '@/Components/Ui/SelectInput.vue';
import UiButton from '@/Components/Ui/UiButton.vue';
import VoiceSearchButton from '@/Components/VoiceSearchButton.vue';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import SearchCard from '@/Components/SearchCard.vue';
import {isPlainClick} from '@/Components/cardClicks.js';

const gtag = inject('gtag');

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
    recentlyReleasedMovies: Array,
    recommendedMovies: Array,
});

const showSuggestions = ref(false);
const filteredTrendingQueries = ref(props.trendingQueries ? [...props.trendingQueries] : []);

const hideSuggestions = () => {
    setTimeout(() => (showSuggestions.value = false), 200); // Delay to allow click event to trigger
};

const filterTrendingQueries = () => {
    if (!props.trendingQueries) return;
    filteredTrendingQueries.value = props.trendingQueries.filter((query) =>
        query.toLowerCase().includes(form.s.toLowerCase())
    );
};

const selectTrendingQuery = (query) => {
    form.s = query; // Set the trending query as the search input
    showSuggestions.value = false; // Hide suggestions
    if (filteredTrendingQueries.value) {
        filteredTrendingQueries.value = [];
    }
    // No delay, but let's see if doSearch works better if we manually set form value
    doSearch(); // Trigger the search
};

const clearSearch = () => {
    form.s = '';
    doSearch();
};


const form = useForm({
    s: props.search,
    type: props.movieType,
    year: props.year || '',
    page: 1,
});

const typeModel = computed({
    get: () => form.type ?? '',
    set: (value) => {
        form.type = value || null;
    },
});

const nextURLLink = ref(props.nextUrl);
const movieList = ref(props.searchResults.Search);
const resultsGrid = ref(null);
const loading = ref(false);
const selectedIMDBId = ref(null);
const selectedTitle = ref(null);
const timeout = ref(0);
const maxRetry = ref(3);

const doSearch = () => {
    if (gtag) {
        gtag.trackSearch(form.s, form.type);
    }
    form.get(route('search'), {
        preserveScroll: true
    });
};

const searchByVoice = (transcript) => {
    form.s = transcript;
    doSearch();
};

const notifyError = (message) => {
    new BroadcastChannel('toast-notifications').postMessage({message, level: 'danger'});
};

const loadMore = async () => {
    loading.value = true;
    const previousCount = movieList.value.length;

    try {
        const response = await fetch(nextURLLink.value, {
            headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
        });

        if (response.status === 429 && maxRetry.value) {
            const retryAfter = response.headers.get('retry-after');
            timeout.value = setTimeout(() => {
                loadMore()
            }, retryAfter * 1000);

            maxRetry.value--;
            return;
        }

        if (!response.ok) {
            notifyError(`Something went wrong. Error -RE-${response.status}`);
            return;
        }

        const data = await response.json();
        if (data.searchResults.Response === 'False') {
            notifyError('Something went wrong. Please try again later. S-100');
            return;
        }

        movieList.value = movieList.value.concat(data.searchResults.Search);
        nextURLLink.value = data.nextUrl;

        // Bring the first newly-loaded card into view, contained to the
        // results grid so it doesn't jump the whole page under the header.
        await nextTick();
        const firstNewCard = resultsGrid.value?.children?.[previousCount];
        if (firstNewCard) {
            const scrolled = firstNewCard.scrollIntoView({behavior: 'smooth', block: 'nearest', container: 'nearest'});
            if (scrolled && typeof scrolled.then === 'function') {
                await scrolled;
            }
        }
    } catch (error) {
        notifyError('Something went wrong. Please try again later.');
    } finally {
        loading.value = false;
    }
};

const onLoadMoreClick = (event) => {
    if (!isPlainClick(event)) {
        return;
    }
    event.preventDefault();
    event.stopPropagation();
    if (!loading.value) {
        loadMore();
    }
};

const shareMovie = async (detail) => {
    if (!detail) return;
    const shareData = {
        title: detail.Title || detail.title,
        text: `Check out ${detail.Title || detail.title} on Movie Guru!`,
        url: route('movie.detail.full', {imdbID: detail.imdbID || detail.imdb_id}),
    };

    try {
        if (navigator.share) {
            await navigator.share(shareData);
        } else {
            await navigator.clipboard.writeText(shareData.url);
            new BroadcastChannel('toast-notifications').postMessage({message: 'Link copied to clipboard!', level: 'success'});
        }
    } catch (err) {
        console.error('Error sharing:', err);
    }
};

const shareMovieFromCard = (movie) => {
    // Vibrate on mobile
    if (typeof navigator !== 'undefined' && 'vibrate' in navigator) {
        navigator.vibrate(10);
    }
    shareMovie(movie);
};

const viewDetail = (imdb_id, sectionName, title = null) => {
    selectedIMDBId.value = imdb_id;
    if (title) {
        selectedTitle.value = title;
    }
    gtag?.trackViewDetail(imdb_id, sectionName, title);
};

defineOptions({layout: BaseLayout})

const pageTitle = props.searchResults.Response === 'False' ?
    `Hmm, Couldn't Find "${props.search}" - Try Another Search?` :
    `${props.searchResults.totalResults} results found for ` + props.search;
const pageDescription = props.searchResults.Response === 'False' ?
    `Oops! We couldn't find matches for “${props.search}”. Try exploring trending movies, or filter by genre and year to discover top-rated films and shows. The perfect pick might be a search away.` :
    `Looking for "${props.search}"? Get detailed info, cast, ratings, and find out where you can watch this movie now! Explore an extensive database of movies with detailed information, reviews, and ratings. Find your next favorite film effortlessly with our user-friendly search feature. Discover ${props.searchResults.totalResults} movies related to ${props.search} and dive into the world of entertainment.`;

const pageUrl = typeof window !== 'undefined' ? window.location.href : '';

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
        <meta :content="pageTitle" head-key="subject" property="subject">

        <meta :content="pageUrl" head-key="og:url" property="og:url">
        <meta content="website" property="og:type">
        <meta :content="pageTitle" head-key="og:title" property="og:title">
        <meta :content="pageDescription" head-key="og:description" property="og:description">
        <meta :content="ogImage" head-key="og:image" property="og:image">

        <!-- Twitter Meta Tags -->
        <meta content="summary_large_image" name="twitter:card">
        <meta content="movieguru.shakiltech.com" property="twitter:domain">
        <meta :content="pageUrl" head-key="twitter:url" property="twitter:url">
        <meta :content="pageTitle" head-key="twitter:title" name="twitter:title">
        <meta :content="pageDescription" head-key="twitter:description" name="twitter:description">
        <meta :content="ogImage" head-key="twitter:image" name="twitter:image">

        <link :href="pageUrl" head-key="canonical" rel="canonical"/>
    </Head>
    <div class="bg-gray-100 dark:bg-gray-900 dark:text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl pt-24 pb-8 sm:pt-28 sm:pb-12 lg:max-w-none">
                <form :action="route('search')" class="w-full" method="get" role="search" @submit.prevent="doSearch">
                    <div class="relative">
                        <label class="sr-only" for="search-input">Search movies and series</label>
                        <div
                            class="flex items-center gap-2 rounded-full border border-gray-300 bg-white p-1.5 pr-3 pl-5 shadow-lg shadow-gray-900/5 transition-colors focus-within:border-primary-500 focus-within:ring-4 focus-within:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-900 dark:shadow-black/30"
                        >
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-400" fill="none"
                                 stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                            <input
                                id="search-input"
                                v-model="form.s"
                                autocomplete="off"
                                class="min-w-0 flex-1 border-0 bg-transparent px-1 py-2.5 text-base text-gray-900 placeholder:text-gray-400 focus:ring-0 focus:outline-none sm:text-lg dark:text-white"
                                enterkeyhint="search"
                                name="s"
                                placeholder="Search movies and series..."
                                type="search"
                                @blur="hideSuggestions"
                                @focus="showSuggestions = true"
                                @input="filterTrendingQueries"
                            />
                            <VoiceSearchButton v-if="!form.s" class="shrink-0" @result="searchByVoice"/>
                            <UiButton v-else aria-label="Clear search" size="icon" variant="ghost" @click="clearSearch">
                                <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor"
                                     stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </UiButton>
                        </div>

                        <ul
                            v-if="showSuggestions && filteredTrendingQueries.length > 0"
                            class="absolute z-20 mt-2 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                            <li class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-500 dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-400">
                                <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke-linecap="round"
                                          stroke-linejoin="round" stroke-width="2"></path>
                                </svg>
                                Trending searches
                            </li>
                            <li
                                v-for="query in filteredTrendingQueries"
                                :key="query"
                                class="flex cursor-pointer items-center gap-2 px-4 py-2 text-gray-700 transition-colors hover:bg-primary-50 dark:text-gray-200 dark:hover:bg-primary-900/30"
                                @mousedown="selectTrendingQuery(query)">
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-400" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round"
                                          stroke-linejoin="round" stroke-width="2"></path>
                                </svg>
                                {{ query }}
                            </li>
                        </ul>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3 sm:flex sm:items-center">
                        <div class="sm:w-40">
                            <label class="sr-only" for="movie-type-select">Select type</label>
                            <SelectInput id="movie-type-select" v-model="typeModel" name="type">
                                <option value="">All types</option>
                                <option v-for="type in movieTypes" :key="type" :value="type" class="capitalize">
                                    {{ type }}
                                </option>
                            </SelectInput>
                        </div>
                        <div class="sm:w-28">
                            <label class="sr-only" for="year-input">Year</label>
                            <TextInput id="year-input" v-model="form.year" name="year" placeholder="Year"
                                       type="number"/>
                        </div>
                        <UiButton :loading="form.processing" class="col-span-2 sm:w-auto" type="submit">
                            Search
                        </UiButton>
                    </div>
                </form>

                <h1 class="mt-8 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    <template v-if="searchResults.Response === 'False'">No results for '{{ search }}'</template>
                    <template v-else>{{ searchResults.totalResults }} results for '{{ search }}'</template>
                </h1>

                <div v-if="movieList && movieList.length" ref="resultsGrid"
                     class="grid gap-6 pt-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                     style="--card-density: compact;">
                    <SearchCard
                        v-for="movie in movieList"
                        :key="movie.imdb_id"
                        :movie="movie"
                        class="scroll-reveal cv-card"
                        @selected="viewDetail(movie.imdb_id, 'Search', movie.title)"
                        @share="shareMovieFromCard"
                    />
                </div>

                <div v-show="nextURLLink" class="mt-8 flex justify-center" @click.capture="onLoadMoreClick">
                    <UiButton :href="nextURLLink" :loading="loading" variant="secondary">
                        {{ loading ? 'Loading...' : 'Load more' }}
                    </UiButton>
                </div>
            </div>
        </div>
    </div>

    <section v-if="recentlyReleasedMovies && recentlyReleasedMovies.length" class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading subtitle="The latest movies to hit the screens." title="Fresh releases"/>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <SearchCard
                    v-for="movie in recentlyReleasedMovies"
                    :key="movie.imdb_id"
                    :movie="movie"
                    class="scroll-reveal cv-card"
                    @selected="viewDetail(movie.imdb_id, 'Recently Released Movies')"
                    @share="shareMovieFromCard"
                />
            </div>
        </div>
    </section>

    <section v-if="recommendedMovies && recommendedMovies.length" class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading subtitle="Timeless classics and must-watch films for every movie lover."
                            title="Recommended movies"/>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <SearchCard
                    v-for="movie in recommendedMovies"
                    :key="movie.imdb_id"
                    :movie="movie"
                    class="scroll-reveal cv-card"
                    @selected="viewDetail(movie.imdb_id, 'Recommended Movies')"
                    @share="shareMovieFromCard"
                />
            </div>
        </div>
    </section>

    <NewsletterForm/>

    <div class="install-prompt-container mx-auto my-12 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
            class="rounded-2xl border border-gray-200 bg-white p-6 text-center sm:p-10 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">Access details from anywhere</h2>
            <p class="mx-auto mb-6 max-w-xl text-base text-gray-500 sm:text-lg dark:text-gray-400">Stay up to date
                and move work forward with Movie Guru on iOS & Android. Install the app today.</p>
            <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
                <UiButton class="w-full sm:w-auto" variant="secondary">
                    <template #icon>
                        <svg aria-hidden="true" class="h-5 w-5" focusable="false" viewBox="0 0 384 512"
                             xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5q0 39.3 14.4 81.2c12.8 36.7 59 126.7 107.2 125.2 25.2-.6 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.9 48.6-.7 90.4-82.5 102.6-119.3-65.2-30.7-61.7-90-61.7-91.9zm-56.6-164.2c27.3-32.4 24.8-61.9 24-72.5-24.1 1.4-52 16.4-67.9 34.9-17.5 19.8-27.8 44.3-25.6 71.9 26.1 2 49.9-11.4 69.5-34.3z"
                                fill="currentColor"></path>
                        </svg>
                    </template>
                    Mac App Store
                </UiButton>
                <UiButton class="w-full sm:w-auto" variant="secondary">
                    <template #icon>
                        <svg aria-hidden="true" class="h-5 w-5" focusable="false" viewBox="0 0 512 512"
                             xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M325.3 234.3L104.6 13l280.8 161.2-60.1 60.1zM47 0C34 6.8 25.3 19.2 25.3 35.3v441.3c0 16.1 8.7 28.5 21.7 35.3l256.6-256L47 0zm425.2 225.6l-58.9-34.1-65.7 64.5 65.7 64.5 60.1-34.1c18-14.3 18-46.5-1.2-60.8zM104.6 499l280.8-161.2-60.1-60.1L104.6 499z"
                                fill="currentColor"></path>
                        </svg>
                    </template>
                    Google Play
                </UiButton>
            </div>
        </div>
    </div>

    <Show v-if="selectedIMDBId" :imdbID="selectedIMDBId" :title="selectedTitle" @close="selectedIMDBId = null;"
          @share="shareMovie"/>
</template>
<style scoped>
@media (display-mode: standalone) {
    .install-prompt-container {
        display: none;
    }
}
</style>
