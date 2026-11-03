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
import {shareMovie, shareTitle} from '@/Composables/shareTitle.js';

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
    // Built on the server by PageMeta so crawlers and link previews get the same tags without running JS.
    meta: Object,
})

defineOptions({ layout: BaseLayout })

const gtag = inject('gtag');

// The canonical URL is the one the server already gives link previews a proper card for.
const shareThisTitle = () => {
    if (!props.detail) return;
    shareTitle({title: props.detail.Title, url: props.meta.canonical});
};

/** Cards emit `selected` only for a plain tap below 1024px; this page has no quick view, so it navigates. */
const viewDetail = (imdb_id, sectionName) => {
    gtag?.trackViewDetail(imdb_id, sectionName);
    router.visit(route('movie.detail.full', {imdbID: imdb_id}));
};

</script>

<template>
    <Head>
        <title>{{ meta.title }}</title>

        <!-- Warms the connection to the poster CDN before the LCP image request is discovered -->
        <link rel="preconnect" href="https://m.media-amazon.com" head-key="preconnect-poster">
        <link rel="dns-prefetch" href="https://m.media-amazon.com" head-key="dns-prefetch-poster">

        <meta :content="meta.description" head-key="description" name="description"/>
        <meta property="subject" :content="meta.title" head-key="subject">

        <meta property="og:url" :content="meta.canonical" head-key="og:url">
        <meta property="og:type" content="website">
        <meta property="og:title" :content="meta.title" head-key="og:title">
        <meta property="og:description" :content="meta.description" head-key="og:description">
        <meta property="og:image" :content="meta.image" head-key="og:image">

        <!-- Twitter Meta Tags -->
        <meta name="twitter:card" content="summary_large_image" head-key="twitter:card">
        <meta property="twitter:domain" content="movieguru.shakiltech.com">
        <meta property="twitter:url" :content="meta.canonical" head-key="twitter:url">
        <meta name="twitter:title" :content="meta.title" head-key="twitter:title">
        <meta name="twitter:description" :content="meta.description" head-key="twitter:description">
        <meta name="twitter:image" :content="meta.image" head-key="twitter:image">

        <link rel="canonical" :href="meta.canonical" head-key="canonical" />
    </Head>

    <div class="mx-auto max-w-7xl pt-6 sm:px-6 sm:pt-10 lg:px-8">
        <DetailCard :affiliate-link="affiliateLink" :detail="detail" :sources="sources" layout="page"
                    @share="shareThisTitle"/>
    </div>

    <section v-if="trailerId" class="pt-12 sm:pt-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading title="Watch the trailer"/>
            <TrailerPlayer :title="detail?.Title ?? 'Movie'" :video-id="trailerId" class="max-w-4xl"/>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
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
                    @share="shareMovie"
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
                    @share="shareMovie"
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
                    @share="shareMovie"
                />
            </div>
        </div>
    </section>

    <NewsletterForm/>
</template>
