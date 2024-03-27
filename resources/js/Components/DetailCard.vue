<template>
    <div class="flex min-h-full items-stretch justify-center text-center md:items-center">
        <div class="flex text-left text-base w-full">
            <div
                class="relative rounded-lg flex w-full items-center overflow-hidden bg-white dark:bg-gray-900 dark:text-white px-4 pb-6 pt-6 shadow-2xl sm:px-6 sm:pt-8 md:p-6 lg:p-8">
                <div v-if="detail"
                     class="grid w-full grid-cols-1 items-start gap-x-3 gap-y-4 sm:grid-cols-12 lg:gap-x-8">
                    <div class="relative overflow-hidden rounded-lg sm:col-span-4 lg:col-span-5">
                        <div class="aspect-h-3 aspect-w-2 bg-gray-100 dark:bg-gray-900">
                            <img :alt="detail.Title + ' Poster'"
                                 :src="moviePoster(detail)"
                                 class="object-cover object-center">
                        </div>
                        <div class="flex flex-wrap gap-1 absolute top-0 p-1 justify-end w-full">
                <span v-if="trending"
                      class="flex items-center gap-1 bg-orange-100 text-orange-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-orange-900 dark:text-orange-300">
                    Trending
                    <svg class="h-4 w-4" fill="none" height="24" stroke="currentColor"
                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                         width="24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path d="M3 17l6 -6l4 4l8 -8"/>
                        <path d="M14 7l7 0l0 7"/>
                    </svg>
                </span>
                            <span v-else-if="recentlyReleased"
                                  class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-purple-900 dark:text-purple-300">New</span>
                            <span v-else-if="topRated"
                                  class="flex items-center gap-1 bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-primary-900 dark:text-primary-300">
                        <svg class="w-4 h-4" fill="none" height="24" stroke="currentColor"
                             stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                             width="24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 0h24v24H0z"
                                  fill="none"
                                  stroke="none"/>
                            <path
                                d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
                            <path
                                d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z"/><path
                            d="M12.5 13.847l-1.5 1.153l.532 -1.857l-1.532 -1.143h1.902l.598 -1.8l.598 1.8h1.902l-1.532 1.143l.532 1.857z"/></svg>
                                    Top Rated
                                </span>
                            <span v-else-if="hiddenGems"
                                  class="flex items-center gap-1 bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-primary-900 dark:text-primary-300">
                                    <svg class="w-4 h-4" fill="none" height="24" stroke="currentColor"
                                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         viewBox="0 0 24 24"
                                         width="24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z"
                                              fill="none"
                                              stroke="none"/>
                                        <path
                                            d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
                                        <path
                                            d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z"/><path
                                        d="M12.5 13.847l-1.5 1.153l.532 -1.857l-1.532 -1.143h1.902l.598 -1.8l.598 1.8h1.902l-1.532 1.143l.532 1.857z"/></svg>
                                    Hidden Gem
                                </span>
                        </div>
                    </div>
                    <div class="sm:col-span-8 lg:col-span-7">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white sm:pr-12"
                            v-text="detail.Title"></h2>
                        <span :title="detail.Released ? 'Released on '+ detail.Released : 'Release Year'"
                              class="mt-2 pr-2 text-sm text-gray-500 dark:text-gray-400"
                              v-text="detail.Year"></span>
                        <span class="mt-2 pr-2 text-sm text-gray-500 dark:text-gray-400" title="Genre"
                              v-text="Genre"></span>
                        <span v-if="detail.Type !== 'series'" class="mt-2 pr-2 text-sm text-gray-500 dark:text-gray-400"
                              title="Runtime" v-text="runtime"></span>
                        <span v-if="isValue(detail.Rated) && detail.Rated !== 'Not Rated'"
                              class="mt-2 pr-2 text-sm text-gray-500 dark:text-gray-400" title="Rated"
                              v-text="detail.Rated"></span>

                        <section aria-labelledby="information-heading" class="mt-2">
                            <h3 id="information-heading" class="sr-only">Product information</h3>
                            <div class="flex items-center">
                                <!-- Active: "text-gray-900", Default: "text-gray-200" -->
                                <svg
                                    v-for="i in 5"
                                    :key="i"
                                    :class="i <= basicRating ? 'text-yellow-500 dark:text-yellow-400' : 'text-gray-500 dark:text-gray-400'"
                                    aria-hidden="true" class="h-5 w-5 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path clip-rule="evenodd"
                                          d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                                          fill-rule="evenodd"/>
                                </svg>

                                <p class="sr-only">{{ basicRating }} out of 5 stars</p>
                                <!--                                TODO: Redirect link to imdbID to rating page -->
                                <a class="ml-3 text-sm font-medium text-primary-600 hover:text-primary-500" href="#">
                                    {{ isValue(detail.imdbVotes) ?detail.imdbVotes : 'No '  }} reviews</a>
                            </div>
                            <SnippetText :text="isValue(detail.Plot) ? detail.Plot : 'No Plot detail'" class="text-gray-900 dark:text-white mt-2"/>

                            <div>
                                <div class="py-4 pb-2">
                                    <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-white capitalize">
                                        {{ detail.Type }} Information</h3>
                                    <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500 hidden">Additional detail
                                        and information</p>
                                </div>
                                <div>
                                    <dl class="">
                                        <div
                                            v-if="isValue(detail.Actors)"
                                            class="p-2 sm:py-4 sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Actor
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0"
                                                v-text="detail.Actors"></dd>
                                        </div>
                                        <div
                                            v-if="isValue(detail.Writer)"
                                            class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Writer
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0"
                                                v-text="detail.Writer"></dd>
                                        </div>
                                        <div
                                            v-if="isValue(detail.Director)"
                                            class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Director
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0"
                                                v-text="detail.Director"></dd>
                                        </div>
                                        <div v-if="detail.Type === 'series' && isValue(detail.totalSeasons)"
                                             class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">No.
                                                of Seasons
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0">
                                                {{ detail.totalSeasons }} Seasons - {{ runtime }}
                                            </dd>
                                        </div>
                                        <div
                                            v-if="isValue(detail.Awards)"
                                            class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Awards
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0"
                                                v-text="detail.Awards"></dd>
                                        </div>
                                        <div
                                            v-if="isValue(detail.Country)"
                                            class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Country
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0"
                                                v-text="detail.Country"></dd>
                                        </div>
                                        <div
                                            v-if="isValue(detail.BoxOffice)"
                                            class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Box
                                                Office
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0"
                                                v-text="detail.BoxOffice"></dd>
                                        </div>
                                        <div
                                            v-if="isValue(detail.Production)"
                                            class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Production
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0"
                                                v-text="detail.Production"></dd>
                                        </div>
                                        <div v-if="detail.Ratings.length > 1"
                                             class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Ratings
                                            </dt>
                                            <dd class="mt-2 text-sm text-gray-900 sm:col-span-3 sm:mt-0">
                                                <ul class="divide-y divide-gray-100 rounded-md border border-gray-200"
                                                    role="list">
                                                    <li v-for="(rating, index) in detail.Ratings" :key="index"
                                                        class="flex items-center justify-between py-4 pl-4 pr-5 text-sm leading-6">
                                                        <span class="flex-shrink-0 text-gray-900 dark:text-gray-200"
                                                              v-text="rating.Source"></span>
                                                        <span
                                                            class="font-medium text-primary-600 hover:text-primary-500"
                                                            v-text="rating.Value"></span>
                                                    </li>
                                                </ul>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <DetailSkeletonCard v-else/>
            </div>
        </div>
    </div>
    <Head>
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "Movie",
                "name": "{{ detail.Title }}",
                "image": "{{ moviePoster(detail) }}",
                "genre": "{{ Genre }}",
                "contentRating": "{{ detail.Rated }}",
                "datePublished": "{{ detail.Released }}",
                "actor": "{{ detail.Actors }}",
                "description": "{{ detail.Plot }}",
                "aggregateRating": {
                    "@type": "AggregateRating",
                    "ratingValue": "{{ props.detail.imdbRating }}",
                    "bestRating": "10",
                    "worstRating": "1",
                    "ratingCount": "{{ props.detail.imdbVotes }}"
                },
                "director": {
                    "@type": "Person",
                    "name": "{{ detail.Director }}"
                },
                "duration": "{{ props.detail.Runtime }}"
            }
        </script>
    </Head>
