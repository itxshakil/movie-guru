<script setup>
import {Head} from '@inertiajs/vue3';
import NewsletterForm from '@/Components/NewsletterForm.vue';
import NewsletterWidget from '@/Components/NewsletterWidget.vue';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import DetailCard from '@/Components/DetailCard.vue';
import SearchCard from "@/Components/SearchCard.vue";

const props = defineProps({
    detail: Object,
    sources: {
        type: Array,
        required: false,
        default: () => [],
    },
    recentlyReleasedMovies: Array,
    recommendedMovies: Array,
    affiliateLink: Object,
    similarMovies: Array,
})

defineOptions({ layout: BaseLayout })

const moviePoster = (movie) => {
    return movie.Poster && movie.Poster !== 'N/A' ? movie.Poster : '/assets/images/no-poster.jpg';
};
const pageUrl = window.location.href;

const copyToClipboard = async (text) => {
    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(text);
    } else {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    }
};

const shareMovie = async () => {
    if (!props.detail) return;
    const shareData = {
        title: props.detail.Title,
        text: `Check out ${props.detail.Title} on Movie Guru!`,
        url: route('movie.detail.full', {imdbID: props.detail.imdbID}),
    };

    try {
        if (navigator.share) {
            await navigator.share(shareData);
        } else {
            await copyToClipboard(shareData.url);
            alert('Link copied to clipboard!');
        }
    } catch (err) {
        if (err.name !== 'AbortError') {
            console.error('Error sharing:', err);
        }
    }
};

const shareMovieFromCard = async (movie) => {
    if (typeof navigator !== 'undefined' && 'vibrate' in navigator) {
        navigator.vibrate(10);
    }
    const shareData = {
        title: movie.title,
        text: `Check out ${movie.title} on Movie Guru!`,
        url: route('movie.detail.full', {imdbID: movie.imdb_id}),
    };

    try {
        if (navigator.share) {
            await navigator.share(shareData);
        } else {
            await copyToClipboard(shareData.url);
            alert('Link copied to clipboard!');
        }
    } catch (err) {
        if (err.name !== 'AbortError') {
            console.error('Error sharing:', err);
        }
    }
};

const viewDetail = (imdb_id, sectionName) => {
    if (window.innerWidth < 1024) {
        window.location.href = route('movie.detail.full', {imdbID: imdb_id});
    } else {
        window.open(route('movie.detail.full', {imdbID: imdb_id}), '_blank');
    }
    if (gtag) {
        gtag.trackViewDetail(imdb_id, sectionName);
    }
};

const generateMetaTitle = (detail) => {
  if (!detail || !detail.Title) {
    return 'MovieGuru - Find Your Next Favorite'; // Fallback
  }
  const year = detail.Year ? `(${detail.Year})` : '';

  return `Watch ${detail.Title} ${year} Online: Cast, Plot & Info`;
};

const generateMetaDescription = (detail) => {
  if (!detail || !detail.Title || !detail.Plot) {
    return 'Explore a vast library of movies and series. Find details, ratings, and where to watch your favorites.';
  }
  const year = detail.Year ? `(${detail.Year})` : '';
  const genre = detail.Genre ? `Genre: ${detail.Genre.split(', ')[0]}. ` : '';
  const cast = detail.Actors ? `Starring: ${detail.Actors.split(', ').slice(0, 2).join(', ')}. ` : '';

  const plotSnippet = detail.Plot.substring(0, 100) + (detail.Plot.length > 100 ? '...' : '');

  return `${detail.Title} ${year}: ${genre}${cast}${plotSnippet} Find streaming options and detailed information on MovieGuru.`;
};

const pageTitle = generateMetaTitle(props.detail);
const pageDescription = generateMetaDescription(props.detail);
const ogImage = moviePoster(props.detail);

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

    <DetailCard :affiliate-link="affiliateLink" :detail="detail" :sources="sources"
                @share="shareMovie"
                class="relative isolate px-6 pt-14 lg:px-8 dark:bg-gray-900 dark:text-white"/>

    <div class="mx-auto max-w-7xl px-4 lg:px-8 py-8">
        <NewsletterWidget/>
    </div>

    <div class="mt-4">
        <div v-if="recentlyReleasedMovies && recentlyReleasedMovies.length" class="bg-white dark:bg-gray-900 py-8">
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-base font-semibold text-primary-600 dark:text-primary-500">🎉 Fresh Releases</h2>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">Stay up-to-date with the latest
                        movies hitting
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

        <div v-if="recommendedMovies && recommendedMovies.length" class="bg-white dark:bg-gray-900 py-8">
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-base font-semibold text-primary-600 dark:text-primary-500">🎥 Recommended Movies</h2>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">Timeless classics and must-watch
                        films for
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
    </div>
    <div v-if="similarMovies && similarMovies.length" class="bg-white dark:bg-gray-900 py-8">
        <div class="mx-auto max-w-7xl px-4 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-base font-semibold text-primary-600 dark:text-primary-500">🎥 More Like This</h2>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">Similar movies you might enjoy.</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <SearchCard
                    v-for="movie in similarMovies"
                    :key="movie.imdb_id"
                    :movie="movie"
                    class="scroll-reveal"
                    @selected="viewDetail(movie.imdb_id, 'Similar Movies')"
                    @share="shareMovieFromCard"
                />
            </div>
        </div>
    </div>
    <NewsletterForm/>
</template>
