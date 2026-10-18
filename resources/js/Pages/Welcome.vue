<script setup>
import {Head, useForm} from '@inertiajs/vue3';
import NewsletterForm from '@/Components/NewsletterForm.vue';
import OurFeatures from '@/Components/OurFeatures.vue';
import HiddenGem from '@/Components/HiddenGem.vue';
import VoiceSearchButton from '@/Components/VoiceSearchButton.vue';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import UiButton from '@/Components/Ui/UiButton.vue';
import UiChip from '@/Components/Ui/UiChip.vue';
import SectionHeading from '@/Components/Ui/SectionHeading.vue';
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
    mostWatchedMovies: Array,
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

const searchByVoice = (transcript) => {
    form.s = transcript;
    doSearch();
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

const viewDetail = (imdb_id, sectionName) => {
    selectedIMDBId.value = imdb_id;
    gtag?.trackViewDetail(imdb_id, sectionName);
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
    <section class="px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="mx-auto flex max-w-3xl flex-col items-center text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-balance text-gray-900 sm:text-6xl dark:text-white">
                Discover. Explore. Enjoy.
            </h1>
            <p class="mt-5 max-w-xl text-lg text-pretty text-gray-600 sm:text-xl dark:text-gray-400">
                Dive into a world of movies and series. Find your next favourite today.
            </p>

            <form class="mt-10 w-full max-w-2xl" role="search" @submit.prevent="doSearch">
                <label class="sr-only" for="search-input">Search movies and series</label>
                <div
                    class="flex items-center gap-2 rounded-full border border-gray-300 bg-white p-1.5 pl-5 shadow-lg shadow-gray-900/5 transition-colors focus-within:border-primary-500 focus-within:ring-4 focus-within:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-900 dark:shadow-black/30"
                >
                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor"
                         stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <input
                        id="search-input"
                        v-model="form.s"
                        autocomplete="off"
                        autofocus
                        class="min-w-0 flex-1 border-0 bg-transparent px-1 py-2.5 text-base text-gray-900 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:ring-0 focus:outline-none sm:text-lg dark:text-white [&::-webkit-search-cancel-button]:appearance-none"
                        enterkeyhint="search"
                        name="s"
                        placeholder="Search movies and series..."
                        required
                        type="search"
                    />
                    <VoiceSearchButton v-if="!form.s" class="shrink-0" @result="searchByVoice"/>
                    <UiButton v-else aria-label="Clear search" size="icon" variant="ghost" @click="clearSearch">
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </UiButton>
                    <UiButton :loading="form.processing" class="shrink-0 rounded-full! max-sm:px-3!" size="lg" type="submit">
                        <span class="hidden sm:inline">Explore</span>
                        <svg v-if="!form.processing" aria-hidden="true" class="h-5 w-5 sm:hidden" fill="none"
                             stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="sr-only sm:hidden">Search</span>
                    </UiButton>
                </div>
            </form>

            <div v-if="shuffledTrendingQueries.length" class="mt-6 flex flex-wrap items-center justify-center gap-2">
                <span class="mr-1 text-xs font-medium text-gray-500 dark:text-gray-400">Hot searches</span>
                <UiChip
                    v-for="query in shuffledTrendingQueries.slice(0, 5)"
                    :key="query"
                    @click="form.s = query; doSearch()"
                >{{ query }}</UiChip>
            </div>
        </div>
    </section>
    <HiddenGem/>
    <OurFeatures/>

    <section v-if="mostWatchedMovies && mostWatchedMovies.length" class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading subtitle="What everyone has been watching lately." title="Most watched this week"/>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <SearchCard
                    v-for="movie in mostWatchedMovies"
                    :key="movie.imdb_id"
                    :movie="movie"
                    class="scroll-reveal"
                    @selected="viewDetail(movie.imdb_id, 'Most Watched This Week')"
                    @share="shareMovieFromCard"
                />
            </div>
        </div>
    </section>

    <section v-if="trendingMovies && trendingMovies.length" class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading subtitle="What people are watching right now." title="Trending now"/>
            <ul class="trending-carousel flex gap-6 overflow-x-auto pb-4 -mx-4 px-4 lg:-mx-8 lg:px-8">
                <li v-for="movie in trendingMovies" :key="movie.imdb_id" class="trending-carousel__item w-72 shrink-0">
                    <SearchCard
                        :movie="movie"
                        class="scroll-reveal"
                        @selected="viewDetail(movie.imdb_id, 'Trending Movies')"
                        @share="shareMovieFromCard"
                    />
                </li>
            </ul>
        </div>
    </section>

    <section v-if="recentlyReleasedMovies && recentlyReleasedMovies.length" class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading subtitle="The latest movies to hit the screens." title="Fresh releases"/>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
    </section>

    <section v-if="topRatedMovies && topRatedMovies.length" class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading subtitle="The highest rated films, voted by movie fans." title="Top rated of all time"/>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
    </section>

    <section v-if="hiddenGemsMovies && hiddenGemsMovies.length" class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading subtitle="Underrated films worth finding." title="Hidden gems"/>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
    </section>

    <section v-if="recommendedMovies && recommendedMovies.length" class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading subtitle="Timeless classics and must-watch films." title="Recommended"/>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
    </section>

    <section v-if="popularMovies && popularMovies.length" class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading subtitle="What is popular this week." title="Popular right now"/>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
    </section>

    <NewsletterForm/>

    <Show v-if="selectedIMDBId" :imdbID="selectedIMDBId" @close="selectedIMDBId = null;" @share="shareMovie"/>
</template>

<style scoped>
.trending-carousel {
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}

.trending-carousel::-webkit-scrollbar {
    display: none;
}

.trending-carousel__item {
    scroll-snap-align: start;
}

/* Native CSS Carousel controls (scroll markers + prev/next buttons) as a
   pure progressive enhancement. Feature-detected via selector() so
   unsupporting browsers skip the whole block cleanly rather than getting a
   partial/broken result - the plain scroll-snap carousel above already
   works fully via touch/trackpad/scrollbar without this. */
@supports selector(::scroll-marker) {
    .trending-carousel {
        scroll-marker-group: after;
    }

    .trending-carousel__item::scroll-marker {
        content: '';
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 999px;
        background: var(--color-gray-300);
    }

    .trending-carousel__item::scroll-marker:target-current {
        background: var(--color-primary-500);
    }

    .trending-carousel::scroll-button(inline-start) {
        content: '‹';
    }

    .trending-carousel::scroll-button(inline-end) {
        content: '›';
    }
}

@media (prefers-reduced-motion: reduce) {
    .trending-carousel {
        scroll-behavior: auto;
    }
}
</style>
