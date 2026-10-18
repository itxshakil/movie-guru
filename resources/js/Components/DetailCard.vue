<template>
    <div class="flex min-h-full items-stretch justify-center text-center md:items-center">
        <div class="flex text-left text-base w-full">
            <div
                :class="layout === 'page'
                    ? 'bg-white dark:bg-gray-950 sm:rounded-2xl sm:border sm:border-gray-200 sm:dark:border-gray-800 sm:dark:bg-gray-900'
                    : 'rounded-xl bg-white shadow-2xl dark:bg-gray-950'"
                class="relative flex w-full items-center overflow-hidden dark:text-white">

                <div v-if="detail" itemscope itemtype="https://schema.org/Movie"
                     class="relative grid w-full grid-cols-1 items-start gap-x-6 gap-y-6 sm:grid-cols-12 lg:gap-x-8 p-4 sm:p-6 lg:p-8">
                    <div class="relative overflow-hidden rounded-2xl sm:col-span-4 shadow-xl shadow-black/20">
                        <div class="bg-gray-100 dark:bg-gray-900 aspect-2/3">
                            <img :alt="detail.Title + ' Poster'" itemprop="image"
                                 :src="moviePoster(detail)"
                                 class="object-cover object-center w-full h-full"
                                 fetchpriority="high"
                                 @error="handlePosterError">
                        </div>
                        <span v-if="statusBadge" :class="badgeClass">
                            <svg v-if="statusIcon" aria-hidden="true" class="h-3.5 w-3.5" fill="none"
                                 stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path v-for="path in statusIcon" :key="path" :d="path"/>
                            </svg>
                            {{ statusBadge }}
                        </span>
                    </div>

                    <div class="min-w-0 sm:col-span-8">
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white" itemprop="name"
                            v-text="detail.Title"/>
                        <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                            <span :title="detail.Released ? 'Released on ' + detail.Released : 'Release year'"
                                  itemprop="datePublished" v-text="detail.Year"/>
                            <template v-if="isValue(detail.Genre)">
                                <span aria-hidden="true" class="text-gray-300 dark:text-gray-600">·</span>
                                <span itemprop="genre" v-text="detail.Genre"/>
                            </template>
                            <template v-if="detail.Type !== 'series' && runtime">
                                <span aria-hidden="true" class="text-gray-300 dark:text-gray-600">·</span>
                                <span itemprop="duration" v-text="runtime"/>
                            </template>
                            <template v-if="isValue(detail.Rated) && detail.Rated !== 'Not Rated'">
                                <span aria-hidden="true" class="text-gray-300 dark:text-gray-600">·</span>
                                <span class="rounded-md border border-gray-300 px-1.5 py-0.5 text-[11px] font-semibold text-gray-600 dark:border-gray-600 dark:text-gray-300"
                                      itemprop="contentRating" v-text="detail.Rated"/>
                            </template>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <WatchlistButton
                                :movie="{ imdb_id: detail.imdbID, title: detail.Title, year: detail.Year, poster: detail.Poster, type: detail.Type, imdb_rating: detail.imdbRating }"
                                variant="label"/>
                            <WatchedButton
                                :movie="{ imdb_id: detail.imdbID, title: detail.Title, year: detail.Year, poster: detail.Poster }"
                                variant="label"/>
                            <UiButton size="sm" variant="secondary" @click="shareMovie">
                                <template #icon>
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor"
                                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                                        <path
                                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                    </svg>
                                </template>
                                Share
                            </UiButton>
                            <UiButton :loading="isCreatingPosterCard" size="sm" variant="ghost" @click="sharePosterCard">
                                <template #icon>
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor"
                                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                                        <rect height="18" rx="2" width="18" x="3" y="3"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <path d="M21 15l-5-5L5 21"/>
                                    </svg>
                                </template>
                                {{ posterCardLabel }}
                            </UiButton>
                            <UiButton v-if="upcomingReleaseDate" size="sm" variant="ghost" @click="addReleaseToCalendar">
                                <template #icon>
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor"
                                         stroke-linecap="round" stroke-width="2" viewBox="0 0 24 24">
                                        <rect height="18" rx="2" width="18" x="3" y="4"/>
                                        <path d="M16 2v4M8 2v4M3 10h18"/>
                                    </svg>
                                </template>
                                Add to calendar
                            </UiButton>
                        </div>

                        <div v-if="hasRating" class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1"
                             itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
                            <div class="flex items-center">
                                <svg
                                    v-for="i in 5"
                                    :key="i"
                                    :class="i <= basicRating ? 'text-yellow-500 dark:text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                                    aria-hidden="true" class="h-5 w-5 shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path clip-rule="evenodd"
                                          d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                                          fill-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                <span itemprop="ratingValue">{{ basicRating.toFixed(1) }}</span> out of
                                <span itemprop="bestRating">5</span>
                            </p>
                            <a v-if="isValue(detail.imdbVotes)"
                               :href="'https://www.imdb.com/title/' + detail.imdbID + '/reviews'"
                               class="text-sm text-gray-500 transition-colors hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400"
                               rel="noopener noreferrer"
                               target="_blank">
                                <span class="sr-only" itemprop="ratingCount">{{ detail.imdbVotes.replaceAll(',', '').trim() }}</span>
                                {{ detail.imdbVotes }} reviews
                            </a>
                        </div>
                        <p v-else class="mt-4 text-sm text-gray-500 dark:text-gray-400">No rating yet</p>

                        <section class="mt-6">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Where to watch</h2>
                            <ul class="mt-3 grid auto-rows-fr gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                <li v-if="affiliateLink"
                                    :class="[WATCH_TILE_SHAPE, WATCH_TILE_FEATURED]"
                                    class="sm:col-span-2 xl:col-span-3">
                                    <a
                                        :href="affiliateLink.link"
                                        :class="WATCH_TILE_LINK"
                                        class="group"
                                        rel="noopener noreferrer"
                                        target="_blank"
                                        @click="sendAnalytics('Affiliate: ' + affiliateLink.title, affiliateLink.link)"
                                    >
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-600 text-white">
                                            <svg aria-hidden="true" class="h-6 w-6" fill="none" stroke="currentColor"
                                                 stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                                                <path
                                                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                            </svg>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-base font-semibold text-primary-900 dark:text-primary-100">
                                                Book tickets: {{ affiliateLink.title }}
                                            </span>
                                            <span class="block text-sm text-primary-700 dark:text-primary-300">
                                                Secure your seats for {{ detail.Title }}
                                            </span>
                                        </span>
                                        <svg aria-hidden="true"
                                             class="h-5 w-5 shrink-0 text-primary-500 transition-transform group-hover:translate-x-0.5"
                                             fill="none" stroke="currentColor" stroke-linecap="round"
                                             stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </a>
                                </li>

                                <SourceCard
                                    v-for="source in uniqueSources"
                                    :key="source.availability.sourceId"
                                    :send-analytics="sendAnalytics"
                                    :source="source"
                                />

                                <li v-if="youtubeSearchLink" :class="[WATCH_TILE_SHAPE, WATCH_TILE_NEUTRAL]">
                                    <a
                                        :href="youtubeSearchLink"
                                        :class="WATCH_TILE_LINK"
                                        rel="noopener noreferrer"
                                        target="_blank"
                                        @click="sendAnalytics('Watch on YouTube', youtubeSearchLink)"
                                    >
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-600 text-white">
                                            <svg aria-hidden="true" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                                <path :d="YOUTUBE_ICON_PATH"/>
                                            </svg>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block font-medium text-gray-900 dark:text-white">Watch on YouTube</span>
                                            <span class="block text-sm text-gray-500 dark:text-gray-400">Search on YouTube</span>
                                        </span>
                                    </a>
                                </li>

                                <li :class="[WATCH_TILE_SHAPE, WATCH_TILE_NEUTRAL]">
                                    <a
                                        :href="searchFallbackLink.url"
                                        :class="WATCH_TILE_LINK"
                                        rel="noopener noreferrer"
                                        target="_blank"
                                        @click="sendAnalytics(searchFallbackLink.platform + ' Search', searchFallbackLink.url)"
                                    >
                                        <span v-if="searchFallbackLink.platform === 'YouTube'"
                                              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-600 text-white">
                                            <svg aria-hidden="true" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                                <path :d="YOUTUBE_ICON_PATH"/>
                                            </svg>
                                        </span>
                                        <img v-else alt="Google Logo" class="h-10 w-10 shrink-0 rounded-lg"
                                             src="/assets/google-logo.png"/>
                                        <span class="min-w-0">
                                            <span class="block font-medium text-gray-900 dark:text-white">
                                                {{ searchFallbackLink.platform }} Search
                                            </span>
                                            <span class="block text-sm text-gray-500 dark:text-gray-400">
                                                Search on {{ searchFallbackLink.platform }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </section>

                        <SnippetText :text="isValue(detail.Plot) ? detail.Plot : 'No plot details yet.'"
                                     class="mt-6 text-base leading-relaxed text-gray-700 dark:text-gray-300"
                                     clamp-class="line-clamp-3 md:line-clamp-none"
                                     itemprop="description"/>

                        <section v-if="detail.Ratings?.length" class="mt-6">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Ratings</h2>
                            <ul class="mt-3 grid grid-cols-3 gap-2">
                                <li v-for="rating in detail.Ratings" :key="rating.Source"
                                    class="rounded-xl border border-gray-200 p-3 text-center dark:border-gray-800">
                                    <p class="text-xl font-bold text-gray-900 dark:text-white" v-text="rating.Value"/>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400"
                                       v-text="ratingSourceLabel(rating.Source)"/>
                                </li>
                            </ul>
                        </section>

                        <section class="mt-6">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Details</h2>
                            <dl class="mt-3 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-[8rem_1fr]">
                                <div v-if="isValue(detail.Director)" class="sm:contents">
                                    <dt class="text-gray-500 dark:text-gray-400">Director</dt>
                                    <dd class="text-gray-900 dark:text-gray-100" itemprop="director" itemscope
                                        itemtype="https://schema.org/Person">
                                        <span itemprop="name" v-text="detail.Director"/>
                                    </dd>
                                </div>
                                <div v-if="isValue(detail.Writer)" class="sm:contents">
                                    <dt class="text-gray-500 dark:text-gray-400">Writer</dt>
                                    <dd class="text-gray-900 dark:text-gray-100" itemprop="writer" itemscope
                                        itemtype="https://schema.org/Person">
                                        <span itemprop="name" v-text="detail.Writer"/>
                                    </dd>
                                </div>
                                <div v-if="isValue(detail.Actors)" class="sm:contents">
                                    <dt class="text-gray-500 dark:text-gray-400">Cast</dt>
                                    <dd class="text-gray-900 dark:text-gray-100" itemprop="actor" itemscope
                                        itemtype="https://schema.org/Person">
                                        <span itemprop="name" v-text="detail.Actors"/>
                                    </dd>
                                </div>
                                <div v-if="detail.Type === 'series' && isValue(detail.totalSeasons)" class="sm:contents">
                                    <dt class="text-gray-500 dark:text-gray-400">Seasons</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">
                                        {{ detail.totalSeasons }} {{ detail.totalSeasons === '1' ? 'season' : 'seasons' }}<template v-if="runtime"> - <span itemprop="duration">{{ runtime }}</span> an episode</template>
                                    </dd>
                                </div>
                                <div v-if="isValue(detail.Awards)" class="sm:contents">
                                    <dt class="text-gray-500 dark:text-gray-400">Awards</dt>
                                    <dd class="text-gray-900 dark:text-gray-100" v-text="detail.Awards"/>
                                </div>
                                <div v-if="isValue(detail.Country)" class="sm:contents">
                                    <dt class="text-gray-500 dark:text-gray-400">Country</dt>
                                    <dd class="text-gray-900 dark:text-gray-100" v-text="detail.Country"/>
                                </div>
                                <div v-if="isValue(detail.BoxOffice)" class="sm:contents">
                                    <dt class="text-gray-500 dark:text-gray-400">Box office</dt>
                                    <dd class="text-gray-900 dark:text-gray-100" v-text="detail.BoxOffice"/>
                                </div>
                                <div v-if="isValue(detail.Production)" class="sm:contents">
                                    <dt class="text-gray-500 dark:text-gray-400">Production</dt>
                                    <dd class="text-gray-900 dark:text-gray-100" v-text="detail.Production"/>
                                </div>
                            </dl>
                        </section>
                    </div>
                </div>

                <DetailSkeletonCard v-else :title="title"/>
            </div>
        </div>
    </div>

    <Teleport to="body">
        <div
            v-if="affiliateLink"
            class="booking-bar fixed inset-x-0 bottom-(--tab-bar-height) z-50 flex h-(--booking-bar-height) items-center border-t border-primary-200 bg-white/95 px-3 shadow-2xl backdrop-blur-sm md:hidden dark:border-primary-800 dark:bg-gray-900/95"
        >
            <UiButton
                :href="affiliateLink.link"
                block
                external
                rel="noopener noreferrer"
                size="lg"
                target="_blank"
                @click="sendAnalytics('Affiliate Sticky: ' + affiliateLink.title, affiliateLink.link)"
            >
                <template #icon>
                    <svg aria-hidden="true" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    </svg>
                </template>
                <span class="truncate">Book tickets: {{ affiliateLink.title }}</span>
            </UiButton>
        </div>
    </Teleport>
</template>
<script setup>
import DetailSkeletonCard from '@/Components/DetailSkeletonCard.vue';
import {computed, inject, ref} from 'vue';
import SnippetText from '@/Components/SnippetText.vue';
import SourceCard from "@/Components/SourceCard.vue";
import WatchlistButton from "@/Components/WatchlistButton.vue";
import WatchedButton from "@/Components/WatchedButton.vue";
import UiButton from '@/Components/Ui/UiButton.vue';
import {createPosterCard} from '@/Composables/usePosterCard.js';
import {createCalendarFile} from '@/Composables/useCalendarEvent.js';
import {shareOrDownloadFile} from '@/Composables/useFileSharing.js';
import {movieStatusBadge, ratingSourceLabel} from '@/Composables/movieStatus.js';
import {WATCH_TILE_FEATURED, WATCH_TILE_LINK, WATCH_TILE_NEUTRAL, WATCH_TILE_SHAPE} from '@/Components/watchTileClasses.js';

const gtag = inject('gtag');

const props = defineProps({
    detail: Object,
    sources: {
        type: Array,
        required: false,
        default: () => [],
    },
    title: {
    type: String,
    required: false,
    },
    affiliateLink: {
        type: Object,
        required: false,
        default: null,
    },
    /** "page" sits flush on phones and on the shared card surface from sm up (the full detail page); "sheet" keeps its card look (the quick-view sheet). */
    layout: {
        type: String,
        default: 'sheet',
    },
});

const emit = defineEmits(['share']);

const uniqueSources = computed(() => {
    const sourceMap = new Map();

    if (!Array.isArray(props.sources)) {
        return [];
    }

    props.sources.forEach((source) => {
        if (!source || !source.meta || !source.meta.name) {
            return; // skip invalid entries
        }

        const name = source.meta.name;
        const format = source?.availability?.format ?? null;

        if (sourceMap.has(name)) {
            const currentSource = sourceMap.get(name);
            if (format) {
                currentSource.formats = Array.isArray(currentSource.formats)
                    ? [...new Set([...currentSource.formats, format])]
                    : [format];
            }
        } else {
            sourceMap.set(name, {
                ...source,
                formats: format ? [format] : [],
            });
        }
    });

    return Array.from(sourceMap.values());
});


const associateTrackingID = "itxshakil0ec-21";
const netflixLink = computed(() => props.detail ? `https://www.netflix.com/search?q=${encodeURIComponent(props.detail.Title)}` : '');
const amazonAffiliateLink = computed(() => props.detail ? `https://primevideo.com?tag=${associateTrackingID}&searchTerm=${encodeURIComponent(props.detail.Title)}` : '');
const searchFallbackLink = computed(() => {
    if (!props.detail?.imdbID) {
        return {url: '', platform: 'Google'};
    }
    const hash = props.detail.imdbID.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    const isYouTube = hash % 10 < 3;
    if (isYouTube) {
        return {
            url: `https://www.youtube.com/results?search_query=${encodeURIComponent(props.detail.Title + ' ' + (props.detail.Year ?? '') + ' full movie')}`,
            platform: 'YouTube',
        };
    }
    return {
        url: `https://www.google.com/search?q=${encodeURIComponent(props.detail.Title + ' ' + (props.detail.Year ?? '') + ' full movie watch online')}`,
        platform: 'Google',
    };
});

const youtubeSearchLink = computed(() => {
    if (!props.detail?.imdbID) {
        return null;
    }
    const currentYear = new Date().getFullYear();
    const movieYear = parseInt(props.detail.Year);
    if (isNaN(movieYear) || movieYear >= currentYear) {
        return null;
    }
    const hash = props.detail.imdbID.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    if (hash % 3 !== 0) {
        return null;
    }
    return `https://www.youtube.com/results?search_query=${encodeURIComponent(props.detail.Title + ' ' + (props.detail.Year ?? '') + ' full movie')}`;
});

const isValue = function(value) {
    return value && value !== 'N/A';
};

const hasRating = computed(() => isValue(props.detail.imdbRating));

const basicRating = computed(() => {
  if (isValue(props.detail.imdbRating)) {
    let mappedRating = (props.detail.imdbRating / 2).toFixed(1);

    mappedRating = Math.max(.1, Math.min(5, mappedRating));

    return mappedRating;
  }

  return null;
});

/** The first status that applies, shown as a badge on the poster. */
const statusBadge = computed(() => movieStatusBadge(props.detail));

const badgeClass = 'absolute bottom-3 left-3 inline-flex items-center gap-1 rounded-full bg-black/60 px-2.5 py-1 text-xs '
    + 'font-semibold text-white backdrop-blur-md';

const TAG_STAR_ICON = [
    'M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0',
    'M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z',
    'M12.5 13.847l-1.5 1.153l.532 -1.857l-1.532 -1.143h1.902l.598 -1.8l.598 1.8h1.902l-1.532 1.143l.532 1.857z',
];

/** Icon paths per poster status; New has no icon. */
const STATUS_ICONS = {
    'Trending': ['M3 17l6 -6l4 4l8 -8', 'M14 7l7 0l0 7'],
    'New': null,
    'Top rated': TAG_STAR_ICON,
    'Hidden gem': TAG_STAR_ICON,
};

const statusIcon = computed(() => STATUS_ICONS[statusBadge.value] ?? null);

const YOUTUBE_ICON_PATH = 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z';

const runtime = computed(() => {
    const totalMinutes = parseInt(props.detail.Runtime);
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    let result = '';

    if (hours > 0) {
        result += hours + 'h';
    }

    if (minutes > 0) {
        if (result !== '') {
            result += ' ';
        }
        result += minutes + 'm';
    }

    return result;
});

const moviePoster = (movie) => {
    return movie.Poster && movie.Poster !== 'N/A' ? movie.Poster : '/assets/images/no-poster.jpg';
};

const handlePosterError = (event) => {
    event.target.src = '/assets/images/no-poster.jpg';
    event.target.classList.add('opacity-40', 'grayscale');
};

function sendAnalytics(platform, link) {
    if (gtag) {
        gtag.trackExternalClick(platform, link);
  }
}

const shareMovie = async () => {
    emit('share');
};

const detailUrl = computed(() => route('movie.detail.full', {imdbID: props.detail.imdbID}));

const isCreatingPosterCard = ref(false);
// Kept when rendering took longer than the tap's user activation, so the next tap shares instantly.
const preparedPosterCard = ref(null);

const posterCardLabel = computed(() => {
    if (isCreatingPosterCard.value) {
        return 'Creating card...';
    }
    return preparedPosterCard.value ? 'Card ready - tap to share' : 'Share as image';
});

const sharePosterCard = async () => {
    const shareDetails = {
        title: props.detail.Title,
        text: `${props.detail.Title} via Movie Guru ${detailUrl.value}`,
    };

    try {
        let file = preparedPosterCard.value;
        if (!file) {
            isCreatingPosterCard.value = true;
            file = await createPosterCard(props.detail);
            isCreatingPosterCard.value = false;
        }

        const outcome = await shareOrDownloadFile(file, shareDetails);
        preparedPosterCard.value = outcome === 'needs-activation' ? file : null;
    } catch (error) {
        console.error('Sharing the poster card failed', error);
        preparedPosterCard.value = null;
    } finally {
        isCreatingPosterCard.value = false;
    }
};

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

/**
 * OMDb release dates look like "17 Oct 2026"; parsed by hand because Date parsing of that format is not portable.
 */
const upcomingReleaseDate = computed(() => {
    const match = /^(\d{1,2}) ([A-Za-z]{3}) (\d{4})$/.exec(props.detail?.Released ?? '');
    const monthIndex = match ? MONTHS.indexOf(match[2]) : -1;
    if (monthIndex === -1) {
        return null;
    }

    const releaseDate = new Date(Number(match[3]), monthIndex, Number(match[1]));
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return releaseDate >= today ? releaseDate : null;
});

const addReleaseToCalendar = async () => {
    const file = createCalendarFile({
        title: `🎬 ${props.detail.Title} releases`,
        description: `${props.detail.Title} is out today. Details and where to watch: ${detailUrl.value}`,
        url: detailUrl.value,
        start: upcomingReleaseDate.value,
        allDay: true,
    });

    try {
        await shareOrDownloadFile(file, {title: `${props.detail.Title} release`});
    } catch (error) {
        console.error('Adding the release to the calendar failed', error);
    }
};

</script>
