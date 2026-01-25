<script setup>
import {Head, useForm} from '@inertiajs/vue3';
import NewsletterForm from '@/Components/NewsletterForm.vue';
import OurFeatures from '@/Components/OurFeatures.vue';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import LoadingSpinnerIcon from '@/Components/Icons/LoadingSpinner.vue';
import SearchCard from "@/Components/SearchCard.vue";
import {inject, ref} from "vue";
import Show from "@/Components/Show.vue";

const gtag = inject('gtag');

const props = defineProps({
  trendingSearchQueries: Array,
  popularMovies: Array,
  trendingMovies: Array,
  hiddenGemsMovies: Array,
  recentlyReleasedMovies: Array,
  topRatedMovies: Array,
  recommendedMovies: Array,
});

const selectedIMDBId = ref(null);

const form = useForm({
  s: '',
});

const doSearch = () => {
    if (gtag) {
        gtag.trackSearch(form.s);
    }
  form.get(route('search'));
};

const clearSearch = () => {
    form.s = '';
};

// Randomize trending search queries
const shuffledTrendingQueries = [...props.trendingSearchQueries].sort(() => Math.random() - 0.5);

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
            alert('Link copied to clipboard!');
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

const viewDetail = (imdb_id, sectionName) => {
    if (window.innerWidth < 1024) {
        selectedIMDBId.value = imdb_id;
    } else {
        window.open(route('movie.detail.full', {imdbID: imdb_id}), '_blank');
    }
    if (gtag) {
        gtag.trackViewDetail(imdb_id, sectionName);
    }
};


defineOptions({ layout: BaseLayout });

