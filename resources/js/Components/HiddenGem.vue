<script setup>
import {computed, ref, onMounted, onBeforeUnmount} from 'vue';
import {Link} from '@inertiajs/vue3';
import SearchCard from '@/Components/SearchCard.vue';
import Show from '@/Components/Show.vue';
import WatchlistButton from '@/Components/WatchlistButton.vue';
import WatchedButton from '@/Components/WatchedButton.vue';
import SectionHeading from '@/Components/Ui/SectionHeading.vue';
import UiButton from '@/Components/Ui/UiButton.vue';

const gem = ref(null);
const loading = ref(true);
const isShuffling = ref(false);
const selectedIMDBId = ref(null);
const selectedTitle = ref(null);

const SHAKE_THRESHOLD = 25;
const SHAKE_COOLDOWN_MS = 1500;
const isShakeSupported = typeof window !== 'undefined'
    && 'DeviceMotionEvent' in window
    && window.matchMedia('(pointer: coarse)').matches;
const isShakeEnabled = ref(false);
let lastShakeAt = 0;

const fetchGem = async (params = {}) => {
    const response = await fetch(route('hidden-gem.show', params), {headers: {Accept: 'application/json'}});
    if (!response.ok) {
        throw new Error(`Hidden gem request failed with status ${response.status}`);
    }
    return response.json();
};

onMounted(async () => {
    try {
        gem.value = await fetchGem();
    } catch (error) {
        console.error('Loading the hidden gem failed', error);
        gem.value = null;
    } finally {
        loading.value = false;
    }
});

const shuffleGem = async () => {
    if (isShuffling.value) {
        return;
    }

    isShuffling.value = true;
    try {
        const params = gem.value?.imdb_id ? {shuffle: 1, exclude: gem.value.imdb_id} : {shuffle: 1};
        const nextGem = await fetchGem(params);
        if (nextGem) {
            gem.value = nextGem;
        }
    } catch (error) {
        console.error('Shuffling the hidden gem failed', error);
    } finally {
        isShuffling.value = false;
    }
};

const handleDeviceMotion = (event) => {
    const {x = 0, y = 0, z = 0} = event.accelerationIncludingGravity ?? {};
    const force = Math.abs(x) + Math.abs(y) + Math.abs(z);
    const now = Date.now();

    if (force > SHAKE_THRESHOLD + 9.8 && now - lastShakeAt > SHAKE_COOLDOWN_MS) {
        lastShakeAt = now;
        navigator.vibrate?.(30);
        shuffleGem();
    }
};

/**
 * iOS only grants motion events after DeviceMotionEvent.requestPermission() from a tap,
 * so the shake gesture is switched on by the first tap on the shuffle button.
 */
const enableShake = async () => {
    if (!isShakeSupported || isShakeEnabled.value) {
        return;
    }

    try {
        if (typeof DeviceMotionEvent.requestPermission === 'function'
            && await DeviceMotionEvent.requestPermission() !== 'granted') {
            return;
        }
        window.addEventListener('devicemotion', handleDeviceMotion);
        isShakeEnabled.value = true;
    } catch (error) {
        console.error('Enabling shake to shuffle failed', error);
    }
};

const onShuffleClick = () => {
    enableShake();
    shuffleGem();
};

onBeforeUnmount(() => window.removeEventListener('devicemotion', handleDeviceMotion));

const isValue = (value) => Boolean(value) && value !== 'N/A';

const posterFor = (movie) => isValue(movie.poster) ? movie.poster : '/assets/images/no-poster.jpg';

const metaLine = computed(() => {
    const movie = gem.value ?? {};
    const type = isValue(movie.type) ? movie.type.charAt(0).toUpperCase() + movie.type.slice(1) : '';
    const rating = Number(movie.imdb_rating) ? `★ ${Number(movie.imdb_rating).toFixed(1)}` : '';

    return [movie.year, type, movie.genre, rating].filter(isValue).join(' · ');
});

const viewDetail = (imdbID, title) => {
    selectedIMDBId.value = imdbID;
    selectedTitle.value = title;
};
</script>

