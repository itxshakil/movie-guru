<script setup>
import {ref, computed} from 'vue';
import {Head, Link, useForm} from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import {shareOrCopyLink, shareOrDownloadFile} from '@/Composables/useFileSharing.js';
import {createCalendarFile} from '@/Composables/useCalendarEvent.js';

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
    if (imdbId && !picks.value.includes(imdbId) && picks.value.length < 10) {
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
    return route('movie-match.show', props.session.token);
});

const isCreator = computed(() => Boolean(props.session?.is_creator));
const hasResults = computed(() => props.session?.matched != null && props.session?.partner_picks != null);

const shareFeedback = ref('');
const shareMessage = 'Pick the movies you want to watch and Movie Guru will find the ones we both like 🍿';

const flashShareFeedback = (message) => {
    shareFeedback.value = message;
    setTimeout(() => {
        shareFeedback.value = '';
    }, 2500);
};

const shareLink = async () => {
    try {
        const outcome = await shareOrCopyLink({url: shareUrl.value, title: 'Movie Match', text: shareMessage});
        if (outcome === 'copied') {
            flashShareFeedback('Link copied!');
        }
    } catch (error) {
        console.error('Sharing the match link failed', error);
        flashShareFeedback("Couldn't share the link. Copy it from the box above.");
    }
};

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(shareUrl.value);
        flashShareFeedback('Link copied!');
    } catch (error) {
        console.error('Copying the match link failed', error);
        flashShareFeedback("Couldn't copy the link. Select it and copy it manually.");
    }
};

// Contact Picker API (Android Chrome): choose the friend, then send the link by WhatsApp or SMS.
const isContactPickerSupported = typeof navigator !== 'undefined' && 'contacts' in navigator && 'ContactsManager' in window;
const pickedContact = ref(null);

const pickContact = async () => {
    try {
        const [contact] = await navigator.contacts.select(['name', 'tel'], {multiple: false});
        const phoneNumber = contact?.tel?.[0];
        if (!phoneNumber) {
            flashShareFeedback("That contact doesn't have a phone number.");
            return;
        }
        pickedContact.value = {
            name: contact.name?.[0] ?? phoneNumber,
            phoneNumber: phoneNumber.replace(/[^\d+]/g, ''),
        };
    } catch (error) {
        if (error.name !== 'AbortError') {
            console.error('Picking a contact failed', error);
        }
    }
};

const messageWithLink = computed(() => `${shareMessage} ${shareUrl.value}`);
const whatsAppUrl = computed(() => pickedContact.value
    ? `https://wa.me/${pickedContact.value.phoneNumber.replace(/\D/g, '')}?text=${encodeURIComponent(messageWithLink.value)}`
    : null);
const smsUrl = computed(() => pickedContact.value
    ? `sms:${pickedContact.value.phoneNumber}?body=${encodeURIComponent(messageWithLink.value)}`
    : null);

const matchedMovies = computed(() => props.session?.matched_movies ?? []);
const movieLabel = (movie) => movie.title ? `${movie.title}${movie.year ? ` (${movie.year})` : ''}` : movie.imdb_id;

const nextSaturdayEvening = () => {
    const date = new Date();
    date.setDate(date.getDate() + ((6 - date.getDay() + 7) % 7 || 7));
    date.setHours(20, 0, 0, 0);
    const offsetDate = new Date(date.getTime() - date.getTimezoneOffset() * 60_000);
    return offsetDate.toISOString().slice(0, 16);
};

const movieNightAt = ref(nextSaturdayEvening());

const addMovieNightToCalendar = async () => {
    const start = new Date(movieNightAt.value);
    if (Number.isNaN(start.getTime())) {
        return;
    }

    const titles = matchedMovies.value.map(movieLabel);
    const file = createCalendarFile({
        title: '🍿 Movie night',
        description: titles.length ? `We matched on: ${titles.join(', ')}` : 'Planned with Movie Match on Movie Guru',
        url: shareUrl.value,
        start,
    });

    try {
        await shareOrDownloadFile(file, {title: 'Movie night'});
    } catch (error) {
        console.error('Adding the movie night to the calendar failed', error);
    }
};
</script>

<template>
    <Head>
        <title>Movie Match - Movie Guru</title>
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
                        No overlapping picks - try again with different movies!
                    </div>
                    <ul v-else class="space-y-3">
                        <li v-for="movie in matchedMovies" :key="movie.imdb_id">
                            <Link
                                :href="route('movie.detail.full', { imdbID: movie.imdb_id })"
                                prefetch="click"
                                class="flex items-center gap-3 rounded-xl p-2 -m-2 hover:bg-white dark:hover:bg-gray-800 transition-colors"
                            >
                                <img
                                    v-if="movie.poster"
                                    :alt="`${movie.title ?? movie.imdb_id} poster`"
                                    :src="movie.poster"
                                    class="h-14 w-10 shrink-0 rounded-md object-cover"
                                    loading="lazy"
                                />
                                <span class="text-primary-600 dark:text-primary-400 font-semibold hover:underline text-sm">
                                    {{ movieLabel(movie) }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                </div>

                <div v-if="session.matched.length"
                     class="rounded-2xl border border-gray-100 dark:border-gray-800 p-6 space-y-3">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Plan your movie night</h2>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <label class="sr-only" for="movie-night-at">Movie night date and time</label>
                        <input
                            id="movie-night-at"
                            v-model="movieNightAt"
                            class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            type="datetime-local"
                        />
                        <button
                            class="px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white rounded-xl text-sm font-semibold transition-colors"
                            type="button"
                            @click="addMovieNightToCalendar"
                        >
                            📅 Add to calendar
                        </button>
                    </div>
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
                    Your friend has picked their movies. Now add yours - we'll find the overlap!
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
                    <div class="flex flex-col sm:flex-row gap-2 justify-center">
                        <button
                            class="px-4 py-2.5 bg-primary-600 hover:bg-primary-500 text-white rounded-xl text-sm font-semibold transition-colors"
                            type="button"
                            @click="shareLink"
                        >
                            Share link
                        </button>
                        <button
                            v-if="isContactPickerSupported"
                            class="px-4 py-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-900 dark:text-white rounded-xl text-sm font-semibold transition-colors"
                            type="button"
                            @click="pickContact"
                        >
                            Pick a friend
                        </button>
                    </div>
                    <div v-if="pickedContact" class="space-y-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Send it to {{ pickedContact.name }}:</p>
                        <div class="flex gap-2 justify-center">
                            <a :href="whatsAppUrl"
                               class="px-4 py-2 rounded-xl bg-green-600 hover:bg-green-500 text-white text-sm font-semibold transition-colors"
                               rel="noopener" target="_blank">WhatsApp</a>
                            <a :href="smsUrl"
                               class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-900 dark:text-white text-sm font-semibold transition-colors">SMS</a>
                        </div>
                    </div>
                    <p aria-live="polite" class="min-h-5 text-xs font-medium text-primary-600 dark:text-primary-400">{{ shareFeedback }}</p>
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