const pageTitle = 'Watch Movies & Web Series Online | Cast, Ratings & Where to Watch – Hidden Gems & Popular Picks!';
const pageDescription = 'Watch the latest movies & web series online. Explore cast, story, trailers, ratings & where to stream legally. Discover trending and hidden gems, all time top rated movies and series on MovieGuru.';
const pageUrl = typeof window !== 'undefined' ? window.location.href : '';
const ogImage = "https://movieguru.shakiltech.com/icons/ios/64.png";

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
    <div class="relative isolate px-6 lg:px-8 dark:bg-gray-950 dark:text-gray-100">
        <div class="mx-auto max-w-5xl py-24 sm:py-32">
            <div class="text-center">
                <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-6xl">
                    🎬 Discover. Explore. Enjoy. 🎥
                </h1>
                <p class="mt-8 text-xl sm:text-2xl leading-relaxed text-gray-600 dark:text-gray-400 max-w-2xl mx-auto font-medium text-pretty">
                    Dive into a world of movies & series. Find your next favorite today.
                </p>

                <div class="mt-10 flex items-center justify-center">
                    <form class="relative w-full max-w-2xl group/search" @submit.prevent="doSearch">
                        <label class="sr-only" for="search-input">Search movies and series</label>
                        <div class="relative flex items-center">
                            <svg
                                class="absolute left-5 h-6 w-6 text-gray-400 group-focus-within/search:text-primary-500 transition-all duration-300 pointer-events-none group-focus-within/search:scale-110"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2.5"/>
                            </svg>
                            <input id="search-input"
                                   v-model="form.s"
                                   aria-label="Search Movie or Series"
                                   autocomplete="off"
                                   autofocus
                                   class="w-full rounded-full border-2 border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-6 py-5 pl-14 pr-12 sm:pr-32 text-xl text-gray-900 dark:text-gray-200 placeholder-gray-400 focus:ring-4 focus:ring-primary-500/20 focus:border-primary-500 shadow-xl transition-all duration-300 group-hover/search:shadow-2xl group-hover/search:border-primary-300 dark:group-hover/search:border-primary-700"
                                   enterkeyhint="search"
                                   name="s"
                                   placeholder="Search movies and series..."
                                   required
                                   type="search"
                            />
                            <button
                                v-if="form.s"
                                class="absolute right-32 sm:right-44 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                type="button"
                                @click="clearSearch"
                            >
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"/>
                                </svg>
                            </button>
                        </div>
                        <button :class="form.processing ? 'opacity-70 cursor-wait' : ''"
                                :disabled="form.processing"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center justify-center rounded-full bg-primary-600 px-5 sm:px-8 py-3.5 font-bold text-white shadow-lg hover:bg-primary-700 hover:scale-105 active:scale-95 transition-all duration-200"
                                type="submit">
                            <LoadingSpinnerIcon v-show="form.processing" class="w-5 h-5 sm:mr-2"/>
                            <span class="hidden sm:inline">Explore</span>
                            <svg v-if="!form.processing" class="w-5 h-5 sm:hidden" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round"
                                      stroke-linejoin="round" stroke-width="3"></path>
                            </svg>
                        </button>
                    </form>
                </div>

                <div class="mt-6">
                    <h3 class="sr-only">Trending Searches</h3>
                    <div class="flex flex-wrap justify-center items-center gap-3">
                        <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mr-1">Hot Searches</span>
                        <li v-for="(query, index) in shuffledTrendingQueries.slice(0, 5)" :key="index"
                            class="list-none">
                            <button
                                class="px-4 py-1.5 text-xs font-bold rounded-full bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-primary-600 hover:text-white dark:hover:bg-primary-500 dark:hover:text-white transition-all duration-300 border-2 border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md hover:-translate-y-0.5 active:scale-95"
                                    @click="form.s = query; doSearch()">
                                {{ query }}
                            </button>
                        </li>
                    </div>
                </div>
            </div>
        </div>
    </div>
  <OurFeatures/>

  <div v-if="trendingMovies && trendingMovies.length" class="bg-white dark:bg-gray-900 py-8">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-base font-semibold text-primary-600 dark:text-primary-500">🔥 Trending Now</h2>
        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">Discover what everyone is watching right now
          and join the buzz.</p>
      </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <SearchCard
            v-for="movie in trendingMovies"
            :key="movie.imdb_id"
            :movie="movie"
            class="scroll-reveal"
            @selected="viewDetail(movie.imdb_id, 'Trending Movies')"
            @share="shareMovieFromCard"
        />
      </div>
    </div>
  </div>

  <div v-if="recentlyReleasedMovies && recentlyReleasedMovies.length" class="bg-white dark:bg-gray-900 py-8">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-base font-semibold text-primary-600 dark:text-primary-500">🎉 Fresh Releases</h2>
        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">Stay up-to-date with the latest movies hitting
          the screens.</p>
      </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <SearchCard
            v-for="movie in recentlyReleasedMovies"
            :key="movie.imdb_id"
            :movie="movie"
            class="scroll-reveal"
            @selected="viewDetail(movie.imdb_id, 'Recently Released Movies')"
            @share="shareMovieFromCard"
        />
      </div>
    </div>
  </div>

  <div v-if="topRatedMovies && topRatedMovies.length" class="bg-white dark:bg-gray-900 py-8">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-base font-semibold text-primary-600 dark:text-primary-500">⭐ Top Rated of All Time</h2>
        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">Watch the highest-rated films as voted by movie
          enthusiasts.</p>
      </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <SearchCard
            v-for="movie in topRatedMovies"
            :key="movie.imdb_id"
            :movie="movie"
            class="scroll-reveal"
            @selected="viewDetail(movie.imdb_id, 'Top Rated Movies')"
            @share="shareMovieFromCard"
        />
      </div>
    </div>
  </div>

    <div v-if="hiddenGemsMovies && hiddenGemsMovies.length" class="bg-white dark:bg-gray-900 py-8">
        <div class="mx-auto max-w-7xl px-4 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-base font-semibold text-primary-600 dark:text-primary-500">💎 Hidden Gems</h2>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">Uncover underrated masterpieces waiting
                    to be
                    explored.</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <SearchCard
                    v-for="movie in hiddenGemsMovies"
                    :key="movie.imdb_id"
                    :movie="movie"
                    class="scroll-reveal"
                    @selected="viewDetail(movie.imdb_id, 'Hidden Gems')"
                    @share="shareMovieFromCard"
                />
            </div>
        </div>
    </div>

  <div v-if="recommendedMovies && recommendedMovies.length" class="bg-white dark:bg-gray-900 py-8">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-base font-semibold text-primary-600 dark:text-primary-500">🎥 Recommended Movies</h2>
        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">Timeless classics and must-watch films for
          every movie lover.</p>
      </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <SearchCard
            v-for="movie in recommendedMovies"
            :key="movie.imdb_id"
            :movie="movie"
            class="scroll-reveal"
            @selected="viewDetail(movie.imdb_id, 'Recommended Movies')"
            @share="shareMovieFromCard"
        />
      </div>
    </div>
  </div>

    <div v-if="popularMovies && popularMovies.length" class="bg-white dark:bg-gray-900 py-8">
        <div class="mx-auto max-w-7xl px-4 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-base font-semibold text-primary-600 dark:text-primary-500">Popular Movies</h2>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">Explore What's Popular Right Now</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <SearchCard
                    v-for="movie in popularMovies"
                    :key="movie.imdb_id"
                    :movie="movie"
                    class="scroll-reveal"
                    @selected="viewDetail(movie.imdb_id, 'Popular Movies')"
                    @share="shareMovieFromCard"
                />
            </div>
        </div>
    </div>

  <NewsletterForm/>

    <Show v-if="selectedIMDBId" :imdbID="selectedIMDBId" @close="selectedIMDBId = null;" @share="shareMovie"/>
</template>
