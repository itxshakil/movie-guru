<script setup>
import {ref, computed} from 'vue';
import {Head, Link, useForm} from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import {shareOrCopyLink, shareOrDownloadFile} from '@/Composables/useFileSharing.js';
import {createCalendarFile} from '@/Composables/useCalendarEvent.js';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import LoadingSpinner from '@/Components/Icons/LoadingSpinner.vue';
import UiButton from '@/Components/Ui/UiButton.vue';

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

    <div class="min-h-screen py-12 px-4">
        <div class="mx-auto max-w-2xl">

            <!-- Header -->
            <div class="mb-10 text-center">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Movie Match</h1>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Pick movies you want to watch, share the link, and see
                    what you both agree on.</p>
            </div>

            <!-- Results view -->
            <div v-if="hasResults" class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">
                        You matched on {{ session.matched.length }} movie{{
                            session.matched.length !== 1 ? 's' : ''
                        }}
                    </h2>
                    <div v-if="session.matched.length === 0" class="text-gray-500 dark:text-gray-400 text-sm">
                        No overlapping picks - try again with different movies!
                    </div>
                    <ul v-else class="space-y-3">
                        <li v-for="movie in matchedMovies" :key="movie.imdb_id">
                            <Link
                                :href="route('movie.detail.full', { imdbID: movie.imdb_id })"
                                prefetch="click"
                                class="flex items-center gap-3 rounded-xl p-2 -m-2 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
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
                     class="space-y-3 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Plan your movie night</h2>
                    <InputLabel class="sr-only" for="movie-night-at" value="Movie night date and time"/>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <TextInput id="movie-night-at" v-model="movieNightAt" class="flex-1" type="datetime-local"/>
                        <UiButton @click="addMovieNightToCalendar">Add to calendar</UiButton>
                    </div>
                </div>

                <div class="text-center">
                    <UiButton :href="route('movie-match.create')" size="lg">Start a new match</UiButton>
                </div>
            </div>

            <!-- Partner pick submission -->
            <div v-else-if="session && !isCreator" class="space-y-6">
                <div
                    class="rounded-2xl border border-primary-100 dark:border-primary-900/40 bg-primary-50 dark:bg-primary-950/40 p-4 text-sm text-primary-700 dark:text-primary-300">
                    Your friend has picked their movies. Now add yours - we'll find the overlap!
                </div>

                <div class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                    <div>
                        <InputLabel class="mb-2" for="match-pick" value="Enter IMDb IDs (e.g. tt0111161)"/>
                        <div class="flex gap-2">
                            <TextInput
                                id="match-pick"
                                v-model="searchQuery"
                                class="flex-1"
                                placeholder="tt0111161"
                                type="text"
                                @keydown.enter.prevent="addPick(searchQuery.trim()); searchQuery = ''"
                            />
                            <UiButton @click="addPick(searchQuery.trim()); searchQuery = ''">Add</UiButton>
                        </div>
                    </div>

                    <div v-if="picks.length" class="flex flex-wrap gap-2">
                        <span v-for="id in picks" :key="id"
                              class="flex items-center gap-1.5 px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded-full text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ id }}
                            <UiButton :aria-label="`Remove ${id}`" class="-my-2 -mr-2.5" size="icon" variant="ghost" @click="removePick(id)">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </UiButton>
                        </span>
                    </div>

                    <UiButton :disabled="picks.length === 0" :loading="form.processing" block size="lg" @click="submitPicks">
                        See our matches
                    </UiButton>
                </div>
            </div>

            <!-- Creator waiting view -->
            <div v-else-if="session && isCreator" class="space-y-6">
                <div class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 text-center dark:border-gray-800 dark:bg-gray-900">
                    <LoadingSpinner aria-hidden="true" class="mx-auto h-8 w-8 text-primary-600 dark:text-primary-400"/>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Waiting for your friend to pick their movies.
                        Share this link:</p>
                    <div class="flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                        <span class="flex-1 text-xs text-gray-500 dark:text-gray-400 truncate">{{ shareUrl }}</span>
                        <UiButton class="shrink-0" size="sm" variant="ghost" @click="copyLink">Copy</UiButton>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 justify-center">
                        <UiButton @click="shareLink">Share link</UiButton>
                        <UiButton v-if="isContactPickerSupported" variant="secondary" @click="pickContact">
                            Pick a friend
                        </UiButton>
                    </div>
                    <div v-if="pickedContact" class="space-y-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Send it to {{ pickedContact.name }}:</p>
                        <div class="flex gap-2 justify-center">
                            <UiButton :href="whatsAppUrl" class="bg-green-600! hover:bg-green-500! active:bg-green-700!"
                                      external rel="noopener" target="_blank">WhatsApp</UiButton>
                            <UiButton :href="smsUrl" external variant="secondary">SMS</UiButton>
                        </div>
                    </div>
                    <p aria-live="polite" class="min-h-5 text-xs font-medium text-primary-600 dark:text-primary-400">{{ shareFeedback }}</p>
                </div>
            </div>

            <!-- Create new session -->
            <div v-else class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <div>
                    <InputLabel class="mb-2" for="match-pick" value="Enter IMDb IDs of movies you want to watch (up to 10)"/>
                    <div class="flex gap-2">
                        <TextInput
                            id="match-pick"
                            v-model="searchQuery"
                            class="flex-1"
                            placeholder="tt0111161"
                            type="text"
                            @keydown.enter.prevent="addPick(searchQuery.trim()); searchQuery = ''"
                        />
                        <UiButton @click="addPick(searchQuery.trim()); searchQuery = ''">Add</UiButton>
                    </div>
                </div>

                <div v-if="picks.length" class="flex flex-wrap gap-2">
                    <span v-for="id in picks" :key="id"
                          class="flex items-center gap-1.5 px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded-full text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ id }}
                        <UiButton :aria-label="`Remove ${id}`" class="-my-2 -mr-2.5" size="icon" variant="ghost" @click="removePick(id)">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </UiButton>
                    </span>
                </div>

                <UiButton :disabled="picks.length === 0" :loading="form.processing" block size="lg" @click="submitPicks">
                    Create match link
                </UiButton>
            </div>
        </div>
    </div>
</template>
