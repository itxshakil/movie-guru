<script setup>
import {ref, onMounted, onBeforeUnmount} from 'vue';
import SearchCard from '@/Components/SearchCard.vue';
import Show from '@/Components/Show.vue';

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

const viewDetail = (imdbID, title) => {
    selectedIMDBId.value = imdbID;
    selectedTitle.value = title;
};
</script>

<template>
    <section id="hidden-gem" class="py-10 px-4 scroll-mt-20">
        <div class="mx-auto max-w-7xl">
            <div class="mb-5 flex flex-wrap items-center gap-x-3 gap-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-xl">💎</span>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white tracking-tight">Hidden Gem of the Day</h2>
                </div>
                <button
                    v-if="gem"
                    :disabled="isShuffling"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 transition-colors hover:bg-gray-200 disabled:opacity-60 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    type="button"
                    @click="onShuffleClick"
                >
                    <svg :class="{'motion-safe:animate-spin': isShuffling}" aria-hidden="true" class="h-3.5 w-3.5"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M4 4v5h5M20 20v-5h-5M5.6 15a7 7 0 0 0 12.3 2.3M18.4 9A7 7 0 0 0 6.1 6.7"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Another gem
                </button>
                <span v-if="isShakeEnabled" class="text-xs text-gray-500 dark:text-gray-400">or shake your phone</span>
            </div>

            <!-- Skeleton -->
            <div v-if="loading"
                 class="max-w-xs animate-pulse rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">
                <div class="h-64 bg-gray-200 dark:bg-gray-700"/>
                <div class="p-4 space-y-3">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3"/>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/3"/>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"/>
                </div>
            </div>

            <!-- Gem card -->
            <div v-else-if="gem" :key="gem.imdb_id" class="md:max-w-sm">
                <SearchCard :movie="gem" @selected="viewDetail(gem.imdb_id, gem.title)"/>
            </div>
        </div>

        <Show v-if="selectedIMDBId" :imdbID="selectedIMDBId" :title="selectedTitle" @close="selectedIMDBId = null"/>
    </section>
</template>
