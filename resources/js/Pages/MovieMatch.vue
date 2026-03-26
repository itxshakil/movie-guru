<script setup>
import {ref, computed} from 'vue';
import {Head, Link, useForm, router} from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';

defineOptions({layout: BaseLayout});

const props = defineProps({
    session: {
        type: Object,
        default: null,
    },
});

const searchQuery = ref('');
const picks = ref([]);

const addPick = (imdbId) => {
    if (!picks.value.includes(imdbId) && picks.value.length < 10) {
        picks.value.push(imdbId);
    }
};

const removePick = (imdbId) => {
    picks.value = picks.value.filter((id) => id !== imdbId);
};

const form = useForm({
    picks: [],
});

const submitPicks = () => {
    form.picks = picks.value;
    if (props.session) {
        form.post(route('movie-match.submit', props.session.token));
    } else {
        form.post(route('movie-match.store'));
    }
};

const shareUrl = computed(() => {
    if (!props.session) return null;
    return window.location.origin + route('movie-match.show', props.session.token);
});

const copyLink = () => {
    if (shareUrl.value) {
        navigator.clipboard.writeText(shareUrl.value);
    }
};

const isCreator = computed(() => props.session && !props.session.partner_picks);
const hasResults = computed(() => props.session?.matched !== null && props.session?.partner_picks !== null);
</script>

<template>
    <Head>
        <title>Movie Match — Movie Guru</title>
        <meta content="Find movies you and a friend both want to watch." head-key="description" name="description"/>
    </Head>

    <div class="min-h-screen bg-white dark:bg-gray-900 py-12 px-4">
        <div class="mx-auto max-w-2xl">

            <!-- Header -->
            <div class="mb-10 text-center">
                <div class="text-4xl mb-3">🎬</div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight"
                    style="font-feature-settings: 'kern' 1, 'liga' 1;">Movie Match</h1>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Pick movies you want to watch, share the link, and see
                    what you both agree on.</p>
            </div>

            <!-- Results view -->
            <div v-if="hasResults" class="space-y-8">
                <div class="rounded-2xl border border-gray-100 dark:border-gray-800 p-6 bg-gray-50 dark:bg-gray-800/50">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                        🎉 You matched on {{ session.matched.length }} movie{{
                            session.matched.length !== 1 ? 's' : ''
                        }}!
                    </h2>
                    <div v-if="session.matched.length === 0" class="text-gray-500 dark:text-gray-400 text-sm">
                        No overlapping picks — try again with different movies!
                    </div>
                    <ul v-else class="space-y-2">
                        <li v-for="imdbId in session.matched" :key="imdbId">
                            <Link
                                :href="route('movie.detail.full', { imdbID: imdbId })"
                                class="text-primary-600 dark:text-primary-400 font-semibold hover:underline text-sm"
                            >
                                {{ imdbId }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div class="text-center">
                    <Link :href="route('movie-match.create')"
                          class="inline-block px-6 py-3 bg-primary-600 hover:bg-primary-500 text-white font-semibold rounded-xl transition-colors text-sm">
                        Start a new match
                    </Link>
                </div>
            </div>

            <!-- Partner pick submission -->
            <div v-else-if="session && !isCreator" class="space-y-6">
                <div
                    class="rounded-2xl border border-primary-100 dark:border-primary-900/40 bg-primary-50 dark:bg-primary-900/20 p-4 text-sm text-primary-700 dark:text-primary-300">
                    Your friend has picked their movies. Now add yours — we'll find the overlap!
                </div>

                <div>
                    <label
                        class="block text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-2">Enter
                        IMDB IDs (e.g. tt0111161)</label>
                    <div class="flex gap-2">
                        <input
                            v-model="searchQuery"
                            class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            placeholder="tt0111161"
                            type="text"
                            @keydown.enter.prevent="addPick(searchQuery.trim()); searchQuery = ''"
                        />
                        <button
                            class="px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white rounded-xl text-sm font-semibold transition-colors"
                            type="button"
                            @click="addPick(searchQuery.trim()); searchQuery = ''"
                        >Add
                        </button>
                    </div>
                </div>

                <div v-if="picks.length" class="flex flex-wrap gap-2">
                    <span v-for="id in picks" :key="id"
                          class="flex items-center gap-1.5 px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded-full text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ id }}
                        <button class="text-gray-400 hover:text-red-500 transition-colors"
                                @click="removePick(id)">×</button>
                    </span>
                </div>

                <button
                    :disabled="picks.length === 0 || form.processing"
                    class="w-full py-3 bg-primary-600 hover:bg-primary-500 disabled:opacity-50 text-white font-semibold rounded-xl transition-colors text-sm"
                    @click="submitPicks"
                >
                    See our matches →
                </button>
            </div>

            <!-- Creator waiting view -->
            <div v-else-if="session && isCreator" class="space-y-6">
                <div class="rounded-2xl border border-gray-100 dark:border-gray-800 p-6 text-center space-y-4">
                    <div class="text-3xl">⏳</div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Waiting for your friend to pick their movies.
                        Share this link:</p>
                    <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3">
                        <span class="flex-1 text-xs text-gray-500 dark:text-gray-400 truncate">{{ shareUrl }}</span>
                        <button class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline shrink-0"
                                @click="copyLink">
                            Copy
                        </button>
                    </div>
                </div>
            </div>

            <!-- Create new session -->
            <div v-else class="space-y-6">
                <div>
                    <label
                        class="block text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-2">Enter
                        IMDB IDs of movies you want to watch (up to 10)</label>
                    <div class="flex gap-2">
                        <input
                            v-model="searchQuery"
                            class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            placeholder="tt0111161"
                            type="text"
                            @keydown.enter.prevent="addPick(searchQuery.trim()); searchQuery = ''"
                        />
                        <button
                            class="px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white rounded-xl text-sm font-semibold transition-colors"
                            type="button"
                            @click="addPick(searchQuery.trim()); searchQuery = ''"
                        >Add
                        </button>
                    </div>
                </div>

                <div v-if="picks.length" class="flex flex-wrap gap-2">
                    <span v-for="id in picks" :key="id"
                          class="flex items-center gap-1.5 px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded-full text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ id }}
                        <button class="text-gray-400 hover:text-red-500 transition-colors"
                                @click="removePick(id)">×</button>
                    </span>
                </div>

                <button
                    :disabled="picks.length === 0 || form.processing"
                    class="w-full py-3 bg-primary-600 hover:bg-primary-500 disabled:opacity-50 text-white font-semibold rounded-xl transition-colors text-sm"
                    @click="submitPicks"
                >
                    Create match link →
                </button>
            </div>
        </div>
    </div>
</template>
