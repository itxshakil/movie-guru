<template>
    <article
        class="search-card group relative isolate flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white transition-colors hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700"
        itemprop="mainEntity" itemscope itemtype="https://schema.org/Movie"
        @pointerdown="warmDetail($event, movie.imdb_id)"
        @pointerup="warmDetailOnTap($event, movie.imdb_id)"
        @click.capture="onDetailLinkClick"
    >
        <div class="relative aspect-2/3 w-full overflow-hidden bg-gray-100 dark:bg-gray-800">
            <img
                :alt="movie.title + ' poster'"
                :src="moviePoster(movie)"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                itemprop="image"
                loading="lazy"
                @error="handlePosterError"
            >
        </div>

        <div class="search-card__body flex flex-1 flex-col gap-2 p-4">
            <span v-if="isValue(movie.type)" class="self-start rounded-full bg-primary-50 px-2 py-0.5 text-[11px] font-semibold capitalize text-primary-700 dark:bg-primary-950/60 dark:text-primary-300">
                {{ movie.type }}
            </span>
            <h3 class="text-base leading-snug font-semibold text-gray-900 dark:text-white">
                <Link
                    :href="route('movie.detail.full', {imdbID: movie.imdb_id})"
                    class="card-link line-clamp-2"
                    itemprop="url"
                ><span itemprop="name">{{ movie.title }}</span></Link>
            </h3>
            <!-- After the title in the DOM so Tab reaches the card link first; positioned over the poster's corner. -->
            <div class="absolute top-2 right-2 flex gap-2">
                <WatchlistButton :movie="movie" variant="icon"/>
                <WatchedButton :movie="movie" variant="icon"/>
            </div>
            <p v-if="releaseYear || Number(movie.imdb_rating)" class="flex flex-wrap items-center gap-x-1.5 text-xs text-gray-500 dark:text-gray-400">
                <span v-if="releaseYear" itemprop="datePublished">{{ releaseYear }}</span>
                <span v-if="releaseYear && Number(movie.imdb_rating)" aria-hidden="true">·</span>
                <span v-if="Number(movie.imdb_rating)" class="inline-flex items-center gap-1 font-medium text-gray-700 dark:text-gray-300">
                    <svg aria-hidden="true" class="h-3.5 w-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"/>
                    </svg>
                    <span class="tabular-nums">{{ (movie.imdb_rating / 2).toFixed(1) }}</span>
                    <span class="sr-only">out of 5</span>
                </span>
            </p>
            <p v-if="isValue(movie.director)" class="truncate text-xs text-gray-500 dark:text-gray-400" itemprop="director" itemscope itemtype="https://schema.org/Person">
                Dir. <span class="text-gray-700 dark:text-gray-300" itemprop="name">{{ movie.director }}</span>
            </p>
            <div v-if="genres.length" class="flex flex-wrap gap-1.5">
                <UiChip v-for="genre in genres" :key="genre" as="span" size="sm">{{ genre }}</UiChip>
            </div>

            <div class="relative z-10 mt-auto flex gap-2 pt-2">
                <UiButton class="flex-1" size="sm" variant="secondary" @click.stop="searchWatchDownload(movie.title)">
                    Where to watch
                </UiButton>
                <UiButton :href="route('movie.detail.full', {imdbID: movie.imdb_id})" class="flex-1" size="sm">
                    Details
                    <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M5 12h14m-7-7 7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </UiButton>
            </div>
        </div>
    </article>
</template>
<script setup>
import {computed, inject} from "vue";
import {Link} from '@inertiajs/vue3';
import WatchlistButton from "@/Components/WatchlistButton.vue";
import WatchedButton from "@/Components/WatchedButton.vue";
import UiButton from '@/Components/Ui/UiButton.vue';
import UiChip from '@/Components/Ui/UiChip.vue';
import {prefetchDetail} from '@/Composables/useDetailPrefetch.js';
import {shouldOpenQuickView} from '@/Components/cardClicks.js';

const props = defineProps({
    movie: Object
})
const emit = defineEmits(['selected', 'share']);

const gtag = inject("gtag");

const isValue = (value) => Boolean(value) && value !== 'N/A';

const releaseYear = computed(() => {
    const value = isValue(props.movie.year) ? props.movie.year : props.movie.release_date;
    return isValue(value) ? String(value).slice(0, 4) : '';
});

const genres = computed(() => isValue(props.movie.genre)
    ? props.movie.genre.split(',').map((genre) => genre.trim()).filter(Boolean).slice(0, 2)
    : []);

const openQuickView = () => {
    navigator.vibrate?.(10);
    if (document.startViewTransition) {
        document.startViewTransition(() => emit('selected'));
    } else {
        emit('selected');
    }
};

/**
 * The title link and the Details button are real links to the detail page. Below 1024px a plain
 * click on either opens the quick-view sheet instead; Inertia's Link drops @click listeners, so
 * this runs in the capture phase on the card and prevents the default before the Link sees it.
 */
const onDetailLinkClick = (event) => {
    if (event.target.closest('a') && shouldOpenQuickView(event, window.innerWidth)) {
        event.preventDefault();
        openQuickView();
    }
};

// Below 1024px a plain click opens the quick-view modal, so its data can start loading before the
// click event. Every prefetch is recorded as a view, so it must only start when a click is
// certain: on press for a mouse or pen, and on release for touch, because a scroll also starts
// with a touch press but ends in pointercancel instead of pointerup.
const shouldWarmDetail = (event, imdbID) => {
    return shouldOpenQuickView(event, window.innerWidth) && imdbID && !event.target.closest('button');
};

const warmDetail = (event, imdbID) => {
    if (event.pointerType !== 'touch' && shouldWarmDetail(event, imdbID)) {
        prefetchDetail(imdbID);
    }
};

const warmDetailOnTap = (event, imdbID) => {
    if (event.pointerType === 'touch' && shouldWarmDetail(event, imdbID)) {
        prefetchDetail(imdbID);
    }
};

const moviePoster = (movie)=>{
  return movie.poster && movie.poster !== 'N/A' ? movie.poster : '/assets/images/no-poster.jpg';
}

const handlePosterError = (event) => {
    event.target.src = '/assets/images/no-poster.jpg';
    event.target.classList.add('opacity-40', 'grayscale');
};

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

<style scoped>
/* The title link stretches over the whole card, so the card is one link without nesting the
   icon and action buttons inside an <a>; those sit above it with z-index. */
.card-link::after {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 0;
    border-radius: inherit;
}

/* The ring goes on the whole card instead of the default :focus-visible outline on the title link. */
.card-link:focus-visible {
    outline: none;
}

.search-card:has(.card-link:focus-visible) {
    outline: 2px solid var(--color-primary-500);
    outline-offset: 2px;
}

/* Denser grids (style="--card-density: compact" on the grid) keep the structure, with tighter padding. */
@container style(--card-density: compact) {
    .search-card__body {
        padding: 0.75rem;
    }
}
</style>
