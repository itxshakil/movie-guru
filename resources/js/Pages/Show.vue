<script setup>
import {inject} from 'vue';
import {Head, router} from '@inertiajs/vue3';
import NewsletterForm from '@/Components/NewsletterForm.vue';
import NewsletterWidget from '@/Components/NewsletterWidget.vue';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import DetailCard from '@/Components/DetailCard.vue';
import SearchCard from "@/Components/SearchCard.vue";
import SectionHeading from '@/Components/Ui/SectionHeading.vue';
import TrailerPlayer from '@/Components/TrailerPlayer.vue';

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
    trailerId: {
        type: String,
        required: false,
        default: null,
    },
})

defineOptions({ layout: BaseLayout })

const gtag = inject('gtag');

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

/** Cards emit `selected` only for a plain tap below 1024px; this page has no quick view, so it navigates. */
const viewDetail = (imdb_id, sectionName) => {
    gtag?.trackViewDetail(imdb_id, sectionName);
    router.visit(route('movie.detail.full', {imdbID: imdb_id}));
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

        <!-- Warms the connection to the poster CDN before the LCP image request is discovered -->
        <link rel="preconnect" href="https://m.media-amazon.com" head-key="preconnect-poster">
        <link rel="dns-prefetch" href="https://m.media-amazon.com" head-key="dns-prefetch-poster">

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

    <div class="mx-auto max-w-7xl pt-6 sm:px-6 sm:pt-10 lg:px-8">
        <DetailCard :affiliate-link="affiliateLink" :detail="detail" :sources="sources" @share="shareMovie"/>
    </div>

    <section v-if="trailerId" class="pt-12 sm:pt-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading title="Watch the trailer"/>
            <TrailerPlayer :title="detail?.Title ?? 'Movie'" :video-id="trailerId" class="max-w-4xl"/>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 pt-12 sm:px-6 sm:pt-16 lg:px-8">
        <NewsletterWidget/>
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
            <SectionHeading subtitle="Timeless classics and must-watch films." title="Recommended"/>
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

    <section v-if="similarMovies && similarMovies.length" class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading subtitle="Similar titles you might enjoy." title="More like this"/>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <SearchCard
                    v-for="movie in similarMovies"
                    :key="movie.imdb_id"
                    :movie="movie"
                    class="scroll-reveal cv-card"
                    @selected="viewDetail(movie.imdb_id, 'Similar Movies')"
                    @share="shareMovieFromCard"
                />
            </div>
        </div>
    </section>

    <NewsletterForm/>
</template>