<template>
    <section v-if="loading || gem?.imdb_id" id="hidden-gem" class="scroll-mt-20 py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading subtitle="One underrated film, picked for today." title="Hidden gem of the day">
                <template v-if="gem" #action>
                    <span v-if="isShakeEnabled" class="text-xs text-gray-500 dark:text-gray-400">or shake your phone</span>
                    <UiButton :loading="isShuffling" size="sm" variant="secondary" @click="onShuffleClick">
                        <template #icon>
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path d="M4 4v5h5M20 20v-5h-5M5.6 15a7 7 0 0 0 12.3 2.3M18.4 9A7 7 0 0 0 6.1 6.7"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </template>
                        Another gem
                    </UiButton>
                </template>
            </SectionHeading>

            <!-- Skeleton -->
            <template v-if="loading">
                <div class="max-w-sm animate-pulse overflow-hidden rounded-2xl border border-gray-200 bg-white md:hidden dark:border-gray-800 dark:bg-gray-900">
                    <div class="aspect-2/3 bg-gray-200 dark:bg-gray-700"/>
                    <div class="space-y-3 p-4">
                        <div class="h-4 w-2/3 rounded bg-gray-200 dark:bg-gray-700"/>
                        <div class="h-3 w-1/3 rounded bg-gray-200 dark:bg-gray-700"/>
                        <div class="h-3 w-1/2 rounded bg-gray-200 dark:bg-gray-700"/>
                    </div>
                </div>
                <div class="hidden animate-pulse overflow-hidden rounded-2xl border border-gray-200 bg-white md:grid md:grid-cols-[minmax(0,16rem)_1fr] dark:border-gray-800 dark:bg-gray-900">
                    <div class="aspect-2/3 bg-gray-200 dark:bg-gray-700"/>
                    <div class="flex flex-col gap-3 p-6 lg:p-8">
                        <div class="h-7 w-2/3 rounded bg-gray-200 dark:bg-gray-700"/>
                        <div class="h-4 w-1/3 rounded bg-gray-200 dark:bg-gray-700"/>
                        <div class="h-4 w-full rounded bg-gray-200 dark:bg-gray-700"/>
                    </div>
                </div>
            </template>

            <!-- Gem card -->
            <template v-else-if="gem?.imdb_id">
                <div :key="`card-${gem.imdb_id}`" class="max-w-sm md:hidden">
                    <SearchCard :movie="gem" @selected="viewDetail(gem.imdb_id, gem.title)"/>
                </div>
                <article :key="`featured-${gem.imdb_id}`"
                         class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-white md:grid md:grid-cols-[minmax(0,16rem)_1fr] dark:border-gray-800 dark:bg-gray-900">
                    <Link :href="route('movie.detail.full', {imdbID: gem.imdb_id})" class="block aspect-2/3 bg-gray-100 dark:bg-gray-800" tabindex="-1">
                        <img :alt="gem.title + ' poster'" :src="posterFor(gem)" class="h-full w-full object-cover" loading="lazy">
                    </Link>
                    <div class="flex flex-col gap-3 p-6 lg:p-8">
                        <h3 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            <Link :href="route('movie.detail.full', {imdbID: gem.imdb_id})" class="hover:underline">{{ gem.title }}</Link>
                        </h3>
                        <p v-if="metaLine" class="text-sm text-gray-500 dark:text-gray-400">{{ metaLine }}</p>
                        <p v-if="isValue(gem.director)" class="text-sm text-gray-500 dark:text-gray-400">
                            Dir. <span class="font-medium text-gray-700 dark:text-gray-300">{{ gem.director }}</span>
                        </p>
                        <p v-if="isValue(gem.plot)" class="line-clamp-4 text-base text-gray-700 dark:text-gray-300">{{ gem.plot }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <WatchlistButton :movie="gem" size="md" variant="label"/>
                            <WatchedButton :movie="gem" size="md" variant="label"/>
                            <UiButton :href="route('movie.detail.full', {imdbID: gem.imdb_id})" size="md">Details</UiButton>
                        </div>
                    </div>
                </article>
            </template>
        </div>

        <Show v-if="selectedIMDBId" :imdbID="selectedIMDBId" :title="selectedTitle" @close="selectedIMDBId = null"/>
    </section>
</template>