</template>
<script setup>
import DetailSkeletonCard from '@/Components/DetailSkeletonCard.vue';
import {computed} from 'vue';
import SnippetText from '@/Components/SnippetText.vue';
import {Head} from "@inertiajs/vue3";

const props = defineProps({
    detail: Object,
});
const isValue = function(value) {
    return value && value !== 'N/A';
};

const basicRating = computed(() => {
    let mappedRating = Math.round(props.detail.imdbRating / 2);

    mappedRating = Math.max(1, Math.min(5, mappedRating));

    return mappedRating;
});

const topRated = computed(() => {
    const imdbVotes = parseInt(props.detail.imdbVotes.replaceAll(',', ''));
    return (props.detail.imdbRating > 8 && imdbVotes > 1_00_000) || imdbVotes > 3_00_000 && props.detail.imdbRating ===
        8;
});

const hiddenGems = computed(() => {
    const imdbVotes = parseInt(props.detail.imdbVotes.replaceAll(',', ''));
    return props.detail.imdbRating > 8.5 && imdbVotes < 1_00_000 && imdbVotes > 30_000;
});

const recentlyReleased = computed(() => {
    const releaseDate = new Date(props.detail.Released);
    const today = new Date();

    const diffTime = Math.abs(today - releaseDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    return diffDays < 30;
});
const trending = computed(() => {
    return topRated.value && recentlyReleased.value;
});

const Genre = computed(() => {
    return props.detail.Genre.replaceAll(', ', '/');
});

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

</script>
